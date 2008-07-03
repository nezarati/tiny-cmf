<?
namespace Role;
class Hook {
	public function __construct() {
		\Hook::add('permission', '\Role\Hook::permission');
		\Hook::add('menu', '\Role\Hook::menu');
		\Hook::add('install', '\Role\API::install');
	}
	
	public static function permission() {
		return array(
			'administer permissions' => array(
				'title' => __('Administer permissions'),
				'description' => __('Warning: Give to trusted roles only; this permission has security implications.')
			)
		);
	}
	public static function menu() {
		return array(
			'role' => array(
				'title' => __('Role'),
				'access arguments' => array('administer permissions'),
				'parent' => 'admin'
			),
			'role/0/edit' => array(
				'title' => __('Add role'),
				'access arguments' => array('administer permissions'),
				'parent' => 'role'
			),
		);
	}
}