<?
namespace Link;
class API {
	public static function widget() {
		$cfg = \Registry::getInstance('link', SERVICE_LINK_MAIN);
		$query = \Model\Link::all()->fields('id', 'title', 'url', 'description', '__titleLength')->filter('service', SERVICE_LINK_MAIN)->filter('status', 1)->limit($cfg->perPage);
		#if ($cfg->order == 'rand') # TODO
		#	$query->orderBy('rand()', NULL);
		#else
			$query->orderBy($cfg->order, $cfg->sort);
		$li = '';
		foreach ($query as $row)
			$li .= '<li><a href="'.$row->url.'" title="'.$row->description.'" target="_blank" onclick="$.Link.hit(\'main\', '.$row->id.')">'.$row->title.'</a></li>';
		return '<ul>'.$li.'<ul>';
	}
	public static function install() {
		\Menu\API::save((object)array('module' => 'link', 'title' => __('Link'), 'callback' => '\Link\API::widget', 'status' => 1, 'weight' => 4), _SERVICE_MENU);
	}
	
	public static function load($id, $service = SERVICE_LINK) {
		static $T = array();
		return isset($T[$service][$id]) ? $T[$service][$id] : $T[$service][$id] = \Model\Link::all()->filter('service', $service)->filter('id', $id)->fetch();
	}
	public static function save($data, $service = SERVICE_LINK) {
		$data->service = $service;
		\View::status($data->id ? __('The changes have been saved.') : __('The :name has been added.', array('%name' => __('link'))));
		\Model\Link::put($data);
	}
	public static function delete($id, $service = SERVICE_LINK) {
		\View::status(__(':name has been deleted.', array('%name' => static::load($id, $service)->title)));
		return \Model\Link::all()->filter('service', $service)->filter('id', $id)->delete();
	}
	public static function search($query, $match, $order) { # TODO
		return \DB::query('select url href, title, description content from {link} where service in(:0) && status = 1 && MATCH(title, description) AGAINST(:1)', array(array_keys(\Service::required('link')), $query), array('model' => function($model) {
			static $index = 0;
			$model->index = ++$index;
		}));
	}
}