<?
namespace Product;
class Controller extends \Controller {
	const SIZE_LIMIT = 4194304;
	
	protected $permission = array('index' => 'access content', 'upload' => 'post contents', 'deleteUpload' => 'post contents');

	protected function index($A) {
		$this->view->pageTitle[] = __('Products');
		return new \GridView(NULL, \Model\Post::all()->filter('service', SERVICE_POST_PRODUCT)->map(function($doc) {
			foreach (\Model\Storage::all()->filter('service', SERVICE_STORAGE_PRODUCT)->filter('taxonomy', $doc->id)->sort('weight')->limit(1) as $image);
			$doc->content = '
	<div class="PhotoAlbum-photoFrame">
		<img src="'.\Model\Storage::thumbnail($image, 135, 135).'" />
	</div>
	<strong>'.$doc->title.'</strong>
	<p>'.$doc->product['description'].'</p>
';
		}), 'There are no products.');
	}
}