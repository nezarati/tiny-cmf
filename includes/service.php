<?
class Service {
	protected static $modules, $modulesName, $requireds, $dependences, $loaded, $identifiers;
	public static function init($domain = DOMAIN) {
		if (!(static::$modules = TMP::get('service.modules')) || !(static::$modulesName = TMP::get('service.modulesName'))) {
			foreach (\Model\Module::all()->map() as $doc) {
				static::$modules[$doc->id] = $doc;
				static::$modulesName[$doc->name] = $doc->id;
			}
			TMP::set('service.modules', static::$modules, 0);
			TMP::set('service.modulesName', static::$modulesName, 0);
		}
		if ($tmp = TMP::get($cacheKey = $domain.'.service.init')) {
			static::$requireds[$domain] = $tmp['requireds'];
			static::$dependences[$domain] = $tmp['dependences'];
			static::$loaded[$domain] = $tmp['loaded'];
			static::$identifiers[$domain] = $tmp['identifiers'];
		} else {
			foreach (\Model\Service::all()->fields('id', 'module', 'dependence')->filter('domain', $domain)->map() as $row) {
				$loaded[$row->module] = 1;
				$row->dependence && ($loaded[$row->dependence] = 1);
				static::$requireds[$domain][static::$modules[$row->module]->name][$row->id] = $row->dependence;
				static::$dependences[$domain][static::$modules[$row->dependence]->name][$row->id] = $row->module;
				static::$identifiers[$domain][static::$modules[$row->module]->name][static::$modules[$row->dependence]->name] = $row->id;
			}
			TMP::set($cacheKey, array(
					'requireds' => static::$requireds[$domain],
					'dependences' => static::$dependences[$domain],
					'loaded' => static::$loaded[$domain] = array_keys($loaded),
					'identifiers' => static::$identifiers[$domain],
				)
			);
		}
	}
	public static function install($module, $dependence = NULL, $domain = _DOMAIN, $user = USER_ID) {
		\View::status(__(':name module has been installed.', array('%name' => $dependence ? __(self::load(self::id($dependence))->title).'/'.__(self::load(self::id($module))->title) : __(self::load(self::id($module))->title))));
		return \Model\Service::put(array('module' => self::id($module), 'dependence' => self::id($dependence), 'domain' => $domain, 'user' => $user, 'created' => GMT, 'name' => $dependence ? ucfirst($dependence).'/'.ucfirst($module) : ucfirst($module)));
	}
	public static function required($module, $domain = DOMAIN) { # return array(SERVICE_ID => MODULE_INFO);
		$result = array();
		foreach (static::$requireds[$domain][$module] as $service => $module)
			$result[$service] = Service::load($module);
		return $result;
	}
	public static function dependence($module, $domain = DOMAIN) { # //
		$result = array();
		foreach (static::$dependences[$domain][$module] as $service => $module)
			$result[$service] = Service::load($module);
		return $result;
	}
	public static function identifier($module, $dependence = NULL, $domain = DOMAIN) {
		return static::$identifiers[$domain][$module][$dependence];
	}
	public static function loaded($domain = DOMAIN) {
		return static::$loaded[$domain];
	}
	public static function exists($module, $dependence = NULL) {
		return (bool) static::identifier($module, $dependence);
	}
	
	# Module
	public static function load($id) {
		return static::$modules[$id];
	}
	public static function id($name) {
		return $name ? (static::$modulesName[$name] ?: -1) : 0;
	}
}