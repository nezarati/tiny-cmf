<?
namespace Comment;
class API {
	public static function load($id, $service = SERVICE_COMMENT) {
		static $T = array();
		return isset($T[$service][$id]) ? $T[$service][$id] : $T[$service][$id] = \Model\Comment::all()->filter('service', $service)->filter('id', $id)->fetch();
	}
	public static function save($data, $service = SERVICE_COMMENT) {
		$data->service = $service;
		\View::status($data->id ? __('The changes have been saved.') : __('Your comment has been posted.'));
		return \Model\Comment::put($data);
	}
	public static function delete($id, $service = SERVICE_COMMENT) { # TODO
		# Are you sure you want to delete the comment %title?
		\Hook::invoke('comment_delete', 'comment', $id);
		\View::status(__('The comment and all its replies have been deleted.'));
		foreach (\Model\Comment::all()->fields('id')->filter('service', $service)->filter('parent', $id) as $row)
			static::delete($row->id, $service);
		return \Model\Comment::all()->filter('service', $service)->filter('id', $id)->delete();
	}
	
	public static function feed() { # TODO
		return \DB::select('comment', 'c')->fields('c', array('title', 'link' => '/comment/'.$_REQUEST['arg']['dependence'].'/%id', 'id', 'published', 'updated', 'summary', 'author(name|uri)', 'category', 'content'))->condition('service', service($_REQUEST['arg']['dependence']))->orderBy('id', 'desc')->map(function($model) {
			$user = \User\API::load($model->user);
			$model->author['name'] = $user->name;
			unset($model->author_name);
		});
	}
	public static function sitemap() { # TODO
		return \DB::select('comment', 'c')->fields('c', array('url', 'modify', 'frequency', 'priority'))->extend('\Sitemap\Node');
	}
}