<?
namespace Model;
class Registry extends Model {
	public static $_schema = array(
		'fields' => array(
			'module' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'service' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'name' => array(
				'type' => 'string',
				'length' => 255,
				'not null' => TRUE,
			),
			'value' => array(
				'type' => 'string|array', # TODO: mediumtext
				'length' => Model::TEXT,
				'not null' => TRUE,
			),
			'autoload' => array( # TODO
				'type' => 'integer',
				'length' => 1,
				'not null' => TRUE,
				'default' => 0,
			),
			'serialized' => array( # TODO
				'type' => 'integer',
				'length' => 1,
				'not null' => TRUE,
				'description' => 'A flag to indicate whether content is serialized (1) or not (0).',
			),
		),
		'primary key' => array('service', 'module', 'name'),
	);
}