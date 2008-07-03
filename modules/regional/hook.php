<?
namespace Regional;
class Hook {
	public function __construct() {
		\Hook::add('construct', '\Regional\Hook::construct');
		\Hook::add('permission', '\Regional\Hook::permission');
		\Hook::add('menu', '\Regional\Hook::menu');
		\Hook::add('timestamp-get', '\Regional\Hook::timestamp');
		\Hook::add('install', '\Regional\API::install');
	}
	
	public static function construct() {
		$cfg = \Registry::getInstance('regional', SERVICE_REGIONAL);
		define('JOORCHIN_LANGUAGE', $cfg->language);
		define('JOORCHIN_CALENDAR', $cfg->calendar);
		define('JOORCHIN_TIMEZONE', $cfg->timezone);
	}
	public static function permission() {
		return array(
			'administer regional' => array(
				'title' => __('Administer regional'),
			),
			'administer languages' => array(
				'title' => __('Administer languages'),
			),
			'translate interface' => array(
				'title' => __('Translate interface texts'),
			),
		);
	}
	public static function menu() {
		$menu['regional/preferences'] = array(
			'title' => __('Regional and language'),
			'description' => __('Regional settings, localization and translation.'),
			'access arguments' => array('administer regional'),
			'parent' => 'preferences'
		);
		if (in_array(HOST, array('en', 'fr', 'fa', '127.0.0.1', 'localhost')))
			$menu['regional/translate'] = array(
				'title' => __('Translate'),
				'access arguments' => array('translate interface'),
				'description' => __('Translate interface texts'),
				'parent' => 'structure'
			);
		return $menu;
	}
	
	public static function timestamp($data) {
		return mktime($data->hour, $data->minute, $data->second, $data->month, $data->day, $data->year);
	}
}