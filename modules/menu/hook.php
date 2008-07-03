<?
namespace Menu;
class Hook {
	public function __construct() {
		\Hook::add('menu', '\Menu\Hook::menu');
		\Hook::add('permission', '\Menu\Hook::permission');
		\Hook::add('install', '\Menu\API::install', 0);
	}
	public static function menu() {
		return array(
			'menu/archive' => array(
				'title' => __('Menus'),
				'description' => __('Add new menus to your site, edit existing menus, and rename and reorganize menu links.'),
				'access arguments' => array('administer menu'),
				'parent' => 'structure'
			),
			'menu/0/edit' => array(
				'title' => __('Add menu'),
				'access arguments' => array('administer menu'),
				'parent' => 'menu/archive'
			)
		);
	}
	public static function permission() {
		return array(
			'administer menu' => array('title' => __('Administer menus and menu items'))
		);
	}
}