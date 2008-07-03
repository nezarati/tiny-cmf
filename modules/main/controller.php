<?
namespace Main;
class Controller extends \Controller {
	protected $permission = array('updated' => 1, 'preferences' => 'administer site configuration', 'logo' => 1, 'quick' => 1, 'block' => 'administer site configuration', 'robots' => 1, 'extendJS' => 1, 'extendCSS' => 1);
	protected static function updated() {
		$updated = array();
		foreach (\Model\Domain::all()->fields('id', 'host')->filter('parent', 0)->filter('status', 1)->sort('-modified')->limit(10) as $row) {
			\Service::init($row->id);
			$cfg = \Registry::getInstance('main', \Service::identifier('main', NULL, $row->id));
			$updated[] = array('title' => $cfg->title, 'description' => $cfg->slogan, 'url' => 'http://'.(strpos($row->host, '.') === FALSE ? $row->host.'.chonoo.com' : 'www.'.$row->host));
		}
		return $updated;
	}
	protected function preferences($A, $D) {
		$cfg = \Registry::getInstance();
		if ($this->callback) {
			foreach ($cfg as $key => $value)
				$cfg->$key = $D->$key;
			$cfg->layout = 1;
			\View::status(__('The configuration options have been saved.'));
			return TRUE;
		}
		
		$form = new \Form;
		
		$form->data->attr(array('type' => 'fieldset'));
		$form->data->title->attr(array('label' => __('Website title'), 'value' => $cfg->title, 'validator' => array('validate_match', REGEX_TITLE, __(':name field is required.', array('!name' => __('Website title'))))));
		$form->data->name->attr(array('label' => __('Website name'), 'value' => $cfg->name, 'dir' => 'ltr', 'validator' => array('validate_match', REGEX_TITLE, __(':name field is required.', array('!name' => __('Website name'))))));
		$form->data->home->attr(array('label' => __('Website URL'), 'value' => $cfg->home, 'dir' => 'ltr', 'tip' => __('The full address of your website (e.g. http://www.example.com)'), 'validator' => array('validate_match', REGEX_URL, __(':name field is required.', array('!name' => __('Website URL'))))));
		$form->data->slogan->attr(array('label' => __('Slogan'), 'value' => $cfg->slogan, 'tip' => __("How this is used depends on your site's theme."), 'class' => 'full'));
		$form->data->mail->attr(array('label' => __('E-mail address'), 'value' => $cfg->mail, 'dir' => 'ltr', 'validator' => array('\User\validate::mail', -1)));
		$form->data->frontPage->attr(array('label' => __('Default front page'), 'prefix' => '<span dir="ltr">http://'.$_SERVER['HTTP_HOST'].'/', 'suffix' => '</span>', 'value' => $cfg->frontPage, 'tip' => __('The home page displays content from this relative URL. If unsure, specify "article".'), 'dir' => 'ltr', 'validator' => '\Main\Validate::frontPage'));
		$form->data->keywords->attr(array('label' => __('Keywords'), 'value' => $cfg->keywords, 'type' => 'textarea'));
		
		$form->button->submit['value'] = __('Save configuration');
		return $form;
	}
	protected function logo() {
		header('Content-Type: image/png');
		$i = imagecreate(80, 15);
		imagecolorallocate($i, 0x89, 0x8E, 0x79);
		$fgc = imagecolorallocate($i, 0xff, 0xff, 0xff);
		$bc = imagecolorallocate($i, 0x66, 0x66, 0x66);
		$lc = imagecolorallocate($i, 0xff, 0xff, 0xff);
		$ic = imagecolorallocate($i, 0xff, 0xff, 0xff);
		$ibc = imagecolorallocate($i, 0x31, 0x65, 0x9C);
		imagefilledrectangle($i, 2, 2, 24, 12, $ibc);
		imageline($i, 25, 2, 25, 12, $lc);
		imagerectangle($i, 0, 0, 80-1, 15-1, $bc);
		imagerectangle($i, 1, 1, 80-2, 15-2, $lc);
		imagestring($i, 3, 7, 0, 'CH', $ic);
		imagestring($i, 1, 30, 3, 'Powered', $fgc);
		imagepng($i);
		imagedestroy($i);
		die;
	}
	
	protected function quick($A, $D) {
		if (!USER_ID) {
			$this->view->header['location'] = '/user/login?continue='.urlencode('/main/quick'.(empty($A->domain) ? '' : '?arg[domain]='.$A->domain));
			return TRUE;
		}
		if ($this->callback) {
			$D->domain = strtolower($D->domain);
			$domain = new \Model\Domain(array('user' => USER_ID, 'created' => GMT, 'host' => $D->domain, 'parent' => 0)); # TODO
			define('_DOMAIN', $domain->put());
			\Hook::invoke('install', $D);
			\View::status('<p>'.__('Congratulations, you\'ve added :link<br />Right now you can manage that. Click on your website URL and then by recognizing user name and password you have selected manage website.', array('link' => '<a href="http://'.$D->domain.'.chonoo.com" target="_blank">'.$D->domain.'.chonoo.com</a>')).'</p>');
			return TRUE;
		}
		$form = new \Form;
		
		$form->data->attr(array('type' => 'fieldset', 'legend' => __('Site information')));
		$form->data->title->attr(array('label' => __('Title'), 'validator' => array('validate_match', REGEX_TITLE, __(':name field is required.', array('!name' => __('Title'))))));
		$form->data->domain->attr(array('label' => __('Domain'), 'prefix' => 'http://www.', 'suffix' => '.chonoo.com/', 'dir' => 'ltr', 'validator' => '\Main\validate::subdomain', 'style' => 'width: 60px', 'class' => 'inputbox', 'value' => $A->domain));
		$form->data->language->attr(array('label' => __('Language'), 'type' => 'radio', 'options' => array('en' => 'English', 'fr' => 'French', 'fa' => 'Persian')));
		
		$form->terms->attr(array('type' => 'fieldset', 'legend' => __('Chonoo Terms')));
		$form->terms = '<p>
	<ul>
		<li>'.__('Users\' private information and also their e-mails are protected in this site and access to such information is denied to legal or real entities.').'</li>
		<li>'.__('The websites that post obscene contents and images or insult the authorities of the Islamic Republic, religions, ethnic groups and human races will be closed.').'</li>
		<li>'.__('Those users\' websites that in any ways (including the useless consumption of the main website\'s resources, hack, the advertisements\' omission and etc) endanger the financial resources or the security of the site will be dealt with and closed.').'</li>
		<li>'.__('The experimental websites, spam and inactive and contentless websites will be closed so that the site\'s resources are protected and any sort of abuse is prevented.').'</li>
	</ul>
