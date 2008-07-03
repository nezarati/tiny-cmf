<?
namespace Appearance;
class Hook {
	public function __construct() {
		\Hook::add('menu', '\Appearance\Hook::menu');
		\Hook::add('permission', '\Appearance\Hook::permission');
	}
	
	public static function menu() {
		return array(
			'appearance' => array(
				'title' => __('Theme'),
				'parent' => 'admin'
			),
			'appearance/set' => array(
				'title' => __('select'),
				'access arguments' => 'theme',
				'parent' => 'appearance'
			),
			'appearance/add' => array(
				'title' => __('add'),
				'access arguments' => 'theme',
				'parent' => 'appearance'
			),
			'appearance/edit' => array(
				'title' => __('archive'),
				'access arguments' => 'theme',
				'parent' => 'appearance'
			)
		);
	}
	public static function permission() {
		return array(
			'administer themes' => array(
				'title' => __('Administer themes')
			)
		);
	}
}