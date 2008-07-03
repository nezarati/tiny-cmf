<?
namespace Statistics;
class Hook {
	public function construct() {
		\Hook::add('permission', 'Statistics\Hook::permission');
		\Hook::add('menu', '\Statistics\Hook::menu');
		\Hook::add('script', '\Statistics\Hook::script');
	}
	
	public static function permission() {
		return array(
			'administer statistics' => array(
				'title' => __('Administer statistics'),
			),
			'access statistics' => array(
				'title' => __('View content access statistics'),
			),
			'view post access counter' => array(
				'title' => __('View content hits'),
			),
		);
	}
	public static function menu() {
		return array(
			'statistics' => array(
				'title' => 'Statistics',
				'access arguments' => array('access statistics'),
				'parent' => 'admin',
				'weight' => 5
			),
			'statistics/hits' => array(
				'title' => 'Recent hits',
				'description' => 'View pages that have recently been visited.',
				'access arguments' => array('access statistics'),
				'parent' => 'statistics'
			),
			'statistics/pages' => array(
				'title' => 'Top pages',
				'description' => 'View pages that have been hit frequently.',
				'access arguments' => array('access statistics'),
				'weight' => 1,
				'parent' => 'statistics'
			),
			'statistics/visitors' => array(
				'title' => 'Top visitors',
				'description' => 'View visitors that hit many pages.',
				'access arguments' => array('access statistics'),
				'weight' => 2,
				'parent' => 'statistics'
			),
			'statistics/referrers' => array(
				'title' => 'Top referrers',
				'description' => 'View top referrers.',
				'access arguments' => array('access statistics'),
				'parent' => 'statistics'
			)
		);
	}
	public static function script() {
		return __DIR__.'/script.js';
	}
}