</p>';
		$form->button->submit['value'] = __('Create');
		return $form;
	}
	protected static function block() { # TODO
		\DB::query('update {domain} set status = 0 where host = :0', array(DOMAIN));
		return TRUE;
	}
	
	protected function serviceArchive($A, $D) {
		return new \TableSelect(NULL, array('module' => array('data' => __('Module')), 'dependence' => array('data' => __('Dependence')), 'user' => array('data' => __('Publisher')), 'created' => array('data' => __('Installed On')), 'size' => array('data' => __('Size')), 'operations' => array('data' => __('Operations'))), \Model\Service::all()->filter('domain', \Model\Domain::id($A->domain))->map(function($doc) use($A) {
			$doc->operations = \View::Operations(array('delete' => array('type' => 'delete', 'title' => __('Uninstall'), 'href' => '/main/'.$doc->id.'/serviceDelete?'.http_build_query(array('arg' => array('service' => $doc->module))))));
			$doc->dependence = $doc->dependence ? __(\Service::load($doc->dependence)->title) : NULL;
			$doc->module = __(\Service::load($doc->module)->title);
			$doc->user = \User\API::load($doc->user)->name;
			$doc->created = format_date($doc->created);
		}));
	}
	protected function serviceDelete($A) {
		\View::status(__(':name has been deleted.', array('%name' => __(\Service::load($A->service)->title))));

		$query = \Model\Service::all()->fields('domain')->filter('module', $A->service)->filter('id', $A->id);

		$domain = $query->fetchField();
		\TMP::delete($domain.'.service.init', $domain.'.hook.init');

		return $query->delete();
	}
	protected function serviceAdd($A, $D) {
		if ($this->callback) {
			define('_DOMAIN', \Model\Domain::id($D->domain));
			\TMP::delete(_DOMAIN.'.service.init', _DOMAIN.'.hook.init');
			if (method_exists($class = '\\'.\Service::load($D->module)->name.'\\Hook', $method = 'install'))
				call_user_func($class.'::'.$method);
			else
				\Service::install($D->module, $D->dependence);
			return TRUE;
		}
		$form = new \Form;
		$form->data->attr(array('type' => 'fieldset'));

		$options = array('-');
		foreach (\Model\Module::all()->map() as $doc)
			$options[$doc->id] = $doc->title;
		
		$data = $form->data;
		$data->module->attr(array('type' => 'select', 'label' => __('Module'), 'options' => $options));
		$data->dependence->attr(array('type' => 'select', 'label' => __('Dependence'), 'options' => $options));
		$data->user->attr(array('label' => __('Username'), 'dir' => 'ltr'));
		$data->domain->attr(array('label' => __('Domain'), 'dir' => 'ltr'));
		
		$form->button->submit['value'] = __('Install');
		return $form;
	}
	
	protected function robots() {
		die(implode(PHP_EOL, array('Sitemap: '.implode(PHP_EOL.'Sitemap: ', \Hook::invoke('sitemap')), 'User-agent: *', 'Crawl-delay: 10', 'Disallow: /user/')));
	}
	protected function extendJS() {
		$script = implode(PHP_EOL, array_map('file_get_contents', \Hook::invoke('script')));
		
		# ClosureCompiler
		if (!JOORCHIN_DEBUG_MODE)
			$optimize = \HTTP::request('http://closure-compiler.appspot.com/compile', array(
					'js_code' => $script,
					'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
					'output_format' => 'text',
					'output_info' => 'compiled_code'
				)
			);
		header('Content-Type: text/javascript');
		die($optimize && file_put_contents('extend.js', $optimize) ? $optimize : $script);
	}
	protected function extendCSS() {
		$style = implode(PHP_EOL, array_map('file_get_contents', \Hook::invoke('style')));
		$optimize = preg_replace(array('{
			(?<=\\\\\*/)([^/\*]+/\*)([^\*/]+\*/)	# Add a backslash also at the end ie-mac hack comment, so the next pass will not touch it.
													# The added backslash does not affect the effectiveness of the hack.
			}x', '<
				\s*([@{}:;,]|\)\s|\s\()\s* |		# Remove whitespace around separators, but keep space around parentheses.
				/\*[^*\\\\]*\*+([^/*][^*]*\*+)*/ |	# Remove comments that are not CSS hacks.
			>x'), array('\1\\\\\2', '\1'), $style);
		JOORCHIN_DEBUG_MODE || file_put_contents('extend.css', $optimize);
		header('Content-Type: text/css');
		die($optimize);
	}
}