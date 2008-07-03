<?
namespace Post;
class Controller extends \Controller {
	public function __construct() {
		\Service::exists('post', $_REQUEST['arg']['service']) && define('SERVICE_POST', \Service::identifier('post', $_REQUEST['arg']['service']));
		parent::__construct();
	}
	
	protected $permission = array('preferences' => 'administer posts', 'archive' => 'post contents', 'edit' => 'post contents', 'delete' => 'post contents');
	
	protected function preferences($A, $D) {
		is_numeric($A->id) && defined('SERVICE_POST') || die;
		$cfg = \Registry::getInstance('post', SERVICE_POST);
		if ($this->callback) {
			foreach ($cfg as $key => $value)
				$cfg->$key = $D->$key;
			\View::status(__('The configuration options have been saved.'));
			return TRUE;
		}
		
		$form = new \Form;
		
		$form->data->attr(array('type' => 'fieldset'));
		$form->data->order->attr(array('label' => __('Ordering'), 'value' => $cfg->order, 'type' => 'radio', 'options' => array('published' => __('Published'))));
		$form->data->sort->attr(array('label' => __('Sort by'), 'value' => $cfg->sort, 'type' => 'sort'));
		$form->data->perPage->attr(array('label' => __('Number Of Items Per Page'), 'value' => $cfg->perPage, 'type' => 'limit'));
		$form->data->perFeed->attr(array('label' => __('Number Of Items Per Feed'), 'value' => $cfg->perFeed, 'type' => 'limit'));
		
		$form->button->submit['value'] = __('Save configuration');
		return $form;
	}
	
	protected function archive($A) {
		$services = \Service::required('post');
		$query = \Model\Post::all()->fields('id', 'title', 'service', 'created', 'modified', 'user')->filter('service', array_keys($services), 'in');

		\User\API::access('administer posts') || $query->filter('user', USER_ID);
		
		return new \TableSelect(NULL, array('module' => array('data' => __('Type'), 'field' => 'service'), 'title' => array('data' => __('Title'), 'field' => 'title', 'primary' => TRUE), 'author' => array('data' => __('Author')), 'modified' => array('data' => __('Date'), 'field' => 'modified', 'sort' => 'desc')), $query->map(function($model) use($services) {
			$service = $services[$model->service];
			$model->module = \Post\__($service->title);
			$model->author = \User\API::load($model->user)->name;
			
			$date = min($model->created, $model->modified);
			$model->modified = '<abbr title="'.format_date($date, 'Y/m/d H:i:s a').'">'.format_date($date, $date>86400 ? 'Y/m/d' : 'period').'</abbr><br />'.__($model->created  == $model->modified ? 'Published' : 'Last Modified');
			
			$model->actions['edit'] = array('type' => 'edit', 'href' => '/post/'.$model->id.'/'.$service->name.'/edit');
			\Hook::invoke($service->name.'_action', $service->name, $model);
			$model->actions['delete'] = array('type' => 'delete', 'href' => '/post/'.$model->id.'/'.$service->name.'/delete');
		}), __('No content available.'));
	}
	
	protected function edit($A, $D) {
		is_numeric($A->id) && defined('SERVICE_POST') && (\User\API::access('administer posts') || API::load($A->id)->user = USER_ID) || die; # TODO
		if ($this->callback) {
			$D->id = $A->id;

			foreach ($D->options as $key => $value)
				$D->$key = $value;
			unset($D->options);
			
			# TODO PHP Bug
			#$domain = new \Model\Domain(array('parent' => 0, 'id' => DOMAIN, 'modified' => GMT));
			#$domain->put();
			\Model\Domain::update(array('parent' => 0, 'id' => DOMAIN), array('$set' => array('modified' => GMT))); # MongoDB
			
			\Hook::call($A->service.'_presave', $D);
			//$this->view->script = '$("#loader").attr("src", "http://pingomatic.com/ping/?'.http_build_query(array('title' => \Registry::getInstance()->title, 'blogurl' => \Registry::getInstance()->home, 'rssurl' => '')+array_fill_keys(array('chk_weblogscom', 'chk_blogs', 'chk_technorati', 'chk_feedburner', 'chk_syndic8', 'chk_newsgator', 'chk_myyahoo', 'chk_pubsubcom', 'chk_blogdigger', 'chk_blogrolling', 'chk_blogstreet', 'chk_moreover', 'chk_weblogalot', 'chk_icerocket', 'chk_newsisfree', 'chk_topicexchange', 'chk_google', 'chk_tailrank', 'chk_bloglines', 'chk_aiderss', 'chk_skygrid', 'chk_bitacoras', 'chk_audioweblogs', 'chk_rubhub', 'chk_geourl', 'chk_a2b', 'chk_blogshares'), 'on')).'")';
			return (bool) API::save($D);
		}
		return $this->form($A->id ? API::load($A->id) : new \StdClass);
	}
	private function form($data = NULL) {
		$form = new \Form;
		$form->data['type'] = 'fieldset';
		$form->data['legend'] = __('Information');

		$form->data->title->attr(array('label' => __('Title'), 'value' => $data->title, 'validator' => array('validate_match', REGEX_TITLE, __(':name field is required.', array('!name' => __('Title'))))));

		$form->content->attr(array('legend' => __('Content'), 'type' => 'fieldset'));
		$script = \View::jQuery().\View::editor('
(function editor() {
	if (window.$ && $.cleditor)
		$(document).ready(function() {
			$("#content-source").cleditor({width: "100%", height: "100%"})[0].focus().change(function() {
				this.updateTextArea();
			});
		});
	else
		setTimeout(editor, 1);
})();
');
		$form->content = $script.'<textarea name="data[content]" rows="5" class="full" id="content-source">'.htmlspecialchars($data->content).'</textarea>';
		
		$option_checked = array();
		if (!isset($data->status) || $data->status)
			$option_checked[] = 'status';
		$form->data->options->attr(array('label' => __('Publishing options'), 'type' => 'checkbox', 'value' => $option_checked, 'options' => array('status' => __('Published'))));
		$form->button->submit['value'] = __('Save');
		
		\Hook::call($_REQUEST['arg']['service'].'_form', $form, $data);
		return $form;
	}
	
	protected function delete($A) {
		is_numeric($A->id) && defined('SERVICE_POST') && (\User\API::access('administer posts') || API::load($A->id)->user = USER_ID) || die; # TODO
		\Hook::call($A->service.'_delete', $A->service, $A->id);
		return (bool) API::delete($A->id);
	}
}