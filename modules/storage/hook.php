<?
namespace Storage;
class Hook {
	public function __construct() {
		\Hook::add('style', '\Storage\Hook::style');
		\Hook::add('script', '\Storage\Hook::script');
		
		\Hook::add('permission', '\Storage\Hook::permission');
		
		foreach (\Service::required('storage') as $module) {
			\Hook::add($module->name.'_presave', '\Storage\Hook::postPresave');
			\Hook::add($module->name.'_delete', '\Storage\Hook::postDelete');
			\Hook::add($module->name.'_form', '\Storage\Hook::postForm');
		}
	}
	
	public static function style() {
		return __DIR__.'/fileuploader.css';
	}
	public static function script() {
		return __DIR__.'/fileuploader.js';
	}
	
	public static function permisison() {
		return array(
			'administer taxonomy' => array(
				'title' => 'Administer uploads'
			),
		);
	}
	
	public static function postForm($form, $data) {
		$_SESSION['product']['id'] = $data->id;
		
		$uploads = array();
		foreach (\Model\Storage::feed(\Service::identifier('storage', $_REQUEST['arg']['service']))->filter('taxonomy', $data->id) as $doc)
			$uploads[] = '$(".qq-upload-list").append(\'<li class="qq-upload-success"><span class="qq-upload-file">'.$doc->filename.'</span><span class="qq-upload-size" style="display: inline;">'.\Main\API::byteConvert($doc->length).'</span><a href="/storage/'.$doc->id.'/'.$_REQUEST['arg']['service'].'/delete" class="qq-upload-cancel" rel="delete">'.__('Delete').'</a></li>\');';
		$form->upload->attr(array('type' => 'fieldset', 'legend' => __('Upload Files')));
		$form->upload = \View::uploadFile('
(function upload() {
	if (window.qq && window.Ø("file-uploader")) {
		new qq.FileUploader({
			element: Ø("file-uploader"),
			action: "/storage/'.((int)$data->id).'/'.$_REQUEST['arg']['service'].'/upload",
		});
		'.implode($uploads).'
	} else
		setTimeout(upload, 1);
})();
').'<div id="file-uploader"></div>';
	}
	public static function postPresave($data) {
		if (!$data->id)
			$data->id = $_SESSION[$_REQUEST['arg']['service']]['id'];
		unset($_SESSION[$_REQUEST['arg']['service']]['id']);
	}
	public static function postDelete($service, $id) {
		\Model\Storage::all()->filter('service', \Service::identifier('storage', $service))->filter('taxonomy', $id)->fields('_id')->delete();
	}
}