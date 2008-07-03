<?
class DB {
	const RETURN_NULL = 0, RETURN_STATEMENT = 1, RETURN_AFFECTED = 2, RETURN_INSERT_ID = 3;
	protected static $instance, $handle, $prefix = '', $model;
	protected $connection;
	public static function instance($uri = 'mysql://root:@127.0.0.1/cms') {
		if (static::$instance)
			return static::$instance;
		self::$handle = parse_url($uri);
		static::$instance = new self(self::$handle['host'], self::$handle['user'], @self::$handle['pass'], substr(self::$handle['path'], 1), @self::$handle['port']);
		return static::$instance;
	}
	protected function __construct($host = NULL, $user = NULL, $passwd = NULL, $db = 'cms') {
		if (!$this->connection = mysql_pconnect($host, $user, $passwd))
			throw new DBException('Connection Failed!');
		if (!mysql_select_db($db))
			throw new DBException('Can not select Data base!');
		mysql_set_charset('UTF8', $this->connection);
		$this->model = function() {
		};
	}
	public function __set_state() {
		// mysql_get_*
		return mysql_info();
	}
	// TODO: Benchmark for any user
	public static function query($sql, Array $placeholders = NULL, Array $options = array()) {
		$_sql = $sql;
		$sql = preg_replace('/\{(\S+?)\}([\s\.,]|$)/', '`'.static::$prefix.'\\1`\\2', $sql);
		if (is_array($placeholders))
			foreach ($placeholders as $placeholder => $value)
				$sql = str_replace(':'.$placeholder, '"'.(is_array($value) ? implode('", "', array_map('static::escape', $value)) : static::escape($value)).'"', $sql);
		$options += array('return' => static::RETURN_STATEMENT);
		# Bootstrap::DEBUG_MODE && FB::log($sql, 'SQL');
		if (!$result = mysql_query($sql, static::$instance->connection))
			throw new DBException(mysql_error().' - ['.$_sql.']', mysql_errno());
		switch ($options['return']) {
			case static::RETURN_STATEMENT:
				$data = new DBResult($result, isset($options['model']) ? $options['model'] : static::$instance->model);
				if (count($data)) {
					$table = array();
					/*foreach ($data[0] as $key => $value)
						$table[0][] = $key;
					foreach ($data as $row)
						$table[] = (array)$row;
					FB::table($sql, $table);
					*/
				}
				return $data;
			case static::RETURN_AFFECTED:
				return mysql_affected_rows(static::$instance->connection);
			case static::RETURN_INSERT_ID:
				return mysql_insert_id(static::$instance->connection);
			case static::RETURN_NULL:
				return;
			default:
				throw new DBException('Invalid return directive: ' . $options['return']);
		}
	}
	public static function escape($string) {
		return mysql_real_escape_string($string, static::$instance->connection);
	}
	  /**
   * Escapes a table name string.
   *
   * Force all table names to be strictly alphanumeric-plus-underscore.
   * For some database drivers, it may also wrap the table name in
   * database-specific escape characters.
   *
   * @return
   *   The sanitized table name string.
   */
	public function escapeTable($table) {
		return preg_replace('/[^A-Za-z0-9_]+/', '', $table);
	}

  /**
   * Escape characters that work as wildcard characters in a LIKE pattern.
   *
   * The wildcard characters "%" and "_" as well as backslash are prefixed with
   * a backslash. Use this to do a seach for a verbatim string without any
   * wildcard behavior.
   *
   * For example, the following does a case-insensitive query for all rows whose
   * name starts with $prefix:
   * @code
   * $result = db_query(
   *   'SELECT * FROM person WHERE name LIKE :pattern',
   *   array(':pattern' => db_like($prefix) . '%')
   * );
   * @endcode
   *
   * Backslash is defined as escape character for LIKE patterns in
   * DatabaseCondition::mapConditionOperator().
   *
   * @param $string
   *   The string to escape.
   * @return
   *   The escaped string.
   */
	public function escapeLike($string) {
		return addcslashes($string, '\%_');
	}
	
	public function ConditionOr() {
		return new DatabaseCondition('OR');
	}
	
	public static function select($table, $alias) {
		$select = new DBSelect($table, $alias);
		$select->table($table, $alias);
		return $select;
	}
	public static function insert($table) {
		return new DBInsert($table);
	}
	public static function update($table) {
		return new DBUpdate($table);
	}
	public static function delete($table) {
		return new DBDelete($table);
	}
}
class DBException extends Exception {
}
class DBResult implements Iterator, Countable, ArrayAccess {
	protected $result, $position, $rowData, $model;
	public $rowCount = 0;

