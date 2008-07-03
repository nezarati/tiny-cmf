<?
namespace Forum;
class Controller extends \Controller {
	protected $permission = array('index' => 'access content', 'thread' => 'access content');

	protected function index($A) {
		$this->view->pageTitle[] = __('Forums');

		$headers = array(
			'forum' => array('data' => __('Forum'), 'field' => 'term'),
			'topic' => array('data' => __('Topics'), 'field' => 'count'),
			'lastPost' => array('data' => __('Last post')),
		);
		$query = \Taxonomy\API::feed(SERVICE_TAXONOMY_FORUM)->filter('parent', (int)$A->id)->map(function($doc) {
			$doc->forum = '<a href="/forum/'.$doc->id.'" rel="ajax" title="'.$doc->description.'">'.$doc->term.'</a><br /><small>'.$doc->description.'</small>';
			$doc->topic = number_format($doc->count);
			$doc->lastPost = \Forum\Controller::detail(\Forum\Controller::queryLastReply(\Forum\Controller::queryLastThread($doc->id)->fields('id')->fetchField())->fields('user', 'created')->fetch());
		});
		$headersTopic = array(
			'status' => array(),
			'topic' => array('data' => __('Topic — :new', array('new' => '<a href="/post/0/forum/edit?arg[taxonomy]='.$A->id.'" rel="ajax">'.__('Add New »').'</a>'))),
			'replies' => array('data' => __('Replies'), 'field' => 'comment.count'),
			'lastReply' => array('data' => __('Last reply'), 'field' => 'updated'),
		);
		$queryTopic = \Model\Post::all()->fields('id', 'user', 'created', 'title')->filter('service', SERVICE_POST_FORUM)->filter('taxonomy', (int)$A->id)->filter('status', \Model\Post::FLAG_PUBLISHED)->map(function($doc) {
			$doc->topic = '<a href="/forum/'.$doc->id.'/thread" rel="ajax">'.$doc->title.'</a><br /><small>'.\Forum\Controller::detail($doc).'</small>';
			$doc->replies = number_format($doc->comment['count']);
			$doc->lastReply = \Forum\Controller::detail(\Forum\Controller::queryLastReply($doc->id)->fields('user', 'created')->fetch());
		});
		
		return new \TableSelect(NULL, $headers, $query).new \TableSelect(NULL, $headersTopic, $queryTopic);
	}
	public static function queryLastThread($taxonomy) {
		return \Model\Post::all()->filter('service', SERVICE_POST_FORUM)->filter('taxonomy', $taxonomy)->filter('status', \Model\Post::FLAG_PUBLISHED)->sort('-updated');
	}
	public static function queryLastReply($node) {
		return \Model\Comment::all()->filter('service', SERVICE_COMMENT_FORUM)->filter('node', $node)->filter('status', \Model\Comment::STATUS_PUBLISH)->sort('-created');
	}
	public static function detail($doc) {
		return $doc ? __('By :author :time', array('author' => '<a href="/user/'.$doc->user.'/profile" title="'.__('View user profile.').'">'.\User\API::load($doc->user)->name.'</a>', 'time' => format_date($doc->created, 'period'))) : __('n/a');
	}
	
	protected function thread() {
		$ret = \Model\DataStore::connection()->command(
			array(
				'mapreduce' => 'Link',
				'map' => new \MongoCode('function() {
					emit({service: this.service}, {length: this.title.length, title: this.title});
				}'),
				'reduce' => new \MongoCode('function(key, doc) {
					//return doc;
				}'),
				'verbos' => TRUE
			)
		);
		print_r($ret);
		die;
	}
	protected function reply() {
		$data->parent;
		$data->node;
		$data->user;
		$data->created;
		$data->status;
		$data->content;
		\Model\Comment::saveById($data, SERVICE_COMMENT_FORUM);
	}
}