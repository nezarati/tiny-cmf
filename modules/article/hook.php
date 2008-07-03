<?
namespace Article;
class Hook {
	public function __construct() {
		\Hook::add('article_presave', '\Article\Hook::postPresave');
		\Hook::add('article_form', '\Article\Hook::postForm');
		\Hook::add('install', '\Article\API::install');
		\Hook::add('head', '\Article\Hook::head');
		\Hook::add('sitemap', '\Article\Hook::sitemap');
		\Hook::add('search', '\Article\Hook::search');
	}
	
	public static function head() {
		return '<link href="/article/feed" rel="alternate" type="application/atom+xml" title="'.\Registry::getInstance()->title.' Atom Feed" />';
	}
	public static function sitemap() {
		return \Registry::getInstance()->home.'/article/sitemap';
	}
	public static function search() {
		return array('article' => __('Article'));
	}
	
	public static function postPresave($data) {
		$data->content = \Post\API::tidy($data->content);
		
		$data->article['published'] = \Regional\API::timestamp($data->published);
		unset($data->published);
		
		$data->article['image'] = $data->image;
		unset($data->image);
		
		$data->article['promote'] = (int)$data->promote;
		unset($data->promote);
		
		$data->article['sticky'] = (int)$data->sticky;
		unset($data->sticky);
		
		return; # TODO
		if ($data->status)
			foreach (array('rpc.pingomatic.com', 'rpc.weblogs.com', 'ping.blo.gs', 'rpc.technorati.com', 'audiorpc.weblogs.com') as $server)
				static::ping($server);
	}
	public static function postForm($form, $data) {
		$form->data->published->attr(array('label' => __('Authored on'), 'value' => $data->article['published'], 'type' => 'datetime'));
		$form->data->image->attr(array('label' => __('Image'), 'value' => $data->article['image'], 'dir' => 'ltr', 'class' => 'full'));
		$form->data->options['options'] += array('promote' => __('Promoted to front page'), 'sticky' => __('Sticky at top of lists'));
		$option_checked = array();
		if (!isset($data->article['promote']) || $data->article['promote'])
			$option_checked[1] = 'promote';
		if ($data->article['sticky'])
			$option_checked[2] = 'sticky';
		$form->data->options['value'] += $option_checked;
		
		$form->relatedLink->attr(array('type' => 'fieldset', 'legend' => 'Related links'));
		
	}
}