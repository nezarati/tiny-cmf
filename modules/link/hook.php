<?
namespace Link;
class Hook {
	public function __construct() {
		\Hook::add('permission', '\Link\Hook::permission');
		\Hook::add('menu', '\Link\Hook::menu');
		\Hook::add('status', '\Link\Hook::status', 7);
		\Hook::add('install', '\Link\API::install');
		\Hook::add('script', '\Link\Hook::script');
		\Hook::add('search', '\Link\Hook::search');
	}
	
	public static function permission() {
		return array(
			'administer links' => array(
				'title' => __('Administer comments and comment settings')
			)
		);
	}
	public static function menu() {
		$menu = array(
			'link/archive' => array(
				'title' => __('Link'),
				'access argument' => array('administer links'),
				'parent' => 'admin'
			),
			'link/preferences' => array(
				'title' => __('Link'),
				'access argument' => array('administer links'),
				'parent' => 'preferences',
				'type' => 0
			)
		);
		foreach (\Service::required('link') as $module) {
			$menu['link/0/'.$module->name.'/edit'] = array(
				'title' => __('Add new :name', array('@name' => __($module->title))),
				'access argument' => array('administer links'),
				'parent' => 'link/archive',
			);
			$menu['link/0/'.$module->name.'/preferences'] = array(
				'title' => __($module->title),
				'parent' => 'link/preferences'
			);
		}
		return $menu;
	}
	public static function status() {
		if (\User\API::access('administer links'))
			return '<a href="/link/archive?sort=status" rel="ajax" title="'.__('Links').'" class="link">'.\Model\Link::all()->filter('service', array_keys(\Service::required('link')), 'in')->filter('status', 0)->count().'</a>';
	}
	public static function script() {
		return __DIR__.'/script.js';
	}
	public static function search() {
		return array('link' => __('Link'));
	}
}