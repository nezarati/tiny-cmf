<?
# MongoDB
class Registry implements IteratorAggregate {
	protected $module, $service, $default, $autoload, $value;
	protected static $instance = array(), $result;
	public static function setInstance($module, Array $default = array(), $autoload = FALSE) {
		self::$instance[$module] = new self($module, $default, $autoload);
	}
	public static function getInstance($module = 'main', $service = SERVICE_MAIN) {
		self::$instance[$module]->service = $service;
		if (!$tmp =& self::$instance[$module]->value[$service])
			if (self::$instance[$module]->autoload) {
				if (!$tmp = TMP::get($cacheKey = $service.'.registry.'.Service::id($module)))
					TMP::set($cacheKey, $tmp = static::init($module, $service));
			} else
				$tmp = static::init($module, $service);
		return self::$instance[$module];
	}
	public static function init($module, $service) {
		return \Model\Registry::all()->fields('value')->filter('module', Service::id($module))->filter('service', $service)->fetchField();
	}
	
	protected function __construct($module, $default, $autoload) {
		$this->module = Service::id($module);
		$this->default = $default;
		$this->autoload = $autoload;
	}
	public function __set($name, $value) {
		if ($this->default[$name] == $value)
			unset($this->$name);
		else {
			\Model\Registry::update(array('module' => $this->module, 'service' => $this->service), array('$set' => array('value.'.$name => $value)), array('upsert' => 1));
			$this->value[$this->service][$name] = $value;
			TMP::delete($this->service.'.registry.'.$this->module);
		}
	}
	public function __isset($name) {
		return isset($this->value[$this->service][$name]);
	}
	public function __unset($name) {
		unset($this->value[$this->service][$name]);
		TMP::delete($this->service.'.registry.'.$this->module);
		return \Model\Registry::update(array('module' => $this->module, 'service' => $this->service), array('$unset' => array('value.'.$name => TRUE)));
	}
	public function __get($name) {
		return isset($this->$name) ? $this->value[$this->service][$name] : $this->default[$name];
	}
	
	public function getIterator() {
		return new ArrayIterator($this->default);
	}
}