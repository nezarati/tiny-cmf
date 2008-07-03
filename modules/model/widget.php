<?
namespace Model;
class Widget extends Model {
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
			'weight' => array(
				'type' => 'integer',
				'length' => 2,
				'not null' => TRUE,
			),
			'status' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'default' => 1,
			),
			'module' => array(
				'type' => 'string',
				'length' => 64,
				'not null' => TRUE,
			),
			'callback' => array(
				'type' => 'string', # tinytext
				'length' => 255,
				'not null' => TRUE,
			),
			'title' => array(
				'type' => 'string', # tinytext
				'length' => 64,
				'not null' => TRUE,
			),
			'content' => array(
				'type' => 'string', # text
				'length' => 1024,
			),
		),
		'indexes' => array(
			'service' => array('service', 'status', 'weight'),
			'weight' => array('service', 'weight'),
		),
		'primary key' => array('service', 'id'),
	);
}