	public function __construct($result, $model) {
		$this->result = $result;
		$this->position = 0;
		$this->model = $model;
		$this->rowCount = mysql_num_rows($this->result);
	}
	
	public function rewind() {
		if (!count($this))
			return;
		mysql_data_seek($this->result, $this->position = 0);
		/* The initial call to valid requires that data
			pre-exists in $this->rowData
		*/
		$this->rowData = mysql_fetch_object($this->result);
	}
	public function valid() {
		return (boolean) $this->rowData;
	}
	public function current() {
		$func = $this->model;
		$func($this->rowData);
		return $this->rowData;
	}
	public function key() {
		return $this->position;
	}
    public function next() {
		$this->position++;
		$this->rowData = mysql_fetch_object($this->result);
	}
	
	public function count() {
		return $this->rowCount;
	}
	
	public function offsetSet($offset, $value) {
		throw new Exception('Can not use DBResult[index] = value');
	}
	public function offsetExists($offset) {
		return count($this)>$offset;
	}
	public function offsetUnset($offset) {
		throw new Exception('Can not use unset(DBResult[index])');
	}
	public function offsetGet($offset) {
		if (!$this->offsetExists($offset))
			throw new Exception('Offset '.$offset.' is invalid for MySQL result index '.count($this));
		mysql_data_seek($this->result, $offset);
		$row = mysql_fetch_object($this->result);
		$func = $this->model;
		$func($row);
		return $row;
	}
	
	public function fetch($row = 0) {
		if (empty($this[$row]))
			return NULL;
		mysql_data_seek($this->result, $row);
		$row = mysql_fetch_object($this->result);
		$func = $this->model;
		$func($row);
		return $row;
	}
	public function fetchField($field = 0) {
		if (isset($this[0])) {
			$row = mysql_fetch_row($this->result);
			return $row[$field];
		}
	}
	public function fetchCol($column) {
		$result = array();
		foreach ($this as $row)
			$result[] = $row->$column;
		return $result;
	}
	public function fetchAllKeyed($key, $value) {
		$result = array();
		foreach ($this as $row)
			$result[$row->$key] = $row->$value;
		return $result;
	}
	
	public function __destruct() {
		mysql_free_result($this->result);
	}
}
/**
 * Interface for a query that accepts placeholders.
 */
interface QueryPlaceholderInterface {

  /**
   * Returns the next placeholder ID for the query.
   *
   * @return
   *   The next available placeholder ID as an integer.
   */
  function nextPlaceholder();
}
abstract class DBQuery implements QueryAlterableInterface, QueryPlaceholderInterface {
	protected $tables = array(), $fields = array(), $join, $where, $group_by, $order_by = array(), $limit, $offset;
	protected $alterTags = array(), $alterMetaData = array();
	protected $nextPlaceholder = 0;
	
	public function __construct() {
		$this->where = new DatabaseCondition('AND');
	}
	public function table($table, $alias = NULL) {
		$this->tables[] = '`'.DB::escapeTable($table).'` '.$alias;
		return $this;
	}
	public function fields($table, Array $column = NULL) {
		if (is_array($column))
			foreach ($column as $field)
				$this->fields[] = $table.'.'.$field;
		else
			$this->fields[] = $table.'.*';
		return $this;
	}
//	TODO:JOIN
	public function join($table, $alias, $condition) {
		
	}
	public function groupBy($column) {
		$this->group_by[] = $column;
		return $this;
	}
	public function orderBy($order, $sort = 'ASC') {
		$order && ($this->order_by[] = $order.' '.$sort);
		return $this;
	}
	public function limit($length = NULL, $offset = NULL) {
		is_numeric($length) && $this->limit = $length;
		is_numeric($offset) && $this->offset = $offset;
		return $this;
	}
	public function offset($start) {
		$this->offset = $start;
		return $this;
	}
	abstract public function execute();
	public function __toString() {
		if (count($this->where)) {
			$this->where->compile(/*$this->connection, */$this);
			// There is an implicit string cast on $this->condition.
			$sql = ' where '.$this->where;
		}
		if (count($this->group_by))
			$sql .= ' group by '.implode(', ', $this->group_by);
		if (count($this->order_by))
			$sql .= ' order by '.implode(', ', $this->order_by);
		if (is_numeric($this->limit))
			$sql .= ' limit '.$this->limit;
		if (is_numeric($this->offset))
			$sql .= ' offset '.$this->offset;
		return $sql;
	}
	
