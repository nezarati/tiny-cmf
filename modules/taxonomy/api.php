<?
namespace Taxonomy;
class API {
	public static function install() {
		\Menu\API::save((object)array('module' => 'taxonomy', 'title' => __('Taxonomy'), 'callback' => '\Taxonomy\API::widget', 'status' => 1, 'weight' => 1), _SERVICE_MENU);
	}
	public static function widget() {
		$li = '';
		foreach (\Service::required('taxonomy') as $module)
			$li .= '<li><a href="/'.$module->name.'/taxonomy/index" rel="ajax"><span onclick="$.Taxonomy.parent(this, \''.$module->name.'\', 0)">→</span> '.__($module->title).'</a></li>';
		return '<ul>'.$li.'<ul>';
	}
	public static function access($service, $id, $username = USER_ID) {
		if (is_numeric($id) && is_numeric($username)) {
			if (\User\API::access('administer taxonomy'))
				return TRUE;
			foreach (explode(',', self::load($id, $service)->moderator) as $admin)
				if ($admin == $username)
					return TRUE;
		}
		return FALSE;
	}
	public static function load($id, $service = SERVICE_TAXONOMY) {
		static $T = array();
		return isset($T[$service][$id]) ? $T[$service][$id] : $T[$service][$id] = \Model\Taxonomy::all()->filter('service', $service)->filter('id', $id)->fetch();
	}
	public static function save($data, $service = SERVICE_TAXONOMY) {
		$data->service = $service;
		\View::status(__($data->id ? 'The changes have been saved.' : 'The :name has been added.', array('%name' => __('taxonomy'))));
		\Model\Taxonomy::put($data);
	}
	public static function delete($id, $service = SERVICE_TAXONOMY) {
		\View::status(__(':name has been deleted.', array('%name' => static::load($id, $service)->term)));
		return \Model\Taxonomy::all()->filter('service', $service)->filter('id', $id)->delete();
	}
	public static function feed($service) {
		return \Model\Taxonomy::all()->filter('service', $service)->sort('weight');
	}
	
	private static function moderator($M, $V) { # TODO // $M == 0: Save, $M == 1: Load
		$r = array();
		if ($M) {
			foreach (explode(',', $V) as $i)
				$r[] = \User\API::load($i)->username;
			return implode(',', $r);
		}
		foreach (explode(',', $V) as $u)
			trim($u) && $r[] = User::U2I($u);
		return implode(',', $r);
	}
	
	public static function count($service, $new, $old = 0) {
		if ($new)
			\Model\Taxonomy::update(array('service' => (int)$service, 'id' => (int)$new), array('$inc' => array('count' => 1))); # MongoDB
		if ($old)
			\Model\Taxonomy::update(array('service' => (int)$service, 'id' => (int)$old), array('$inc' => array('count' => -1))); # MongoDB
		return TRUE;
	}
}