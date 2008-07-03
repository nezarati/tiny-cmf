<?
namespace Menu;
class API {
	public static function install() {
		define('_SERVICE_MENU', \Service::install('menu'));
	}
	public static function widget() {
		$widget = array();
		foreach (\Model\Widget::all()->fields('module', 'callback', 'title', 'content')->filter('service', SERVICE_MENU)->filter('status', 1)->sort('weight')->map() as $row) {
			if ($row->callback)
				$row->content = call_user_func($row->callback);
			$widget[] = (array)$row;
		}
		return $widget;
	}
	
	public static function load($id, $service = SERVICE_MENU) {
		return \Model\Widget::all()->filter('service', $service)->filter('id', $id)->fetch();
	}
	public static function save($data, $service = SERVICE_MENU) {
		$data->service = $service;
		$tidy = new \Tidy;
		$tidy->parseString('<span>'.$data->content.'</span>', array('doctype' => '-//W3C//DTD XHTML 1.0 Transitional//EN', 'output-xhtml' => TRUE), 'UTF8');
		$data->content = preg_replace('#^<span>(.*)</span>$#s', '$1', $tidy->body()->child[0]);
		unset($tidy);
		\View::status($data->id ? __('The changes have been saved.') : __('The :name has been added.', array('%name' => __('Widget'))));
		\Model\Widget::put($data);
	}
	public static function delete($id, $service = SERVICE_MENU) {
		\View::status(__(':name has been deleted.', array('%name' => self::load($id)->title)));
		return \Model\Widget::all()->filter('service', $service)->filter('id', $id)->delete();
	}
}