	public function addTag($tag) {
		$this->alterTags[$tag] = 1;
		return $this;
	}
	public function hasTag($tag) {
		return isset($this->alterTags[$tag]);
	}
	public function hasAllTags() {
		return !(boolean)array_diff(func_get_args(), array_keys($this->alterTags));
	}
	public function hasAnyTag() {
		return (boolean)array_intersect(func_get_args(), array_keys($this->alterTags));
	}
	public function addMetaData($key, $object) {
		$this->alterMetaData[$key] = $object;
		return $this;
	}
	public function getMetaData($key) {
		return isset($this->alterMetaData[$key]) ? $this->alterMetaData[$key] : NULL;
	}

	/* Implementations of QueryConditionInterface for the WHERE clause. */
	public function condition($field, $value = NULL, $operator = NULL) {
		if (!isset($num_args))
			$num_args = func_num_args();
		$this->where->condition($field, $value, $operator, $num_args);
		return $this;
	}
	public function &conditions() {
		return $this->where->conditions();
	}
	public function arguments() {
		return $this->where->arguments();
	}
	public function where($snippet, $args = array()) {
		$this->where->where($snippet, $args);
		return $this;
	}
	public function isNull($field) {
		$this->where->isNull($field);
		return $this;
	}
	public function isNotNull($field) {
		$this->where->isNotNull($field);
		return $this;
	}
	public function compile(/*DatabaseConnection $connection, */QueryPlaceholderInterface $queryPlaceholder = NULL) {
		return $this->where->compile(/*$connection, */isset($queryPlaceholder) ? $queryPlaceholder : $this);
	}
	
	public function nextPlaceholder() {
		return $this->nextPlaceholder++;
	}
}
interface SelectQueryInterface {
#	function preExecute();
#	public function execute();
}
class DBSelect extends DBQuery implements QueryPlaceholderInterface, SelectQueryInterface {
	protected $model, $expressions = array();
	public function addExpression($expression, $alias) {
		$this->expressions[$alias] = $expression.' '.$alias;
		return $this;
	}
	public function count() {
		static $count = array();
		$sql = 'select count(*) from ('.preg_replace(array('/SELECT.*?FROM /Asi', '/ORDER BY .*/i'), array('SELECT 1 FROM ', ''), $this).') count_table';
		return isset($count[$sql]) ? $count[$sql] : $count[$sql] = DB::query($sql, $this->where->arguments())->fetchField();
	}
	public function model($func) {
		$this->model = $func;
		return $this;
	}
	public function extend($extender_name) {
		return new $extender_name($this);
	}
	public function __toString() {
		return 'select '.($this->fields ? implode(', ', $this->fields) : '').($this->expressions ? ($this->fields ? ', ' : '').implode(', ', $this->expressions) : '').' from '.implode(', ', $this->tables).parent::__toString();
	}
	public function execute() {
		return DB::query((string)$this, $this->where->arguments(), array('model' => $this->model));
	}
}
class SelectQueryExtender implements SelectQueryInterface {
	protected $query;
	public function __construct(SelectQueryInterface $query) {
		$this->query = $query;
	}
	/**
	* Magic override for undefined methods.
	*
	* If one extender extends another extender, then methods in the inner extender
	* will not be exposed on the outer extender.  That's because we cannot know
	* in advance what those methods will be, so we cannot provide wrapping
	* implementations as we do above.  Instead, we use this slower catch-all method
	* to handle any additional methods.
	*/
	public function preExecute() {
		return TRUE;
	}
	public function execute() {
		if ($this->preExecute())
			return $this->query->execute();
	}
	public function extend($extender_name) {
		return new $extender_name($this);
	}
	public function __toString() {
		return $this->query->__toString();
	}
	public function __call($method, $args) {
		$return = call_user_func_array(array($this->query, $method), $args);

		// Some methods will return the called object as part of a fluent interface.
		// Others will return some useful value.  If it's a value, then the caller
		// probably wants that value.  If it's the called object, then we instead
		// return this object.  That way we don't "lose" an extender layer when
		// chaining methods together.
		return $return instanceof SelectQueryInterface ? $this : $return;
	}
}

