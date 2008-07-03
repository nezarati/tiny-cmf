<?
class MyOAuth extends HTTP {
	const HTTP_METHOD_GET = 'GET', HTTP_METHOD_POST = 'POST';
	const SIG_METHOD_RSASHA1 = 'RSA-SHA1', SIG_METHOD_HMACSHA1 = 'HMAC-SHA1', IG_METHOD_HMACSHA256 = 'HMAC-SHA256';
	
	protected static function fetch($url, $parameters = NULL, $method = self::HTTP_METHOD_GET, array $files = array(), $output = 'json') {
		$oauth['oauth_consumer_key'] = static::CONSUMER_KEY;
		$oauth['oauth_nonce'] = md5(microtime().rand());
		$oauth['oauth_timestamp'] = time();
		$oauth['oauth_token'] = static::OAUTH_TOKEN;
		$oauth['oauth_signature_method'] = static::SIGNATURE_METHOD;
		$oauth['oauth_version'] = '1.0';

		$data = $oauth;
		$data = array_merge($data, is_array($parameters) ? $parameters : array(), array_reduce($files, function($out, $metadata) use($files) {
			return $out + array(array_search($metadata, $files) => $metadata['basename']);
		}, array()));

		if ($method == self::HTTP_METHOD_GET && is_array($parameters))
			$url .= '?'.self::buildQuery($parameters);
		
		$oauth['oauth_signature'] = self::hmacsha1(static::CONSUMER_SECRET.'&'.static::OAUTH_TOKEN_SECRET, self::calculateBaseString($url, $method, $data));
		$headers['Authorization'] = self::calculateHeader($oauth, $url);
		$headers['Expect'] = '';
		
		$response = parent::request($url, $method == self::HTTP_METHOD_GET ? NULL : $parameters, $headers, $files);
		return $output == 'json' ? json_decode($response) : $response;
	}
	protected static function buildQuery(array $parameters) {
		ksort($parameters);
		foreach ($parameters as $key => $value)
			$chunks[] = self::urlencode($key).'='.self::urlencode($value);
		return implode('&', $chunks);
	}
	private static function calculateBaseString($url, $method, array $parameters) {
		return implode('&', array_map('self::urlencode', array($method, self::normalisedURL($url), self::buildQuery($parameters))));
	}
	private static function calculateHeader(array $parameters, $url = NULL) {
		foreach ($parameters as $key => $value)
			$chunks[] = self::urlencode($key).'="'.self::urlencode($value).'"';

		return 'OAuth realm="'.self::normalisedURL($url).'", '.implode(', ', $chunks);
	}
	private static function hmacsha1($key, $data) { # DOTO: fix me! support other
		return base64_encode(hash_hmac('SHA1', $data, $key, TRUE));
	}
	private static function normalisedURL($url) { # return scheme://[user:passwd@]host[:port][/path]
		$parts = parse_url($url);
		return $parts['scheme'].'://'.(isset($parts['user']) ? "{$parts[user]}:{$parts[pass]}@" : '').$parts['host'].(isset($parts['port']) && !($parts['scheme'] == 'http' && $parts['port'] == 80 || $parts['scheme'] == 'https' && $parts['port'] == 443) ? ':'.$parts['port'] : '').$parts['path'];
	}
	private static function urlencode($value) { # rfc3986
		return is_array($value) ? array_map('self::urlencode', $value) : str_replace('%7E', '~', rawurlencode($value));
	}
}