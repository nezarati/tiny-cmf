<?
namespace User;
class API {
	static protected $cache = array();
	public static function load($id = USER_ID, $service = SERVICE_USER) {
		return $t =& self::$cache[$service][$id] ?: $t = \Model\User::all()->filter('service', $service)->filter('id', $id)->fetch();
	}
	public static function save($data, $service = SERVICE_USER) {
		$data->service = $service;
		
		if (isset($data->name)) // 4 update accessed
			$data->name = htmlspecialchars($data->name);
		
		\Hook::call('user_presave', 'user', $data);
		\View::status($data->id ? __('The changes have been saved.') : __('Created a new user account for :name. No e-mail has been sent.', array('%name' => $data->username)));
		\Model\User::put(static::$cache[$data->service][$data->id] = $data);
	}
	public static function delete($id = USER_ID, $service = SERVICE_USER) {
		unset(self::$cache[$service][$id]);
		\Hook::invoke('user_delete', 'user', $id);
		\View::status(__(':name has been deleted.', array('%name' => API::load($id)->name)));
		return \Model\User::all()->filter('service', $service)->filter('id', $id)->delete();
	}
	public static function access($access) {
		return $access == 1 || $_SESSION['user']['role'] == 1 ?: in_array($access, $_SESSION['user']['permission']);
	}
	
	public static function hashPassword($passwd) { // shadow
		$seeds = array_merge(range(46, 57), range(65, 90), range(97, 122)); # ./0-9A-Za-z
		$salt = implode(array_map(function($input) use($seeds) {
			return chr($seeds[$input]);
		}, array_rand($seeds, 8)));
		return crypt($passwd, '$1$'.$salt.'$');
	}
	public static function checkPassword($passwd, $stored_hash) {
		return crypt($passwd, substr($stored_hash, 0, 12)) == $stored_hash;
	}

	public static function username2Id($username, $service) {
		static $T = array();
		return isset($T[$username]) ? $T[$username] : $T[$username] = \Model\User::all()->fields('id')->filter('service', $service)->filter('username', $username)->fetchField();
	}
	public static function install() {
		$data = self::load();
		unset($data->_id, $data->id); # MongoDB
		$data->created = GMT;
		$data->role = 1;
		$data->access = 0;
		foreach ($data as $key => $value)
			$doc->$key = $value;
		self::save($doc, \Service::install('user', 0));
	}
}