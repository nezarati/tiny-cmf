<?
namespace Model;
class Taxonomy extends Model {
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
			'parent' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'default' => 0,
			),
			'weight' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'description' => 'The weight of this term in relation to other terms.',
			),
			'count' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'default' => 0,
			),
			'term' => array(
				'type' => 'string',
				'length' => 64,
				'not null' => TRUE,
			),
			'description' => array(
				'type' => 'string', # text
				'length' => 255
			),
			/*'moderator' => array(
				'type' => 'array',
				'not null' => TRUE,
			),*/ # TODO: Moderator
		),
		'indexes' => array(
			'parent_weight' => array('service', 'parent', 'weight'),
			'__parent' => array('service', '__parent'),
		),
		'primary key' => array('service', 'id'),
	);
	
	protected function prePut() {
		$this->__parent = $this->parent ?: (isset($this->id) ? $this->id : 0); # if(parent = 0, id, parent)
		
		if (!isset($this->modified)) {
			$this->created = GMT;
			$this->user = USER_ID;
		}
		$this->modified = GMT;
	}
}