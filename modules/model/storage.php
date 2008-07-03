<?
namespace Model;
class Storage extends Model {
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
			'filename' => array(
				'type' => 'string',
				'length' => 64,
			),
			'description' => array(
				'type' => 'string',
				'length' => 255,
			),
			'length' => array(
				'type' => 'integer',
			),
			'md5' => array(
				'type' => 'string',
			),
			'user' => array(
				'type' => 'integer',
			),
			'taxonomy' => array(
				'type' => 'integer',
			),
			'created' => array(
				'type' => 'timestamp',
			),
			'modified' => array(
				'type' => 'timestamp',
			),
			'weight' => array(
				'type' => 'integer',
			),
			'downloads' => array(
				'type' => 'integer',
			),
			'metadata' => array(
				'type' => 'array',
			),
		),
		'indexes' => array(
			'filename' => array('service', 'filename'),
			'length' => array('service', 'length'),
			'user' => array('service', 'user'),
			'taxonomy' => array('service', 'taxonomy'),
			'created' => array('service', 'created'),
			'weight' => array('service', 'weight'),
		),
		'fulltext' => array('filename', 'description'),
		'primary key' => array('service', 'id'),
	);
	protected $operatorNew = FALSE;
	protected static function collectionName() {
		return 'Storage.files';
	}
	public static function load($service, $id) {
		return self::all()->filter('service', $service)->filter('id', $id)->fetch();
	}
	
	protected function prePut() {		
		$this->modified = GMT;
		if (!isset($this->id)) {
			$this->created = GMT;
			$this->user = USER_ID;
			$this->weight = $this->downloads = 0;
			$this->id = $this->primaryKey();
			if (isset($this->data)) {
				$data = $this->data;
				unset($this->data);
				self::$_databases[self::$_database]->getGridFS('Storage')->storeBytes($data, $this->_documentCurrent);
			}
			
			$this->operatorNew = TRUE;
		}
	}
	protected function onPut() {
		\View::status(!$this->operatorNew ? __('The changes have been saved.') : __('The :name has been added.', array('%name' => $this->filename)));
	}
	
	public function remove($filters) {
		$fs = self::$_databases[self::$_database]->getGridFS('Storage');
		foreach ($fs->find($filters, array('_id' => TRUE, 'filename' => TRUE)) as $doc) {
			$fs->delete($doc->file['_id']);
			\View::status(__(':name has been deleted.', array('%name' => $doc->file['filename'])));
		}
	}
	
	public static function thumbnail($uri, $width, $height) {
		if (is_object($uri))
			$uri = self::url($uri->service, $uri->id);
		return (JOORCHIN_DEBUG_MODE ? 'http://127.0.0.1/api/thumbnail' : 'http://api.chonoo.com/thumbnail').'?src='.urlencode($uri).'&w='.$width.'&h='.$height;
	}
	public static function url($service, $id) {
		return (JOORCHIN_DEBUG_MODE ? 'http://127.0.0.1/api/storage/' : 'http://api.chonoo.com/storage/').$service.'/'.$id;
	}
	
	public static function feed($service) {
		return \Model\Storage::all()->filter('service', $service)->sort('weight');
	}
}