<?
namespace Comment;
class Hook {
	public function __construct() {
		foreach (\Service::required('comment') as $module) {
			\Hook::add($module->name.'_block', '\Comment\Hook::block', 2);
			\Hook::add($module->name.'_delete', '\Comment\Hook::delete', 2);
			\Hook::add($module->name.'_action', '\Comment\Hook::action', 2);
		}
		\Hook::add('status', '\Comment\Hook::status', 5);
		\Hook::add('menu', '\Comment\Hook::menu');
		\Hook::add('permission', '\Comment\Hook::permission');
		\Hook::add('script', '\Comment\Hook::script');
	}
	
	public static function status() {
		if (\User\API::access('administer comments'))
			return '<a href="/comment/archive" rel="ajax" title="'.__('Comments').'" class="comment">'.\Model\Comment::all()->filter('service', array_keys(\Service::required('comment')))->filter('status', 0)->count().'</a>';
	}
	public static function menu() {
		$menu = array(
			'comment/archive' => array(
				'title' => __('Comment'),
				'parent' => 'content'
			),
			'comment/preferences' => array(
				'title' => __('Comment'),
				'parent' => 'preferences',
				'type' => 0
			),
		);
		foreach (\Service::required('comment') as $module) {
			$menu['comment/0/'.$module->name.'/preferences'] = array(
				'title' => __($module->title),
				'parent' => 'comment/preferences'
			);
		}
		return $menu;
	}
	public static function permission() {
		return array(
			'administer comments' => array('title' => __('Administer comments and comment settings')),
			'access comments' => array('title' => __('View comments')),
			'post comments' => array('title' => __('Post comments with approval')),
			'post comments without approval' => array('title' => __('Post comments without approval')),
			'edit own comments' => array('title' => __('Edit own comments'))
		);
	}
	public static function delete($module, $node) {
		foreach (\Model\Comment::all()->fields('id')->filter('service', $service = \Service::identifier('comment', $module))->filter('node', $node) as $row)
			\Hook::invoke('comment_delete', 'comment', $row->id); # TODO
		\Model\Comment::all()->filter('service', $service)->filter('node', $node)->delete();
		\View::warning(__('Comments on item are deleted.'));
	}
	public static function block($module, $doc) {
		$doc->comment = function($attr) use($module, $doc) {
			return array(
				'count' => \Model\Post::all()->filter('service', \Service::identifier('comment', $module))->filter('comment.node', $doc->id)->filter('status', 1)->count(),
				'url' => '/comment/'.$doc->id.'/'.$module.'/index',
			);
		};
	}
	public static function action($module, $doc) {
		$doc->operations['comment'] = array(
			'href' => '/comment/'.$doc->id.'/'.$module.'/archive',
			'value' => '<img src="/img/module/comment/icon.png" />',
			'title' => __('Comment')
		);
	}
	public static function script() {
		return __DIR__.'/script.js';
	}
}