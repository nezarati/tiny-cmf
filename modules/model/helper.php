<?
namespace Model;
class DataStore {
	public static function connection() {
		return Model::$_databases[Model::$_database];
	}
}
final class DataStoreAll extends \MongoCursor implements \Countable {
	protected static $operators = array(
		'<' => '$lt',
		'<=' => '$lte',
		'>' => '$gt',
		'>=' => '$gte',
		'!=' => '$ne',
		'in' => '$in',
		'!in' => '$nin',
		'all' => '$all',
		'size' => '$size',
		'exists' => '$exists',
		'type' => '$type'
	);
	protected $_filters = array(), $_sort = array();
	protected $_doQuery = FALSE, $_model;
	
	public function __construct(&$connection, $namespace, $className) {
		parent::__construct($connection, $namespace);
		$this->_model = $className;
	}
	public function filter($field, $value, $operator = '==') { # TODO: Schema
		$value = static::sanatize($value);
		$model = $this->_model;
		if ($filter = $model::$_schema['fields'][$field]['filter'] ?: Model::$_columns[$model::$_schema['fields'][$field]['type']]['filter'])
			if ($operator == 'in')
				foreach ($value as &$v)
					$v = call_user_func($filter, $v);
			else
				$value = call_user_func($filter, $value);
		if ($operator == '==')
			$this->_filters[$field] = $value;
		else if ($operator == '=~')
			$this->_filters[$field] = new \MongoRegex($value);
		else
			$this->_filters[$field][self::$operators[$operator]] = $value;
		return $this;
	}
	protected static function sanatize($var) {
		if (is_array($var)) {
			$newVar = array();
			foreach($var as $key => $value) {
				$newKey = $key;
				if (is_string($key))
					$newKey = str_replace(array(chr(0), '$'), '', $key);
				if (is_array($value))
					$newVar[$newKey] = static::sanatize($value);
				else
					$newVar[$newKey] = $value;
			}
			return $newVar;
		}
		return $var;
	}

	public function sort() {
		foreach (array_filter(func_get_args()) as $field)
			if ($field[0] != '-')
				$this->_sort[$field] = \MongoCollection::ASCENDING;
			else
				$this->_sort[subStr($field, 1)] = \MongoCollection::DESCENDING;
		parent::sort($this->_sort); # TODO
		return $this;
	}
	public function limit($length, $offset = NULL) {
		$this->offset($offset);
		return parent::limit(abs((int)$length));
	}
	public function offset($start) {
		return parent::skip(abs((int)$start));
	}

	public function fetch() {
		$this->limit(1)->rewind();
		return $this->current();
	}
	public function count() {
		$this->_execute();
		return parent::count();
	}
	public function explain() {
		$this->_execute();
		return parent::explain();
	}

	public function rewind() {
		$this->_execute();
		parent::rewind();
	}
	protected function _execute() {
		if (!$this->_doQuery) {
			parent::addOption('$query', $this->_filters);
			parent::addOption('$orderby', $this->_sort);
			$this->_doQuery = TRUE;
		}
	}
	public function current() {
		$data = parent::valid() ? parent::current() : array();
		if (!$data)
			return NULL;
		if (is_callable($this->_model)) {
			$data = (object)$data;
			$func = $this->_model;
			$func($data);
			return $data;
		} else if (is_null($this->_model))
			return (object)$data;
		else
			return new $this->_model($data);
	}
	
	public function delete($options = 0) {
		return call_user_func(array($this->_model, 'remove'), $this->_filters, $this->options($options));
	}
	public function update($document, $options = 0) {
		return call_user_func(array($this->_model, 'update'), $this->_filters, array('$set' => $document), $this->options($options));
	}
	protected static function options($options) {
		$result = array();
		foreach (array(Model::JUST_ONE => 'justOne', Model::UPSERT => 'upsert', Model::MULTIPLE => 'multiple') as $option => $name)
			if ($options & $option)
				$result[$name] = TRUE;
		return $result;
	}
	
	public function fetchAllKeyed($key, $value) {
		$result = array();
		foreach ($this as $doc)
			$result[$doc->$key] = $doc->$value;
		return $result;
	}
	public function fetchField() {
		$this->limit(1)->rewind();
		return is_array($doc = parent::current()) ? array_pop($doc) : NULL;
	}
	public function map($func = NULL) {
		$this->_model = $func;
		return $this;
	}
	public function orderBy($field, $sort = 'asc') {
		return $this->sort((strToLower($sort) == 'desc' ? '-' : '').$field);
	}
	public function extend($className) {
		$extend = new $className($this);
		$extend->preExecute();
		return $this;
	}
	public function fields() {
		parent::fields(array_fill_keys(func_get_args(), TRUE));
		return $this;
	}
}
abstract class Query {
	public function __construct($model, $sql, $arg) {
		/*
SELECT [* | __key__] FROM <kind>
	[WHERE <condition> [AND <condition> ...]]
	[ORDER BY <property> [ASC | DESC] [, <property> [ASC | DESC] ...]]
	[LIMIT [<offset>,]<count>]
	[OFFSET <offset>]

	<condition> := <property> {< | <= | > | >= | = | != } <value>
	<condition> := <property> IN <list>
	<condition> := ANCESTOR IS <entity or key>
*/
		if (!preg_match('select\s+(?P<fields>.*?)\s+from\s+(?P<table>.*?)(?:\s+where\s+(?P<where>.*?))?(?:\s+limit\s+(?P<limit>.*?)(?:\s+offset\s+(?P<offset>.*?))?)?', $sql, $mathes))
			throw new Exception('This query not valid format!');
		$query = $model::all();
	}
	abstract function escape();
}
class SelectQueryExtender {
	protected $query;
	public function __construct($query) {
		$this->query = $query;
	}
}