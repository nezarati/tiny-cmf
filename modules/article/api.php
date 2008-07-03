<?
namespace Article;
class API {
	public static function permalink(Array $data) {
		return '/article/'.$data['id'];
	}
	public static function ping($server) {
		$client = new IXR($server);
		$cfg = \Registry::getInstance();
		if ($client->query('weblogUpdates.extendedPing', $cfg->title, $cfg->home, $cfg->home.'/article/feed')) {
			\View::status(__(':server - Thanks for the ping!', array('@server' => $server)));
			return TRUE;
		} else
			if ($client->query('weblogUpdates.ping', $cfg->title, $cfg->home)) {
				\View::status(__(':server - Succeeded.', array('@server' => $server)));
				return TRUE;
			} else {
				\View::status(__(':server - Failed.', array('@server' => $server)));
				return FALSE;
			}
	}
	public static function install() {
		\Service::install('post', 'article');
		\Service::install('comment', 'article');
		\Service::install('rate', 'comment');
		\Service::install('taxonomy', 'article');
		\Service::install('rate', 'article');
		\Service::install('poll', 'article');
	}
	public static function search($query, $match, $order) { # TODO
		return \DB::query('select created, id, title, taxonomy, user, content, MATCH(title, content) AGAINST(:1) AS score from {post} where service = :0 && status = 1 && MATCH(title, content) AGAINST(:1)', array(SERVICE_POST_ARTICLE, $query), array('model' => function($model) {
			static $index = 0;
			$model->href = '/article/'.$model->id;
			$model->category = \Taxonomy\API::load($model->taxonomy, SERVICE_TAXONOMY_ARTICLE)->term;
			$model->author = \User\API::load($model->user)->name;
			$model->index = ++$index;
		}));
	}
}