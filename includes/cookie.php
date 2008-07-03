<?
final class Cookie { # 2009/03/19 17:44:47 ->->->
	public static function exists() {
		foreach (func_get_args() as $name)
			if (!isset($_COOKIE[$name]))
				return FALSE;
		return TRUE;
	}
	public static function get($name) {
		return self::exists($name) ? $_COOKIE[$name] : FALSE;
	}
	public static function set($name, $value, $expires = 31536000, $path = '/', $domain = NULL, $secure = FALSE, $httpOnly = FALSE) {
		return setcookie($name, $value, GMT + $expires, $path, $domain, $secure, $httpOnly);
	}
	public static function delete() {
		foreach (func_get_args() as $name) {
			setcookie($name, NULL, 0);
			unset($_COOKIE[$name]);
		}
	}
}