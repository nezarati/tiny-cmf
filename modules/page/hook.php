<?
namespace Page;
class Hook {
	public function __construct() {
		\Hook::add('install', '\Page\API::install');
		\Hook::add('page_form', '\Page\Hook::postForm');
	}
	
	public static function postForm($form) {
		// unset(); # TODO publish to front page
	}
}