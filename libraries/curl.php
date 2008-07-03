<?
final class CUrl {
	public $handle;
	public static $user_agent = 'Chonoo/0.7.8 (+http://www.chonoo.com/)', $timeout = 3;
	# $user_agent = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
	public function __construct($url, Array $post = NULL, $cookie = 0) {
		$this->handle = curl_init();
		curl_setopt($this->handle, CURLOPT_URL, $url);
		# curl_setopt($this->handle, CURLOPT_FAILONERROR, 1);
		curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->handle, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded;charset=UTF-8'));
		# curl_setopt($this->handle, CURLOPT_HEADER, 1);
		curl_setopt($this->handle, CURLOPT_ENCODING, 'gzip');
		curl_setopt($this->handle, CURLOPT_TIMEOUT, self::$timeout);
		curl_setopt($this->handle, CURLOPT_USERAGENT, self::$user_agent);
		if ($cookie) {
			$file = '/tmp/curl_'.parse_url($url, PHP_URL_HOST).'.cookie';
			file_exists($file) || touch($file);
			curl_setopt($this->handle, CURLOPT_COOKIEFILE, $file);
			curl_setopt($this->handle, CURLOPT_COOKIEJAR, $file);
		}
		$post && $this->post($post);
	}
	public function post(Array $data) {
		curl_setopt($this->handle, CURLOPT_POST, 1);
		curl_setopt($this->handle, CURLOPT_POSTFIELDS, substr($this->_encode($data, '&'), 0, -1));
	}
	public function cookie(Array $data) {
		curl_setopt($this->handle, CURLOPT_COOKIE, substr($this->_encode($data, ';'), 0, -1));
	}
	private static function _encode(Array $data, $separator, $keyprefix = '', $keypostfix = '') {
		assert(is_array($data));
		$string = NULL;
		foreach($data as $key => $value)
			$string .= is_array($value) ? self::_encode($value, $separator, $keyprefix.$key.$keypostfix.urlEnCode('['), urlEnCode(']')) : $keyprefix.$key.$keypostfix.'='.urlencode($value).$separator;
		return $string;
	}
	public function info() {
		return curl_getinfo($this->handle);
	}
	public function __toString() {
		$r = curl_exec($this->handle);
		return $r ? $r : '';
	}
	public function __destruct() {
		curl_close($this->handle); 
	}
}