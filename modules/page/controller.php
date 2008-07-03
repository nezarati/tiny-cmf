<?
namespace Page;
class Controller extends \Controller {
	protected $permission = array('index' => 'access content');
	protected function index($A) {
		is_numeric($A->id) || die;
		$result = \Post\API::load($A->id, \Service::identifier('post', 'page'));
		$this->view->pageTitle[] = $result->title;
		return $result->content;
	}
}