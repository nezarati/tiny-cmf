<?
namespace Model;
class Comment extends Model {
	const STATUS_NOT_PUBLISH = 0, STATUS_PUBLISH = 1, STATUS_PRIVATE = 2, STATUS_SPAM = 4;
	
	public static $_schema = array(
		'fields' => array(
			'service' => array(
				'type' => 'integer',
				'not null' => TRUE,
			),
			'id' => array(
				'type' => 'integer',
				'not null' => TRUE,
				'auto increment' => TRUE,
			),
			'parent' => array(
				'type' => 'integer',
				'not null' => TRUE,
			),
			'node' => array(
				'type' => 'integer',
				'not null' => TRUE,
			),
			'user' => array(
				'type' => 'integer',
				'not null' => TRUE,
				'default' => USER_ID,
			),
			'created' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
				'default' => GMT,
			),
			'status' => array(
				'type' => 'integer',
				'length' => 1,
				'not null' => TRUE,
				'description' => 'The published status of a comment. (0 = Inactive, 1 = Active, 2 = Private, 4 = SPAM)',
			),
			'name' => array(
				'type' => 'string',
				'length' => 64,
				'default' => NULL,
			),
			'mail' => array(
				'type' => 'string',
				'length' => 64,
				'not null' => TRUE,
			),
			'homepage' => array(
				'type' => 'string',
				'length' => 255,
				'default' => NULL,
			),
			'hostname' => array(
				'type' => 'string',
				'length' => 128,
				'not null' => TRUE,
				'default' => IP,
			),
			'subject' => array(
				'type' => 'string',
				'length' => Model::TINY_TEXT,
				'not null' => TRUE,
			),
			'content' => array(
				'type' => 'string', # text
				'length' => Model::TEXT,
				'not null' => TRUE,
			),
		),
		'indexes' => array(
			'parent' => array('service', 'parent'),
			'user' => array('service', 'user'),
			'node' => array('service', 'node', 'status', 'created'),
			'created' => array('service', 'created'),
			'status' => array('service', 'status'),
			'__parent' => array('service', '__parent'),
		),
		'fulltext' => array('content'),
		'primary key' => array('service', 'id'),
	);
	
	protected function prePut() {
		$this->__parent = $this->parent ?: (isset($this->id) ? $this->id : 0); # if(parent = 0, id, parent)
	}
}