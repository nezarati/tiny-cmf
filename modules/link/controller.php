<?
namespace Link;
class Controller extends \Controller {
	
	public function __construct() {
		\Service::exists('link', $_REQUEST['arg']['service']) && define('SERVICE_LINK', \Service::identifier('link', $_REQUEST['arg']['service']));
		parent::__construct();
	}
	
	protected $permission = array('preferences' => 'administer links', 'index' => 1, 'hit' => 1, 'archive' => 'administer links', 'edit' => 'administer links', 'delete' => 'administer links');
	private static function form($data = NULL) {
		$form = new \Form;
		$form->data['type'] = 'fieldset';
		$form->data->title->attr(array('label' => __('Title'), 'value' => $data->title, 'validator' => array('validate_match', REGEX_TITLE, __(':name field is required.', array('!name' => __('Title'))))));
		$form->data->url->attr(array('label' => __('URL'), 'value' => $data->url ?: 'http://', 'dir' => 'ltr', 'validator' => array('validate_match', '#^https?\://([A-z\-0-9]+\.)+[a-z]{2,4}/?#', __(':name field is not valid.', array('!name' => __('URL'))))));
		$form->data->description->attr(array('label' => __('Description'), 'value' => $data->description, 'type' => 'textarea', 'rows' => 3));
		$form->data->status->attr(array('label' => __('Status'), 'value' => isset($data->status) ? $data->status : 1, 'type' => 'radio', 'options' => array(__('Inactive'), __('Active'))));
		$form->data->hit->attr(array('label' => __('Hit'), 'value' => (int) $data->hit, 'class' => 'tiny', 'dir' => 'ltr', 'validator' => array('validate_match', REGEX_INTEGER, __(':name field is not valid.', array('!name' => __('Hit'))))));
		$form->button->submit['value'] = __('Save');
		return $form;
	}
	protected function preferences($A, $D) {
		is_numeric($A->id) && defined('SERVICE_LINK') || die;
		$cfg = \Registry::getInstance('link', SERVICE_LINK);
		if ($this->callback) {
			foreach ($cfg as $key => $value)
				$cfg->$key = $D->$key;
			\View::status(__('The configuration options have been saved.'));
			return TRUE;
		}
		
		$form = new \Form;
		
		$form->data->attr(array('type' => 'fieldset'));
		$form->data->order->attr(array('label' => __('Ordering'), 'value' => $cfg->order, 'type' => 'radio', 'options' => array('created' => __('Created'), 'hit' => __('Hit'), '__titleLength' => __('Triangle')))); # TODO: , 'rand' => __('Random')
		$form->data->sort->attr(array('label' => __('Sort by'), 'value' => $cfg->sort, 'type' => 'sort'));
		$form->data->perPage->attr(array('label' => __('Number Of Items Per Page'), 'value' => $cfg->perPage, 'type' => 'limit'));
		
		$form->button->submit['value'] = __('Save configuration');
		return $form;
	}
	protected function archive($A, $D) {
		$services = \Service::required('link');
		$query = \Model\Link::all()->fields('service', 'id', 'title', 'hit', 'status')->filter('service', array_keys(\Service::required('link')), 'in')->map(function($doc) use($services) {
			$doc->module = __($services[$doc->service]->title);
			$doc->status = __($doc->status ? 'active' : 'inactive');
			$doc->actions['edit'] = array('type' => 'edit', 'href' => '/link/'.$doc->id.'/'.$services[$doc->service]->name.'/edit');
			$doc->actions['delete'] = array('type' => 'delete', 'href' => '/link/'.$doc->id.'/'.$services[$doc->service]->name.'/delete');
		});
		return new \TableSelect(NULL, array('module' => array('data' => __('Type'), 'field' => 'service'), 'title' => array('data' => __('Title'), 'field' => 'title', 'primary' => TRUE), 'hit' => array('data' => __('Hit'), 'field' => 'hit', 'sort' => 'desc'), 'status' => array('data' => __('Status'), 'field' => 'status')), $query, __('No links available.'));
	}
	protected function edit($A, $D) {
		is_numeric($A->id) && defined('SERVICE_LINK') || die;
		if ($this->callback) {
			$D->id = $A->id;
			return (bool) API::save($D);
		}
		return $this->form($A->id ? API::load($A->id) : new \StdClass);
	}
	protected function delete($A) {
		is_numeric($A->id) && defined('SERVICE_LINK') || die;
		return (bool) API::delete($A->id);
	}
	protected function hit($A) {
		is_numeric($A->id) && defined('SERVICE_LINK') || die;
		\Model\Link::update(array('service' => SERVICE_LINK, 'id' => (int)$A->id), array('$inc' => array('hit' => 1))); # MongoDB
	}
	protected function index() {
		return API::widget();
	}
}