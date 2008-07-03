<?
final class Session {
	public function __construct() {
		$_SERVER['HTTP_USER_AGENT'] || die; # TODO: BOT
		session_name(subStr(md5($_SERVER['HTTP_USER_AGENT'].$_SERVER['HTTP_HOST'].gmdate('Ymd')), 0, 10));
		session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'garbageCollector'));
		isset($_COOKIE[session_name()]) || session_id(self::createId());
		session_start();
	}
	private static function createId() { // A-z,\-0-9
		return substr(md5(uniqid(mt_rand(0, mt_getrandmax()), TRUE)), 0, rand(20, 32));
	}
	public static function open($path, $id) {
		register_shutdown_function('session_write_close');
		return TRUE;
	}
	public static function close() {
		return TRUE;
	}
	public static function read($id) {
		if (($doc = \Model\Session::all()->fields('data', 'hostname', 'userAgent')->filter('domain', DOMAIN)->filter('id', $id)->fetch()))
			if ($doc->userAgent == $_SERVER['HTTP_USER_AGENT'] && $doc->hostname == IP)
				return $doc->data;
			else
				static::destroy($id);
		return FALSE;
	}
	public static function write($id, $data) {
		return \Model\Session::update(array('domain' => DOMAIN, 'id' => $id), array('$set' => array('data' => $data, 'timestamp' => GMT, 'user' => USER_ID, 'hostname' => IP, 'userAgent' => $_SERVER['HTTP_USER_AGENT'])), array('upsert' => TRUE)); # MongoDB
	}
	public static function destroy($id) {
		Cookie::delete($id);
		return \Model\Session::all()->filter('id', $id)->delete();
	}
	/**
	 * @see session.gc_divisor      100
	 * @see session.gc_maxlifetime 1440
	 * @see session.gc_probability    1
	 * @usage execution rate 1/100
	 * (session.gc_probability/session.gc_divisor)
	*/
	public static function garbageCollector($maxLifeTime) {
		\Model\Form::all()->filter('created', GMT-$maxLifeTime, '<')->delete();
		return \Model\Session::all()->filter('timestamp', GMT-$maxLifeTime, '<')->delete();
	}
}