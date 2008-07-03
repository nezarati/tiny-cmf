<?
namespace User;
class Hook {
	public function __construct() {
		\Hook::add('construct', '\User\Hook::construct');
		\Hook::add('permission', '\User\Hook::permission');
		\Hook::add('menu', '\User\Hook::menu');
		\Hook::add('status', '\User\Hook::status', 10);
		\Hook::add('install', '\User\API::install');
	}
	
	public static function construct() {
		if (!is_array($_SESSION['user']['permission']))
			$_SESSION['user']['permission'] = \Role\API::load(Controller::ROLE_GUEST)->permission;
		if (empty($_SESSION['user']['username']) && isset($_COOKIE['user']['username']))
			if ($result = \Model\User::all()->filter('service', SERVICE_USER)->filter('username', $_COOKIE['user']['username'])->filter('password', $_COOKIE['user']['password'])->map()->fetch()) {
				$result->permission = \Role\API::load($result->role)->permission;
				unset($result->_id); # MongoDB
				$_SESSION['user'] = (array)$result;
			} else
				\Cookie::delete('user[username]', 'user[password]');
		define('USER_ID', $_SESSION['user']['id'] ?: 0);
		
		# destruct
		if (USER_ID && GMT - $_SESSION['user']['accessed'] > 60*3)
			API::save((object)array('accessed' => $_SESSION['user']['accessed'] = GMT, 'id' => USER_ID));
	}
	public static function menu() {
		return array(
			'user' => array(
				'title' => __('User'),
				'access arguments' => array('administer users'),
				'parent' => 'admin'
			),
			'user/account' => array(
				'title' => __('Account'),
				'access arguments' => array('administer account'),
			),
			'user/logout' => array(
				'title' => __('Log out'),
				'access arguments' => array('administer account'),
				'parent' => 'user/account',
			),
		);
	}
	public static function status() {
		if (API::access('administer users'))
			return array(
				'<a href="/user?sort=status" rel="ajax" title="'.__('Inactive users').'" class="user-inactive">'.\Model\User::all()->filter('service', SERVICE_USER)->filter('status', 0)->count().'</a>',
				'<a href="/user?sort=-lastAccess" title="'.__('Online Users').'" rel="ajax" class="analytics">'.\Model\Session::all()->filter('domain', DOMAIN)->filter('timestamp', GMT - 20*60, '>')->count().'</a>', # TODO
			);
	}
	public static function permission() {
		return array(
			'administer users' => array(
				'title' => __('Administer users'),
				'description' => __('Warning: Give to trusted roles only; this permission has security implications.')
			),
			'administer account' => array(
				'title' => __('Administer account'),
			),
			'change own username' => array(
				'title' => __('Change own username')
			)
		);
	}
}