<?
namespace Storage;
class Controller extends \Controller {
	public function __construct() {
		\Service::exists('storage', $_REQUEST['arg']['service']) && define('SERVICE_STORAGE', \Service::identifier('storage', $_REQUEST['arg']['service']));
		parent::__construct();
		if (defined('SERVICE_STORAGE'))
			$this->view->pageTitle[] = __(\Service::load(\Service::id($_REQUEST['arg']['service']))->title);
		$this->view->pageTitle[] = __('Storage');
	}
	
	protected $permission = array('upload' => 'administer taxonomy', 'delete' => 'administer taxonomy');
	protected function upload($A) {
		$A->id = (int)$A->id;
		if (!$A->id)
			$A->id = $_SESSION[$A->service]['id'] ?: $_SESSION[$A->service]['id'] = \Post\API::save((object)array(), \Service::identifier('post', $A->service));
		
		$doc = array('service' => SERVICE_STORAGE, 'filename' => $_SERVER['HTTP_X_FILE_NAME'], 'taxonomy' => $_SESSION[$A->service]['id'], 'contentType' => image_type_to_mime_type($type), 'data' => $bytes = file_get_contents('php://input'));
			
		$storage = new \Model\Storage($doc); # PHP Bug
		$storage->put();
			
		die(json_encode(array('success' => 'Ok')));	
	}
	
	protected function delete($A) {
		\Model\Storage::remove(array('service' => SERVICE_STORAGE, 'id' => (int)$A->id));
	}
}