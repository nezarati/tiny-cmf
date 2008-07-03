<?
namespace Model;
class Attempt extends Model {
	const COLLATION_NAME = 'Attempt';
	public static $_schema = array(
		'fields' => array(
			'hostname' => array(
				'type' => 'int',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'page' => array(
				'type' => 'varchar',
				'length' => 255,
				'not null' => TRUE,
			),
			'created' => array(
				'type' => 'int',
				'length' => 10,
				'not null' => TRUE,
			),
		),
		'indexes' => array(
			'hostname' => array('hostname'),
			'page' => array('page'),
		),
	);
}
