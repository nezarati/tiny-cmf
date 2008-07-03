<?
namespace Page;
class API {
	public static function widget() {
		$li = '';
		foreach (\Model\Post::all()->fields('id', 'title')->filter('service', SERVICE_POST_PAGE)->filter('status', 1)->filter('promote', 1)->sort('-sticky')->sort('created') as $row)
			$li .= '<li><a href="/page/'.$row->id.'" rel="ajax">'.$row->title.'</a></li>';
		return '<ul>'.$li.'</ul>';
	}
	public static function install() {
		\Service::install('post', 'page');
		\Menu\API::save((object)array('module' => 'page', 'title' => __('Static pages'), 'callback' => '\Page\API::widget', 'status' => 1, 'weight' => 5), _SERVICE_MENU);
	}
}