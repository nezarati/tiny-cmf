<?
namespace Article;
class Controller extends \Controller {
	protected $permission = array('index' => 'access content', 'recent' => 1, 'feed' => 1, 'sitemap' => 1);
	protected function index($A) {
		$this->view->pageTitle[] = __('Article');
		# blog_block_query, post_block_entry
		# \Hook::call('article_block', 'article', $row->id, '/article/'.$row->id);
		#TODO: related_link
		$cfg = \Registry::getInstance('post', SERVICE_POST_ARTICLE);
		$_REQUEST['count'] = $cfg->perPage;
		$query = \Model\Post::all()->fields('id', 'blog.published', 'modified', 'blog.taxonomy', 'user', 'title', 'content', 'blog.image')->filter('service', SERVICE_POST_ARTICLE)->filter('status', \Model\Post::FLAG_PUBLISHED)->filter('blog.published', GMT, '<=')->sort('-blog.sticky');
		$pagination = TRUE;
		if (is_numeric($A->id))
			switch ($_REQUEST['module']) {
				case 'taxonomy':
					$query->filter('blog.taxonomy', $A->id);
					break;
				case 'user':
					$query->filter('user', $A->id);
					break;
				default:
					$query->filter('id', $A->id);
					$pagination = FALSE;
			}
		else
			$query->filter('blog.promote', 1);
		$query->extend('TablePagination');
		
		if (!$total = $query->count())
			return __($_REQUEST['module'] == 'taxonomy' ? 'There is currently no content classified with this term.' : 'No front page content has been created yet.');
		$tpl = new \Template(\Model\Layout::block('blog'));
		$tpl->blog = function($attr) use($cfg, $query, $pagination, $total) {
			return array(
				'posts' => $query->map(
					function($doc) use($pagination) {
						$doc->content = \Hook::filter('HTML', ($more = preg_match('/(.*)<!-- More -->/is', $doc->content, $match)) && $pagination ? $match[1] : $doc->content);
						$doc->continue = $more && $pagination ? array('more') : NULL;
						$doc->image = $doc->blog['image'] ? array('url' => $doc->blog['image']) : '';
						$doc->url = '/article/'.$doc->id;
						$doc->published = $doc->blog['published'];

						$doc->author = function() use($doc) {
							return array(
								'name' => \User\API::load($doc->user)->name,
								'url' => '/article/user/'.$doc->user
							);
						};

						$doc->taxonomy = array(
							'term' => \Taxonomy\API::load($doc->blog['taxonomy'], SERVICE_TAXONOMY_ARTICLE)->term,
							'url' => '/article/taxonomy/'.$doc->blog['taxonomy']
						);

						\Hook::call('article_block', 'article', $doc);
					}
				),
				'pagination' => $pagination ? new \Pagination($total, NULL, $cfg->perPage) : ''
			);
		};
		return $tpl;
	}
	protected function recent() {
		$recent = array();
		foreach (\Model\Post::all()->fields('id', 'title')->filter('service', SERVICE_POST_ARTICLE)->sort('-id')->limit(5) as $row)
			$recent[] = array('title' => $row->title, 'url' => 'http://'.(strpos(HOST, '.') === FALSE ? HOST.'.chonoo.com' : 'www.'.HOST).'/article/'.$row->id);
		return $recent;
	}
	protected function feed() {
		$this->view->header['Content-Type'] = 'application/atom+xml';
		$home = \Registry::getInstance()->home;
		die(
			\Feed::atom(
				\Post\API::feed(SERVICE_POST_ARTICLE)->filter('status', \Model\Post::FLAG_PUBLISHED)->filter('published', GMT, '<=')->limit(\Registry::getInstance('post', SERVICE_POST_ARTICLE)->perFeed)->map(
					function($doc) use($home) {
						$doc->intro = mb_substr($content = strip_tags($doc->content), 0, mb_strlen($content)>2048 ? mb_strpos($content, ' ', 2048) : 2048);
						$doc->url = $home.'/article/'.$doc->id;
					}
				)
			)
		);
	}
	protected function sitemap() {
		$home = \Registry::getInstance()->home;
		die(
			\SiteMap::urlset(
				\Post\API::siteMap(SERVICE_POST_ARTICLE)->filter('status', \Model\Post::FLAG_PUBLISHED)->filter('published', GMT, '<=')->fields('id', 'modified')->limit(500)->map(
					function($doc) use($home) {
						$doc->changefreq = 'monthly';
						$doc->priority = .7;
						$doc->lastmod = $doc->modified;
						$doc->loc = $home.'/article/'.$doc->id;
					}
				)
			)
		);
	}
}