class DBUpdate extends DBQuery {
	protected $table;
	public function __construct($table) {
		$this->table = $table;
		parent::__construct();
	}
	public function fields($fields) {
		$this->fields = is_object($fields) ? (array)$fields : $fields;
		return $this;
	}
	public function __toString() {
		foreach (array_keys($this->fields) as $index => $field)
			$update_fields[] = '`'.$field.'` = :'.$index.'_';
		return 'UPDATE '.$this->table.' SET '.implode(',', $update_fields).parent::__toString();
	}
	public function execute() {
		return DB::query((string)$this, array_combine(array_map(function($i) {return $i.'_';}, range(0, count($this->fields)-1)), array_values($this->fields))+($this->where->arguments() ?: array()), array('return' => DB::RETURN_AFFECTED));
	}
}
class DBInsert extends DBQuery {
	protected $table, $insertFields = array(), $insertValues = array();
	public function __construct($table) {
		$this->table = DB::escapeTable($table);
	}
	public function fields($fields, Array $values = array()) {
		if (empty($this->insertFields)) {
			if (empty($values)) {
				if (is_object($fields))
					foreach ($fields as $field => $value) {
						$values[] = $value;
						$columns[] = $field;
					}
				else if (!is_numeric(key($fields))) {
					$values = array_values($fields);
					$columns = array_keys($fields);
				}
			} else
				$columns = $fields;
			$this->insertFields = $columns;
			$this->insertValues = $values;
		}
		return $this;
	}
	public function __toString() {
		return 'INSERT INTO {' . $this->table . '} (`' . implode('`, `', $this->insertFields) . '`) VALUES (:' . implode('_, :', array_keys($this->insertFields)) . '_)';
	}
	public function execute() {
		return DB::query($this, array_combine(array_map(function($i) {return $i.'_';}, range(0, count($this->insertValues)-1)), array_values($this->insertValues)), array('return' => DB::RETURN_INSERT_ID));
	}
}
class DBDelete extends DBQuery {
	protected $table;
	public function __construct($table) {
		$this->table = DB::escapeTable($table);
		parent::__construct();
	}
	public function __toString() {
		return 'DELETE FROM '.$this->table.parent::__toString();
	}
	public function execute() {
		return DB::query((string)$this, $this->where->arguments(), array('return' => DB::RETURN_AFFECTED));
	}
}

interface QueryConditionInterface {

  /**
   * Helper function to build most common conditional clauses.
   *
   * This method can take a variable number of parameters. If called with two
   * parameters, they are taken as $field and $value with $operator having a value
   * of =.
   *
   * @param $field
   *   The name of the field to check.
   * @param $value
   *   The value to test the field against. In most cases, this is a scalar. For more
   *   complex options, it is an array. The meaning of each element in the array is
   *   dependent on the $operator.
   * @param $operator
   *   The comparison operator, such as =, <, or >=. It also accepts more complex
   *   options such as IN, LIKE, or BETWEEN. Defaults to IN if $value is an array
   *   = otherwise.
   * @return QueryConditionInterface
   *   The called object.
   */
  public function condition($field, $value = NULL, $operator = NULL);

  /**
   * Add an arbitrary WHERE clause to the query.
   *
   * @param $snippet
   *   A portion of a WHERE clause as a prepared statement. It must use named placeholders,
   *   not ? placeholders.
   * @param $args
   *   An associative array of arguments.
   * @return QueryConditionInterface
   *   The called object.
   */
  public function where($snippet, $args = array());

  /**
   * Set a condition that the specified field be NULL.
   *
   * @param $field
   *   The name of the field to check.
   * @return QueryConditionInterface
   *   The called object.
   */
  public function isNull($field);

  /**
   * Set a condition that the specified field be NOT NULL.
   *
   * @param $field
   *   The name of the field to check.
   * @return QueryConditionInterface
   *   The called object.
   */
  public function isNotNull($field);

  /**
   * Gets a complete list of all conditions in this conditional clause.
   *
   * This method returns by reference. That allows alter hooks to access the
   * data structure directly and manipulate it before it gets compiled.
   *
   * The data structure that is returned is an indexed array of entries, where
   * each entry looks like the following:
   *
   * array(
   *   'field' => $field,
   *   'value' => $value,
   *   'operator' => $operator,
   * );
   *
   * In the special case that $operator is NULL, the $field is taken as a raw
   * SQL snippet (possibly containing a function) and $value is an associative
   * array of placeholders for the snippet.
   *
   * There will also be a single array entry of #conjunction, which is the
   * conjunction that will be applied to the array, such as AND.
   */
  public function &conditions();

  /**
   * Gets a complete list of all values to insert into the prepared statement.
   *
   * @returns
   *   An associative array of placeholders and values.
   */
  public function arguments();

