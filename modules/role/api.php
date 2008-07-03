<?
namespace Role;
class API {
	public static function load($id, $service = SERVICE_ROLE) {
		$model = \Model\Role::all()->filter('service', $service)->filter('id', $id)->fetch();
		return $model;
	}
	public static function save($data, $service = SERVICE_USER) {
		$data->service = $service;
		$data->permission = array_intersect(array_keys(\Hook::invoke('permission')), (array)$data->permission);
		\View::status($data->id ? __('The changes have been saved.') : __('The :name has been added.', array('%name' => __('Role'))));
		return \Model\Role::put($data);
	}
	public static function delete($id, $service = SERVICE_ROLE) {
		\Hook::invoke('role_delete', 'role', $id);
		\View::status(__(':name has been deleted.', array('%name' => API::load($id)->name)));
		return \Model\Role::all()->filter('service', $service)->filter('id', $id)->delete();
	}
	public static function feed($service = SERVICE_ROLE) {
		return \Model\Role::all()->filter('service', SERVICE_ROLE);
	}
	
	public static function install() {
		$service = \Service::install('role', 0);
		foreach (array(array('name' => __('Administrator')), array('name' => __('Anonymous user'), 'permission' => array('access content', 'access comments', 'post comments', 'post ratings', 'search content')), array('name' => __('Authenticated user'), 'permission' => array('access content', 'access comments', 'post comments without approval', 'search content', 'use advanced search', 'administer account', 'post ratings'))) as $data)
			self::save((object)$data, $service);
	}
}