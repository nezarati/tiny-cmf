<?
namespace Search;
class Hook {
	public function __construct() {
		\Hook::add('permission', '\Search\Hook::permission');
		\Hook::add('install', '\Search\API::install');
		\Hook::add('script', '\Search\Hook::script');
	}
	
	public static function permission() {
		return array(
			'administer search' => array(
				'title' => __('Administer search'),
			),
			'search content' => array(
				'title' => __('Use search'),
			),
			'use advanced search' => array(
				'title' => __('Use advanced search'),
			)
		);
	}
	public static function script() {
		return __DIR__.'/script.js';
	}
}