  /**
   * Compiles the saved conditions for later retrieval.
   *
   * This method does not return anything, but simply prepares data to be
   * retrieved via __toString() and arguments().
   *
   * @param $connection
   *   The database connection for which to compile the conditionals.
   * @param $query
   *   The query this condition belongs to. If not given, the current query is
   *   used.
   */
  public function compile(/*DatabaseConnection $connection, */QueryPlaceholderInterface $queryPlaceholder = NULL);
}
/**
 * Interface for a query that can be manipulated via an alter hook.
 */
interface QueryAlterableInterface {

  /**
   * Adds a tag to a query.
   *
   * Tags are strings that identify a query. A query may have any number of
   * tags. Tags are used to mark a query so that alter hooks may decide if they
   * wish to take action. Tags should be all lower-case and contain only letters,
   * numbers, and underscore, and start with a letter. That is, they should
   * follow the same rules as PHP identifiers in general.
   *
   * @param $tag
   *   The tag to add.
   * @return QueryAlterableInterface
   *   The called object.
   */
  public function addTag($tag);

  /**
   * Determines if a given query has a given tag.
   *
   * @param $tag
   *   The tag to check.
   * @return
   *   TRUE if this query has been marked with this tag, FALSE otherwise.
   */
  public function hasTag($tag);

  /**
   * Determines if a given query has all specified tags.
   *
   * @param $tags
   *   A variable number of arguments, one for each tag to check.
   * @return
   *   TRUE if this query has been marked with all specified tags, FALSE otherwise.
   */
  public function hasAllTags();

  /**
   * Determines if a given query has any specified tag.
   *
   * @param $tags
   *   A variable number of arguments, one for each tag to check.
   * @return
   *   TRUE if this query has been marked with at least one of the specified
   *   tags, FALSE otherwise.
   */
  public function hasAnyTag();

  /**
   * Adds additional metadata to the query.
   *
   * Often, a query may need to provide additional contextual data to alter
   * hooks. Alter hooks may then use that information to decide if and how
   * to take action.
   *
   * @param $key
   *   The unique identifier for this piece of metadata. Must be a string that
   *   follows the same rules as any other PHP identifier.
   * @param $object
   *   The additional data to add to the query. May be any valid PHP variable.
   * @return QueryAlterableInterface
   *   The called object.
   */
  public function addMetaData($key, $object);

  /**
   * Retrieves a given piece of metadata.
   *
   * @param $key
   *   The unique identifier for the piece of metadata to retrieve.
   * @return
   *   The previously attached metadata object, or NULL if one doesn't exist.
   */
  public function getMetaData($key);
}

class DatabaseCondition implements QueryConditionInterface, Countable {

  protected $conditions = array();
  protected $arguments = array();

  protected $changed = TRUE;

  public function __construct($conjunction) {
    $this->conditions['#conjunction'] = $conjunction;
  }

  /**
   * Return the size of this conditional. This is part of the Countable interface.
   *
   * The size of the conditional is the size of its conditional array minus
   * one, because one element is the the conjunction.
   */
  public function count() {
    return count($this->conditions) - 1;
  }

  public function condition($field, $value = NULL, $operator = NULL) {
    if (!isset($operator)) {
      $operator = is_array($value) ? 'IN' : '=';
    }
    $this->conditions[] = array(
      'field' => $field,
      'value' => $value,
      'operator' => $operator,
    );

    $this->changed = TRUE;

    return $this;
  }

  public function where($snippet, $args = array()) {
    $this->conditions[] = array(
      'field' => $snippet,
      'value' => $args,
      'operator' => NULL,
    );
    $this->changed = TRUE;

    return $this;
  }

  public function isNull($field) {
    return $this->condition($field, NULL, 'IS NULL');
  }

  public function isNotNull($field) {
    return $this->condition($field, NULL, 'IS NOT NULL');
  }

  public function &conditions() {
    return $this->conditions;
  }

  public function arguments() {
    // If the caller forgot to call compile() first, refuse to run.
    if ($this->changed) {
      return NULL;
    }
    return $this->arguments;
  }

