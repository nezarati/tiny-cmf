<?
namespace Post;
class Hook {
	public function __construct() {
		\Hook::add('construct', '\Post\Hook::construct');
		\Hook::add('menu', '\Post\Hook::menu');
		\Hook::add('permission', '\Post\Hook::permission');
	}
	
	public static function construct() {
		\Registry::setInstance('post', array('order' => 'published', 'sort' => 'desc', 'perPage' => 5, 'perFeed' => 10));
	}
	public static function permission() {
		return array(
			'administer posts' => array(
			  'title' => __('Administer content'),
			),
			'post contents' => array(
				'title' => __('Post contents without approval')
			),
			'access content' => array(
				'title' => __('View published content')
			)
		);
	}
	public static function menu() {
		$menu = array(
			'post/archive' => array(
				'title' => __('Post'),
				'parent' => 'content',
			),
			'post/preferences' => array(
				'title' => __('Post'),
				'access arguments' => array('post write'),
				'parent' => 'preferences',
				'type' => 0
			)
		);
		foreach (\Service::required('post') as $module) {
			$menu['post/0/'.$module->name.'/edit'] = array(
				'title' => __('Add new :name', array('@name' => __($module->title))),
				'access argument' => array('post write'),
				'parent' => 'post/archive',
			);
			$menu['post/0/'.$module->name.'/preferences'] = array(
				'title' => __($module->title),
				'parent' => 'post/preferences'
			);
		}
		return $menu;
	}
}