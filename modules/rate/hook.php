<?
namespace Rate;
class Hook {
	public function __construct() {
		\Hook::add('permission', '\Rate\Hook::permission');
		foreach (\Service::required('rate') as $module) {
			\Hook::add($module->name.'_block', '\Rate\Hook::block', 3);
			\Hook::add($module->name.'_action', '\Rate\Hook::action', 3);
			\Hook::add($module->name.'_delete', '\Rate\Hook::delete', 3);
		}
		\Hook::add('script', '\Rate\Hook::script');
		\Hook::add('style', '\Rate\Hook::style');
	}
	
	public static function permission() {
		return array(
			'administer ratings' => array('title' => __('Administer ratings and rating settings')),
			'access ratings' => array('title' => __('View ratings')),
			'post ratings' => array('title' => __('Post ratings')),
		);
	}
	
	public static function block($module, $doc) {
		$doc->rate = function($attr) use($module, $doc) {
			return array(
				'rating' => '<span id="rate-'.$module.'-'.$doc->id.'" class="rating" title="'.\Model\Rate::average($doc->id).'"></span>'
			);
		};
	}
	public static function _block($service, $module, $id) { # TODO
		return '<div id="rate-'.$module.'-'.$id.'" class="rate"></div>'.\View::script('$.Rate.view("'.$module.'", '.$id.', '.json_encode(\Model\Rate::scores($id, $service)).')');
	}
	public static function action($module, $doc) {
		$doc->actions['rate'] = array(
			'href' => '/rate/'.$doc->id.'/'.$module.'/archive',
			'text' => __('Rating')
		);
	}
	public static function delete($module, $node) {
		\Model\Rate::all()->filter('service', \Service::identifier('rate', $module))->filter('node', $node)->delete();
		\View::warning(__('Ratings on item are deleted.'));
	}
	public static function script() {
		return __DIR__.'/script.js';
	}
	public static function style() {
		return __DIR__.'/style.css';
	}
}