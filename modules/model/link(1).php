<?
namespace Model;
class Link extends Model {
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
			'taxonomy' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'hit' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'created' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'status' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'title' => array(
				'type' => 'string', # tinytext
				'length' => 64,
				'not null' => TRUE,
			),
			'description' => array(
				'type' => 'string', # text
				'length' => 255
			),
			'url' => array(
				'type' => 'string',
				'length' => 255,
				'not null' => TRUE,
			),
		),
		'indexes' => array(
			'category' => array('service', 'taxonomy'),
			'count' => array('service', 'hit'),
			'created' => array('service', 'created'),
			'publish' => array('service', 'status'),
			'__titleLength' => array('service', '__titleLength'),
		),
		'fulltext' => array('title', 'description'),
		'primary key' => array('service', 'id'),
	);
	
	protected function prePut() {
		$this->__titleLength = mb_strlen($this->title); # length(title)
	}
}