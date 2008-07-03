<?
namespace Model;
class Poll extends Model {
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
			'created' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'expires' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'multiple' => array(
				'type' => 'integer',
				'length' => 2,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'default' => 1,
			),
			'status' => array(
				'type' => 'integer',
				'length' => 1,
				'not null' => TRUE,
				'default' => 1,
			),
			'question' => array(
				'type' => 'string', # text
				'length' => 255,
				'not null' => TRUE,
			),
			'choices' => array(
				'type' => 'array', # text [{label: , count: }]
				'not null' => TRUE,
			),
		),
		'indexes' => array(
			'created' => array('service', 'created'),
		),
		'primary key' => array('service', 'id'),
	);
}