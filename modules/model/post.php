<?
namespace Model;
class Post extends Model {
	const FLAG_NOT_PUBLISHED = 0, FLAG_PUBLISHED = 1, FLAG_PROMOTED = 2, FLAG_STICKY = 4;
	
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
			'revision' => array( # TODO
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'taxonomy' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'user' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'created' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'modified' => array(
				'type' => 'timestamp',
				'default' => NULL,
			),
			'published' => array(
				'type' => 'timestamp',
				'default' => NULL,
			),
			'status' => array(
				'type' => 'integer',
				'length' => 1,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'description' => 'Boolean indicating whether the node is published (visible to non-administrators).',
			),
			'promote' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'description' => 'Boolean indicating whether the node should be displayed on the front page.',
				'default' => 1,
			),
			'sticky' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
				'description' => 'Boolean indicating whether the node should be displayed at the top of lists in which it appears.',
			),
			'title' => array(
				'type' => 'string',
				'length' => 128,
				'not null' => TRUE,
			),
			'content' => array(
				'type' => 'string', # longtext
				'length' => 32768,
				'not null' => TRUE,
			),
			'relatedLink' => array(
				'type' => 'array', # text
			),
			'image' => array(
				'type' => 'string', # tinytext
				'length' => 255,
			),
		),
		'indexes' => array(
			'frontpage' => array('service', 'promote', 'status', 'sticky', 'created'),
			'taxonomy' => array('service', 'taxonomy', 'status', 'sticky', 'created'),
			'user' => array('service', 'user', 'status', 'sticky', 'created'),
		),
		'fulltext' => array('title', 'content'),
		'primary key' => array('service', 'id'),
	);
	
	protected function prePut() {
		if (!isset($this->id)) {
			$this->created = GMT;
			$this->user = USER_ID;
		}
		$this->modified = GMT;
	}

	protected static $tmp = array();
	public function loadById($id, $service = SERVICE_POST) {
		return isset(self::$tmp[$service][$id]) ? self::$tmp[$service][$id] : self::$tmp[$service][$id] = self::all()->filter('service', $service)->filter('id', $id)->fetch();
	}
	public function saveById($data, $service = SERVICE_POST) {
		$data->service = $service;
		\View::status(__($data->id ? 'The changes have been saved.' : 'The :name has been added.', array('%name' => $data->title)));
		self::$tmp[$service][$id] = $data;
		return self::put($data);
	}
	public function deleteById($id, $service = SERVICE_POST) {
		\View::status(__(':name has been deleted.', array('%name' => self::loadById($id, $service)->title)));
		unset(self::$tmp[$service][$id]);
		return self::all()->filter('service', $service)->filter('id', $id)->delete();
	}
	
	public static function feed($service = SERVICE_POST) {
		$cfg = \Registry::getInstance('post', $service);
		return self::all()->fields('id', 'created', 'modified', 'title', 'content')->filter('service', $service)->orderBy($cfg->order, $cfg->sort)->limit($cfg->perFeed);
	}
	public static function siteMap($service = SERVICE_POST) { # TODO
		$cfg = \Registry::getInstance('post', $service);
		return self::all()->filter('service', $service)->sort('-created')->limit(500)->map(function($doc) {
			$doc->lastmod = max($doc->created, $doc->modified);
		});
	}
	
	public static function related() { # TODO
		// SELECT ID, title, content, MATCH (title, content) AGAINST ('$terms') AS score FROM post WHERE MATCH (title, content) AGAINST ('$terms') order by score desc
	}
	public static function archive() { # TODO
		// http://dev.mysql.com/doc/refman/5.5/en/locale-support.html
		// SET lc_time_names = 'fr_FR';
		// select from_unixtime(created, '%m') month, from_unixtime(created, '%Y') year, count(*) count, from_unixtime(created, '%M') Month from post group by year, month
	}
}