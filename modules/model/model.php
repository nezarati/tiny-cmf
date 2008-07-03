<?
namespace Model;
class Model implements \IteratorAggregate {
	const TINY_TEXT = 255, TEXT = 2048, MEDIUM_TEXT = 51200, LONG_TEXT = 102400;
	const JUST_ONE = 1, MULTIPLE = 2, UPSERT = 4;
	
	public static $_handle, $_database, $_databases = array(), $_collections = array();
	protected $_documentOriginal, $_documentCurrent;
	public static $_columns = array(
		'string' => array('name' => 'varchar'),
		'text' => array('name' => 'text'),
		'integer' => array('name' => 'int', 'format' => NULL, 'filter' => 'intval'),
		'float' => array('name' => 'float', 'format' => NULL, 'formatter' => 'floatval'),
		'datetime' => array('name' => 'datetime', 'format' => NULL, 'formatter' => '\Model\Model::dateFormatter'),
		'timestamp' => array('name' => 'timestamp', 'format' => NULL, 'filter' => 'intval'),
		'time' => array('name' => 'time', 'format' => NULL, 'formatter' => '\Model\Model::dateFormatter'),
		'date' => array('name' => 'date', 'format' => NULL, 'formatter' => '\Model\Model::dateFormatter'),
	);
	public static function dateFormatter($date = NULL) {
		return $date ? new \MongoDate($date) : new \MongoDate;
	}
	/*
	protected $_schema = array(
		'description' => '',
		'fields' => array(
			Column => array(
				'type' => 'int|varchar|float',
				'length' => int,
				'unsigned' => TRUE|FALSE,
				'not null' => TRUE|FALSE,
				'auto increment' => TRUE|FALSE,
				'default' => '',
				'description' => '',
			),
		),
		'indexes' => array(Name => Fields),
		'full text' => array(),
		'unique keys' => array(Name => Fields),
		'primary key' => array(Fields),
		'foreign keys' => array(Name => array(Table => Fields), ),
	);
	*/
	
	public static function connect($host = 'mongodb://127.0.0.1/cms') {
		$database = substr(parse_url($host, PHP_URL_PATH), 1);
		if (!self::$_handle)
			self::$_handle = new \Mongo($host, array('persist' => 'joorchin'));
		if (!isset(self::$_databases[$database]))
			self::$_databases[$database] = self::$_handle->selectDB($database);
		self::$_database = $database;
	}
	public static function getDB($name) {
		return self::$_databases[$name];
	}
	
	protected static function collectionName() {
		$ref = explode('\\', get_called_class());
		return array_pop($ref);
	}
	public static function getCollection() {
		$table = static::collectionName();
		return $namespace =& self::$_collections[self::$_database.'.'.$table] ?: $namespace = self::$_databases[self::$_database]->$table;
	}
	public static function __callStatic($method, $arg) {
		return call_user_func_array(array(static::getCollection(), $method), $arg);
	}
	public function __call($method, $arg) {
		return static::__callStatic($method, $arg);
	}
	final public static function install() {
		foreach (array_reverse(get_declared_classes()) as $class)
			if ($class == __CLASS__)
				break;
			else if (is_subclass_of($class, __CLASS__))
				$class::setup();
	}
	public static function all() {
		return new DataStoreAll(self::$_handle, self::$_database.'.'.static::collectionName(), get_called_class());
	}
	protected function primaryKey() {
		return static::all()->fields('id')->filter('service', $this->service)->sort('-id')->fetchField() + 1;
	}
	
	public static function enum($field, $value = NULL) {
		return is_null($value) ? static::$_schema['fields'][$field]['enum'] : static::$_schema['fields'][$field]['enum'][$value];
	}
	
