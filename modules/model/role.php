<?
namespace Model;
class Role extends Model {
	public static $_schema = array(
		'fields' => array(
			'service' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'id' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'auto increment' => TRUE,
			),
			'administer' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'description' => 'The user id can be administer this group.',
			),
			'name' => array(
				'type' => 'string',
				'length' => 64,
				'not null' => TRUE,
			),
			'permission' => array(
				'type' => 'array',
			),
		),
		'primary key' => array('service', 'id'),
	);
}