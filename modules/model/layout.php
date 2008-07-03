<?
namespace Model;
class Layout extends Model {
	public static $_schema = array(
		'fields' => array(
			'id' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'auto increment' => TRUE,
			),
			'user' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'default' => 1,
			),
			'created' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'modified' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'public' => array(
				'type' => 'integer',
				'length' => 1,
				'not null' => TRUE,
			),
			'name' => array(
				'type' => 'string', # tinytext
				'length' => 64,
				'not null' => TRUE,
			),
			'main' => array(
				'type' => 'string', # longtext
				'length' => Model::LONG_TEXT,
				'not null' => TRUE,
			),
			'block' => array(
				'type' => 'string', # longtext
				'length' => Model::LONG_TEXT,
				'not null' => TRUE,
			),
			'image' => array(
				'type' => 'string',
				'length' => Model::TINY_TEXT,
				'not null' => TRUE,
			),
		),
		'primary key' => array('id'),
	);
	
	public static function loadById($id = NULL) {
		return self::all()->filter('id', is_numeric($id) ? $id : \Registry::getInstance()->layout)->fetch(); # TODO: Can access
	}
	public static function block($name) {
		$block = new \SimpleXMLElement(self::loadById()->block);
		return trim($block->$name);
	}
}