<?
namespace Main;
class Hook {
	public function __construct() {
		\Hook::add('construct', '\Main\Hook::construct', 100);
		\Hook::add('route', '\Main\Hook::route', 0);
		\Hook::add('permission', '\Main\Hook::permission');
		\Hook::add('menu', '\Main\Hook::menu');
		\Hook::add('status', '\Main\Hook::status', 1);
		\Hook::add('head', '\Main\Hook::head');
		\Hook::add('install', '\Main\API::install');
		\Hook::add('script', '\Main\Hook::script');
		\Hook::add('style', '\Main\Hook::style');
		\Hook::add('HTML', '\Main\Smilies::convert', 3);
	}
	
	public static function construct() {
		define('SERVICE_MAIN', \Service::identifier('main'));
		\Registry::setInstance('main', array('title' => '', 'name' => '', 'home' => '', 'slogan' => '', 'mail' => '', 'keywords' => '', 'frontPage' => 'article', 'layout' => 1), TRUE);
	}
	public static function route() {
		API::rewrite(ROUTE, $result);
		
		# Fix4Lighttpd
		if (subStr($_SERVER['SERVER_SOFTWARE'], 0, 6) != 'Apache') {
			parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $_GET);
			$result = array_merge_recursive($result, $_GET);
		}
		
		$_REQUEST = array_merge_recursive($_REQUEST, $result);
	}
	public static function permission() {
		return array(
			'administer site configuration' => array(
				'title' => __('Administer site configuration'),
			)
		);
	}
	public static function menu() {
		return array(
			'admin' => array(
				'title' => __('Administer'),
				'weight' => 10,
				'type' => 0
			),
			'content' => array(
				'title' => __('Content'),
				'weight' => 5,
				'type' => 0
			),
			'structure' => array(
				'title' => __('Structure'),
				'weight' => 15,
				'type' => 0
			),
			'preferences' => array(
				'title' => __('Configuration'),
				'weight' => 20,
				'type' => 0
			),
			'main/preferences' => array(
				'title' => __('Site information'),
				'description' => __('Change basic site name, e-mail address, slogan and default front page.'),
				'parent' => 'preferences'
			)
		);
	}
	
	public static function status() {
		if (\User\API::access('administer account'))
			return '<a href="/'.\Registry::getInstance('main', SERVICE_MAIN)->frontPage.'" rel="ajax" title="'.__('Preview').'" class="preview">-</a>';
	}
	public static function head() {
		$cfg = \Registry::getInstance('main', SERVICE_MAIN);
		return '
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript" src="/root/script.js"></script>
<meta name="description" content="'.$cfg->slogan.'" />
<meta name="keywords" content="'.$cfg->keywords.'" />
<script type="text/javascript" async="async" id="script"></script>
';
	}
	public static function script() {
		return array(
			__DIR__.'/script.js',
			__DIR__.'/collapse.js',
			JOORCHIN_MODULE_DIRECTORY.'statistics/script.js'
		);
	}
	public static function style() {
		return array(
			__DIR__.'/style.css',
			__DIR__.'/smilies.css',
#			__DIR__.'/pagination.css' TODO
		);
	}
}