<?
namespace Post;
class API {
	public static function related() { # TODO
		// SELECT ID, title, content, MATCH (title, content) AGAINST ('$terms') AS score FROM post WHERE MATCH (title, content) AGAINST ('$terms') order by score desc
	}
	public static function archive() { # TODO
		// http://dev.mysql.com/doc/refman/5.5/en/locale-support.html
		// SET lc_time_names = 'fr_FR';
		// select from_unixtime(created, '%m') month, from_unixtime(created, '%Y') year, count(*) count, from_unixtime(created, '%M') Month from post group by year, month
	}
	public static function access($service, $id) { # TODO: ?
		return \User\API::access('post-editor') || DB::query('select user from {post} where service = :0 && id = :1', array($service, $id))->fetchField() == USER_ID;
	}
	
	public static function load($id, $service = SERVICE_POST) {
		static $T = array();
		return isset($T[$service][$id]) ? $T[$service][$id] : $T[$service][$id] = \Model\Post::all()->filter('service', $service)->filter('id', $id)->fetch();
	}
	public static function save($data, $service = SERVICE_POST) {
		$data->service = $service;
		\View::status($data->id ? __('The changes have been saved.') : __('The :name has been added.', array('%name' => $data->title)));
		return \Model\Post::put($data);
	}
	public static function delete($id, $service = SERVICE_POST) {
		\View::status(__(':name has been deleted.', array('%name' => static::load($id, $service)->title)));
		return \Model\Post::all()->filter('service', $service)->filter('id', $id)->delete();
	}
	
	public static function feed($service = SERVICE_POST) {
		$cfg = \Registry::getInstance('post', $service);
		return \Model\Post::all()->fields('id', 'created', 'modified', 'title', 'content')->filter('service', $service)->orderBy($cfg->order, $cfg->sort)->limit($cfg->perfeed);
	}
	public static function siteMap($service = SERVICE_POST) { # TODO
		$cfg = \Registry::getInstance('post', $service);
		return \Model\Post::all()->filter('service', $service)->sort('-created')->limit(500)->map(function($doc) {
			$doc->lastmod = max($doc->created, $doc->modified);
		});
	}
	public static function search($P, $M, $O) { # TODO
		return 'select p.id, p.create, p.modify, c.name category, u.username author, p.title, p.content, concat("'.TS_URL.'/post-", p.id, ".html") link, concat("'.TS_URL.'/comment-post-", p.id, ".html") comment from #__post p, #__category c, #__user u where u.bind='.User::$BIND.' && p.bind='.Bind.' && c.bind='.Bind.' && p.group=0 && u.id=p.author && c.group='.Group::identifier(Category::KEY, 'post').' && c.id=p.category && p.create<'.GMT.(is_numeric($A->id) ? ' && p.id='.$A->id : '').' order by p.'.$this->configuration->order.' '.$this->configuration->sort;
	}
	public static function calendar($A) { # TODO
		return 'select id, created, taxonomy, user, title from {post} where service = '.' && created<'.GMT.' && created between '.$A->min.' and '.$A->max.' order by `'.$this->configuration->order.'` '.$this->configuration->sort;
	}
	
	public static function tidy($content) {
		$tidy = new \Tidy;
		return $tidy->repairString($content, array('show-body-only' => TRUE, 'doctype' => '-//W3C//DTD XHTML 1.0 Transitional//EN', 'output-xhtml' => TRUE), 'UTF8');
	}
}