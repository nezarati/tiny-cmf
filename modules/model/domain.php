<?
namespace Model;
class Domain extends Model {
	public static $_schema = array(
		'fields' => array(
			'id' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'auto increment' => TRUE,
			),
			'parent' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'user' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'created' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'modified' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'expire' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'host' => array(
				'type' => 'string',
				'length' => 64,
				'not null' => TRUE,
				'filter' => 'strToLower',
			),
			'status' => array(
				'type' => 'integer',
				'length' => 1,
				'not null' => TRUE,
				'default' => 1,
			),
		),
		'indexes' => array(
			'updated' => array('parent', 'status', 'modified'),
			'user' => array('user'),
		),
		'unique keys' => array(
			'host' => array('host')
		),
		'primary key' => array('parent', 'id'),
	);
	
	protected function prePut() {
		$this->__soundex = soundex($this->host);
		$this->__soundexRev = soundex(strrev($this->host));
	}
	
	public static function id($host) {
		$result = \Model\Domain::all()->fields('id', 'parent')->filter('host', $host)->fetch();
		return $result->parent ?: $result->id;
	}
}