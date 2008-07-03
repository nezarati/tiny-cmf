<?
namespace Model;
class User extends Model {
	public static $_schema = array(
		'fields' => array(
			'service' => array(
				'type' => 'integer',
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'id' => array(
				'type' => 'integer',
				'unsigned' => TRUE,
				'not null' => TRUE,
				'auto increment' => TRUE,
			),
			'role' => array(
				'type' => 'integer',
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'created' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'access' => array(
				'type' => 'timestamp',
				'default' => NULL,
			),
			'username' => array(
				'type' => 'string',
				'length' => 30,
				'not null' => TRUE,
				'filter' => 'strToLower',
			),
			'password' => array(
				'type' => 'string',
				'length' => 34,
				'not null' => TRUE,
			),
			'mail' => array(
				'type' => 'string',
				'length' => 64,
				'not null' => TRUE,
				'filter' => 'strToLower',
			),
			'name' => array(
				'type' => 'string',
				'length' => 64,
				'not null' => TRUE,
			),
			'status' => array(
				'type' => 'integer',
				'length' => 1,
				'not null' => TRUE,
				'description' => 'Whether the user is active(1) or blocked(0).',
				'default' => 1,
			),
		),
		'indexes' => array(
			'role' => array('service', 'role'),
			'created' => array('service', 'created'),
			'access' => array('service', 'access'),
			'status' => array('service', 'status'),
		),
		'unique keys' => array(
			'username' => array('service', 'username'),
			'mail' => array('service', 'mail'),
			'name' => array('service', 'name'),
		),
		'primary key' => array('service', 'id'),
	);
}