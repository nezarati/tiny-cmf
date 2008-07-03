<? # 2009-02-14 20:20:47
class Hook {
	protected static $action = array();
	public static function init($domain = DOMAIN) {
		if (static::$action = TMP::get($cacheKey = $domain.'.hook.init'))
			return;
		foreach (Service::loaded($domain) as $module)
		if (class_exists($class = '\\'.Service::load($module)->name.'\Hook'))
			new $class;
		Hook::sort();
		TMP::set($cacheKey, static::$action);
	}
	public static function add($name, $func, $priority = 10) {
		self::$action[$name][$priority][] = $func;
	}
	public static function sort() {
		foreach (self::$action as $name => &$value)
			uksort($value, 'strnatcasecmp');
	}
	public static function module($name, $hook) {
		if (function_exists($func = '\\'.$name.'\Hook::'.$hook)) {
			$result = call_user_func_array($func, array_slice(func_get_args(), 2));
			if (is_array($result))
				return $result;
			else if (isset($result))
				return array($result);
		}
		return array();
	}
	public static function invoke($name) {
		if (!isset(self::$action[$name]))
			return array();
		$argument = array_slice(func_get_args(), 1);
		$return = array();
		foreach (self::$action[$name] as $priority)
			foreach($priority as $function) {
				$result = call_user_func_array($function, $argument);
				if (isset($result) && is_array($result))
					$return = array_merge_recursive($return, $result);
				elseif (isset($result))
					$return[] = $result;
			}
		return $return;
	}
	public static function filter($name) {
		if (!isset(self::$action[$name]))
			return func_get_arg(1);
		$arg = array_slice(func_get_args(), 1);
		array_unshift($arg, $arg[0]);
		foreach (self::$action[$name] as $priority)
			foreach($priority as $f)
				$arg[0] = call_user_func_array($f, $arg);
		return $arg[0];
	}
	public static function call($name) {
		if (!isset(self::$action[$name]))
			return;
		$arg = array_slice(func_get_args(), 1);
		foreach (self::$action[$name] as $priority)
			foreach($priority as $func)
				call_user_func_array($func, $arg);
	}
}