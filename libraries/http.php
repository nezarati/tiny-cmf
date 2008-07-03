<?
class HTTP {
	const EOL = "\r\n";
	protected static $encodeMethods = array('gzip' => 'self::inflateResponseGZip', 'deflate' => 'self::inflateResponseDeflate');
	public static $proxy;
	public static function request($uri, $data = NULL, Array &$headers = NULL, Array $files = NULL) {
		
		if (!$headers)
			$headers = array();
		
		if (is_string($data))
			$content = $data;
		else if ($files) {
			$boundary = '---------------------'.substr(md5(microtime()), 0, 10);
			$headers['Content-Type'] = 'multipart/form-data; boundary='.$boundary;
			foreach ($data as $key => $value) {
				$content .= '--'.$boundary.self::EOL;
				$content .= 'Content-Disposition: form-data; name="'.$key.'"'.self::EOL;
				$content .= self::EOL.$value.self::EOL;
				$content .= '--'.$boundary.self::EOL;
			}
			foreach ($files as $key => $metadata) {
				$content .= '--'.$boundary.self::EOL;
				$content .= 'Content-Disposition: form-data; name="'.$key.'"; filename="'.$metadata['basename'].'"'.self::EOL;
				$content .= 'Content-Type: application/octet-stream'.self::EOL;
				$content .= self::EOL.$metadata['bytes'].self::EOL;
				$content .= '--'.$boundary.self::EOL;
			}
		} else if (is_array($data)) {
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
			$content = static::buildQuery($data);
		}
		
		$urlinfo = parse_url(self::$proxy ?: $uri);
		$urlinfo += array('port' => $urlinfo['scheme'] == 'https' ? 443 : 80, 'path' => self::$proxy ? $uri : '/');
		if ($urlinfo['scheme'] == 'https')
			if (function_exists('openssl_open'))
				$scheme = 'ssl://';
			else
				throw new Exception('SSL');
		
		if ($fp = pfsockopen($scheme.$urlinfo['host'], $urlinfo['port'], $errno, $errstr, 10)) {
			
			$headers += array(
				'Host' => $urlinfo['host'],
				'User-Agent' => 'Joorchin via Socket/PHP',
				'Accept-Encoding' => implode(', ', array_keys(self::$encodeMethods)),
				'Accept-Charset' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
				'Content-length' => strlen($content),
				'Connection' => 'close'
			);
			foreach ($headers as $key => &$value)
				$value = $key.': '.$value;
			
			array_unshift($headers, (is_null($data) ? 'GET' : 'POST').' '.$urlinfo['path'].($urlinfo['query'] ? '?'.$urlinfo['query'] : '').' HTTP/1.1');
			
			fwrite($fp, $log = implode(self::EOL, $headers).self::EOL.self::EOL);
			fwrite($fp, $log .= $content);
#			echo $log;

			while (!feof($fp))
				$response .= fgets($fp, 4096);
			fclose($fp);
			
			$response = explode(self::EOL.self::EOL, $response, 2);
			$headers = new StdClass;
			foreach (explode(self::EOL, array_shift($response)) as $index => $entry)
				if ($index == 0 && preg_match('#^http/(1\.[012]) ([12345]\d\d) (.*)#i', $entry, $httpmatches))
					$headers->http = (object) array('version' => $httpmatches[1], 'statusCode' => $httpmatches[2], 'statusText' => $httpmatches[3]);
				else {
					list($key, $value) = explode(':', $entry, 2);
					if ($key == 'set-cookie') {
						preg_match('/^(.*?)=(.*?); path=(.*?);?/', $value, $mathes);
						$headers->cookie->{$mathes[3]}->{$mathes[2]} = $mathes[1];
					} else if (trim($key))
						$headers->{preg_replace('/\-([a-z])/e', 'strtoupper("$1")', strtolower(trim($key)))} = trim($value);
				}
			
			$content = array_pop($response);
			if ($headers->transferEncoding == 'chunked') # TODO
				$content = self::unchunk($content);
			
			return empty($content) || empty($headers->contentEncoding) ? $content : call_user_func(self::$encodeMethods[$headers->contentEncoding], $content);
		} else
			throw new Exception($errstr, $errno);
	}
	
	protected static function buildQuery($data) {
		foreach ($data as $key => $value)
				$content .= urlencode($key).'='.urlencode($value).'&';
		return substr($content, 0, -1);
	}
	protected static function unchunk($result) { #steve@visual77.com
		return preg_replace_callback(
			'/(?:(?:\r\n|\n)|^)([0-9A-F]+)(?:\r\n|\n){1,2}(.*?)'.'((?:\r\n|\n)(?:[0-9A-F]+(?:\r\n|\n))|$)/si',
			function($matches) {
				return hexdec($matches[1]) == strlen($matches[2]) ? $matches[2] : $matches[0];
			},
			$result
		);
	}
	protected static function chunked() { #richardaburton@hotmail.com
		do {
			$byte = "";
			$chunk_size="";
			do {
				$chunk_size .= $byte;
				$byte = fread($request, 1);
			} while ($byte != "\r"); // till we match the CR
			fread($request, 1); // also drop off the LF
			$chunk_size = hexdec($chunk_size); // convert to real number
			$response .= fread($request, $chunk_size);
			fread($request, 2); // ditch the CRLF that trails the chunk
		} while ($chunk_size); // till we reach the 0 length chunk (end marker)
	}

	protected static function inflateResponseGZip($content) {
		if ($content[0] == "\x1F" && $content[1] == "\x8b")
			if ($inflated = @gzinflate(substr($content, 10)))
				return $inflated;
	}
	protected static function inflateResponseDeflate($content) {
		if ($content[0] == "\x78" && $content[1] == "\x9C" && $inflated = @gzinflate(substr($content, 2)))
			return $inflated;
		else if ($inflated = @gzinflate($content))
			return $inflated;
	}
}