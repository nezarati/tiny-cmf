<?
namespace Model;
class Module extends Model {
	public static $_schema = array(
		'fields' => array(
			'id' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'auto increment' => TRUE,
			),
			'name' => array(
				'type' => 'string',
				'length' => 64,
				'not null' => TRUE,
			),
			'version' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'default' => 100,
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
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'default' => 31536000,
			),
			'price' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'default' => 0,
			),
			'title' => array(
				'type' => 'string',
				'length' => 64,
				'not null' => TRUE,
			),
			'description' => array(
				'type' => 'string', # text
				'length' => 1024
			),
			'status' => array(
				'type' => 'integer',
				'length' => 1,
				'not null' => TRUE,
				'default' => 1,
			),
		),
		'primary key' => array('id'),
	);
}