  public function compile(/*DatabaseConnection $connection, */QueryPlaceholderInterface $queryPlaceholder = NULL) {
    if ($this->changed) {
      $condition_fragments = array();
      $arguments = array();

      $conditions = $this->conditions;
      $conjunction = $conditions['#conjunction'];
      unset($conditions['#conjunction']);
      foreach ($conditions as $condition) {
        if (empty($condition['operator'])) {
          // This condition is a literal string, so let it through as is.
          $condition_fragments[] = ' (' . $condition['field'] . ') ';
          $arguments += $condition['value'];
        }
        else {
          // It's a structured condition, so parse it out accordingly.
          // Note that $condition['field'] will only be an object for a dependent
          // DatabaseCondition object, not for a dependent subquery.
          if ($condition['field'] instanceof QueryConditionInterface) {
            // Compile the sub-condition recursively and add it to the list.
            $condition['field']->compile(/*$connection, */$queryPlaceholder);
            $condition_fragments[] = '(' . (string)$condition['field'] . ')';
            $arguments += $condition['field']->arguments();
          }
          else {
            // For simplicity, we treat all operators as the same data structure.
            // In the typical degenerate case, this won't get changed.
            $operator_defaults = array(
              'prefix' => '',
              'postfix' => '',
              'delimiter' => '',
              'operator' => $condition['operator'],
              'use_value' => TRUE,
            );
            #$operator = $connection->mapConditionOperator($condition['operator']);
            if (!isset($operator)) {
              $operator = $this->mapConditionOperator($condition['operator']);
            }
            $operator += $operator_defaults;

            $placeholders = array();
            if ($condition['value'] instanceof SelectQueryInterface) {
              $condition['value']->compile(/*$connection, */$queryPlaceholder);
              $placeholders[] = (string)$condition['value'];
              $arguments += $condition['value']->arguments();
            }
            // We assume that if there is a delimiter, then the value is an
            // array. If not, it is a scalar. For simplicity, we first convert
            // up to an array so that we can build the placeholders in the same way.
            elseif (!$operator['delimiter']) {
              $condition['value'] = array($condition['value']);
            }
            if ($operator['use_value']) {
              foreach ($condition['value'] as $value) {
                $placeholder = 'db_condition_placeholder_' . $queryPlaceholder->nextPlaceholder();
                $arguments[$placeholder] = $value;
                $placeholders[] = ':'.$placeholder;
              }
            }
            $condition_fragments[] = ' (`' . $condition['field'] . '` ' . $operator['operator'] . ' ' . $operator['prefix'] . implode($operator['delimiter'], $placeholders) . $operator['postfix'] . ') ';
          }
        }
      }

      $this->changed = FALSE;
      $this->stringVersion = implode($conjunction, $condition_fragments);
      $this->arguments = $arguments;
    }
  }

  public function __toString() {
    // If the caller forgot to call compile() first, refuse to run.
    if ($this->changed) {
      return NULL;
    }
    return $this->stringVersion;
  }

  function __clone() {
    $this->changed = TRUE;
    foreach ($this->conditions as $key => $condition) {
      if ($condition['field'] instanceOf QueryConditionInterface) {
        $this->conditions[$key]['field'] = clone($condition['field']);
      }
    }
  }

  /**
   * Gets any special processing requirements for the condition operator.
   *
   * Some condition types require special processing, such as IN, because
   * the value data they pass in is not a simple value. This is a simple
   * overridable lookup function.
   *
   * @param $operator
   *   The condition operator, such as "IN", "BETWEEN", etc. Case-sensitive.
   * @return
   *   The extra handling directives for the specified operator, or NULL.
   */
  protected function mapConditionOperator($operator) {
    // $specials does not use drupal_static as its value never changes.
    static $specials = array(
      'BETWEEN' => array('delimiter' => ' AND '),
      'IN' => array('delimiter' => ', ', 'prefix' => ' (', 'postfix' => ')'),
      'NOT IN' => array('delimiter' => ', ', 'prefix' => ' (', 'postfix' => ')'),
      'IS NULL' => array('use_value' => FALSE),
      'IS NOT NULL' => array('use_value' => FALSE),
      // Use backslash for escaping wildcard characters.
      'LIKE' => array('postfix' => " ESCAPE '\\\\'"),
      // These ones are here for performance reasons.
      '=' => array(),
      '<' => array(),
      '>' => array(),
      '>=' => array(),
      '<=' => array(),
    );
    if (isset($specials[$operator])) {
      $return = $specials[$operator];
    }
    else {
      // We need to upper case because PHP index matches are case sensitive but
      // do not need the more expensive drupal_strtoupper because SQL statements are ASCII.
      $operator = strtoupper($operator);
      $return = isset($specials[$operator]) ? $specials[$operator] : array();
    }

    $return += array('operator' => $operator);

    return $return;
  }

}