	public function put($data = NULL) {
		if (!is_null($data)) {
			$self = get_called_class();
			$self = new $self($data, FALSE);
			return $self->put();
		}
		
		method_exists($this, 'prePut') && $this->prePut($this->_documentCurrent, isset($this->_id) ? 'update' : 'create');

		/*static $schema = FALSE; # TODO:
		if (!$schema) {
			\Hook::call('schema'.static::collectionName(), static::$_schema);
			$schema = TRUE;
		}*/
		foreach ($this->_documentCurrent as $field => &$value)
			if ($filter = static::$_schema['fields'][$field]['filter'] ?: Model::$_columns[static::$_schema['fields'][$field]['type']]['filter'])
				$value = call_user_func($filter, $value);

		if (!isset($this->_id) && isset(static::$_schema) && isset(static::$_schema['primary key'])) {
			$update = TRUE;
			$fields = array();
			foreach (static::$_schema['primary key'] as $field) {
				if (!isset($this->$field))
					$update = FALSE;
				if (static::$_schema['fields'][$field]['auto increment'])
					$autoIncrementField = $field;
				else
					$fields[] = $field;
			}
			if (!$update) {
				$query = static::all()->fields($autoIncrementField);
				foreach ($fields as $field)
					$query->filter($field, $this->$field);
				$this->$autoIncrementField = (int)$query->sort('-'.$autoIncrementField)->fetchField() + 1;
			}
		}

		if (isset($this->_id) || $update) {
			static::getCurrentDocument($document, $this->_documentCurrent, $this->_documentOriginal);
			if ($update)
				$self = $this;
			$document && static::update(
				$update ?
					array_reduce(
						static::$_schema['primary key'],
						function($out, $field) use($self) {
							$out[$field] = $self->$field;
							return $out;
						},
						array()
					)
				:
					array('_id' => is_string($this->_id) && preg_match('/^[0-9a-z]{24}$/', $this->_id) ? new \MongoId($this->_id) : $this->_id),
				$document,
				array('upsert' => TRUE)
			);
		} else
			static::insert($this->_documentCurrent);
		
		method_exists($this, 'onPut') && $this->onPut();
		$this->_documentOriginal = $this->_documentCurrent;
		return $this->$autoIncrementField ?: TRUE;
	}
	public function delete() {
		static::remove(array('_id' => new \MongoId($this->_id)), array('justOne' => TRUE));
		$this->_documentOriginal = $this->_documentCurrent = array();
	}
	public function __construct($data = array(), $original = TRUE) {
		if (is_object($data))
			$data = (array)$data;
		# TODO : id
		if ($data['id'] == 0)
			unset($data['id']);
		$this->_documentOriginal = $original ? $data : array();
		$this->_documentCurrent = $data;
	}
	public function __set($name, $value) {
		$this->_documentCurrent[$name] = $value;
	}
	public function &__get($name) {
		return $this->_documentCurrent[$name];
	}
	public function __isset($name) {
		return isset($this->_documentCurrent[$name]);
	}
	public function __unset($name) {
		unset($this->_documentCurrent[$name]);
	}
	public function getIterator() {
		return new \ArrayIterator($this->_documentCurrent);
	}
	
	/**
	*  Generate Sub-document
	*
	*  This method build the difference between the current sub-document,
	*  and the origin one. If there is no difference, it would do nothing,
	*  otherwise it would build a document containing the differences.
	*
	*  @param array  &$document    Document target
	*  @param string $parent_key   Parent key name
	*  @param array  $values       Current values 
	*  @param array  $past_values  Original values
	*
	*  @return FALSE
	*/
	final protected static function getCurrentDocument(&$document, Array $values, Array $past_values, $parent_key = '') {
		/**
		*  The current property is a embedded-document, now we're looking for differences with the previous value (because we're on an update).
		*/
		foreach ($values as $key => $value) {
			$super_key = $parent_key.$key;
			if (is_array($value)) {
				/**
				*  Inner document detected
				*/
				if (!array_key_exists($key, $past_values) || !is_array($past_values[$key]))
					/**
					*  We're lucky, it is a new sub-document, we simple add it
					*/
					$document['$set'][$super_key] = $value;
				else if (!static::getCurrentDocument($document, $value, $past_values[$key], $super_key.'.'))
					/**
					*  This is a document like this, we need to find out the differences to avoid network overhead. 
					*/
					return FALSE;
				continue;
			} else if (!isset($past_values[$key]) || $past_values[$key] !== $value)
				$document['$set'][$super_key] = $value;
		}

		foreach (array_diff(array_keys($past_values), array_keys($values)) as $key)
			$document['$unset'][$parent_key.$key] = 1;
		return TRUE;
	}
	public static function setup() {
		# static::drop();
		static::deleteIndexes();
		foreach ((array)static::$_schema['indexes'] as $name => $fields)
			static::ensureIndex(array_fill_keys($fields, TRUE), array('name' => $name));
		foreach ((array)static::$_schema['unique keys'] as $name => $fields)
			static::ensureIndex(array_fill_keys($fields, TRUE), array('name' => $name, 'unique' => TRUE));
		if (is_array(static::$_schema['primary key']))
			static::ensureIndex(array_fill_keys(static::$_schema['primary key'], TRUE), array('name' => 'primary key', 'unique' => TRUE));
	}
}