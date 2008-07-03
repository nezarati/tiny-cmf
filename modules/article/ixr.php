<?
namespace Article;
class IXR {
	public $server, $port, $path, $useragent = 'The Incutio XML-RPC PHP Library -- Chonoo/7.0.0', $timeout = 3, $error, $xml;
	public function __construct($server) {
		$bits = parse_url($server);
		$this->server = $bits['host'];
		$this->port = isset($bits['port']) ? $bits['port'] : 80;
		$this->path = !empty($bits['path']) ? $bits['path'] : '/';
	}
	public function query() {
		$r = "\r\n";
		$args = func_get_args();
		$method = array_shift($args);
		foreach ($args as $arg)
		$this->xml .= "<param><value><string>$arg</string></value></param>";
		$xml = "<?xml version='1.0'?><methodCall><methodName>$method</methodName><params>$this->xml</params></methodCall>";
		$request = "POST {$this->path} HTTP/1.0{$r}Host: {$this->server}{$r}Content-Type: text/xml{$r}User-Agent: {$this->useragent}{$r}Content-length: ".strlen($xml)."$r$r$xml";
		$fp = @fsockopen($this->server, $this->port, $errno, $errstr, $this->timeout);
		if (!$fp) {
			$this->error = "transport error - could not open socket: $errno $errstr";
			return FALSE;
		}
		fputs($fp, $request);
		fclose($fp);
		return TRUE;
	}
}