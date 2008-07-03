<?
namespace Poll;
class Hook {
	public function construct() {
		\Hook::add('permission', 'Poll\Hook::permission');
		\Hook::add('menu', '\Poll\Hook::menu');
		foreach (\Service::required('poll') as $modal) {
			\Hook::add($modal->name.'_block', '\Poll\Hook::block', 4);
			\Hook::add($modal->name.'_delete', '\Poll\Hook::delete', 4);
			\Hook::add($modal->name.'_action', '\Poll\Hook::action', 4);
		}
	}
	
	public static function menu() {
		return array(
			'poll/archive' => array(
				'title' => 'Polls',
				'access arguments' => array('access content'),
				'parent' => 'content'
			),
			'poll/0/edit' => array(
				'title' => 'Polls',
				'access arguments' => array('access content'),
				'parent' => 'poll/archive'
			)
		);
	}
	public static function permission() {
		return array(
			'vote on polls' => array(
			  'title' => __('Vote on polls'),
			),
			'cancel own vote' => array(
			  'title' => __('Cancel and change own votes'),
			),
			'inspect all votes' => array(
			  'title' => __('View voting results'),
			)
		);
	}
	public function widget() {
		return array(
			'poll' => array(
				'title' => 'Polls',
				'callback' => '\Poll\API::widget'
			)
		);
	}
	
	public static function delete($G, $K, $L) { # TODO
		return array(__('Delete') => \DB::delete('poll')->condition('service')->condition('node', $K)->execute());
	}
	public static function block($module, $node) {
		if ($block = API::block($module, $node))
			return array('poll' => array('content' => $block));
	}
	public static function action($module, $model) {
		$model->operations['poll'] = array('href' => '/poll/'.$model->id.'/'.$module.'/archive', 'value' => '<img src="/img/module/poll/icon.png" />', 'title' => __('Poll'));
	}
}