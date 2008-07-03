<?
namespace Menu;
class Controller extends \Controller {
	protected $permission = array('navigation' => 1, 'archive' => 'administer menu', 'edit' => 'administer menu', 'delete' => 'administer menu');
	protected static function navigation() {
		if (USER_ID) {
			$navigation = \Hook::invoke('menu');
			#uasort($navigation, function($a, $b) {
			#	return $a['weight']>$b['weight'];
			#});
			foreach ($navigation as $path => $data)
				if ($parent = $data['parent']) {
					unset($data['parent'], $data['access arguments']);
					$navigation[$parent]['children'][$path] = $data;
					unset($navigation[$path]);
				}
			self::normalize($navigation, $navigation);
		} else
			$navigation = array(\Registry::getInstance()->frontPage => array('title' => __('Home')), 'user/login' => array('title' => __('Log in')), 'user/register' => array('title' => __('Create new account')));
		return array('navigation' => $navigation, 'status' => \Hook::invoke('status'));
	}
	private static function normalize(&$data, &$slice) {
		foreach ($slice as $path => &$model) {
			if (is_array($model['children']))
				self::normalize($data, $model['children']);
			else if (isset($data[$path])) {
				$model['children'] = $data[$path]['children'];
				unset($data[$path]);
			}
		}
	}
	
	private function form($data = NULL) {
		$data->weight || $data->weight = 1;
		isset($data->status) || $data->status = 1;
		$form = new \Form;
		$form->data['type'] = 'fieldset';
		$form->data->title->attr(array('label' => __('Title'), 'value' => $data->title, 'validator' => array('validate_match', REGEX_TITLE, __(':name field is not valid.', array('!name' => __('Title'))))));
		$data->callback || $form->data->content->attr(array('label' => __('Content'), 'dir' => 'ltr', 'type' => 'textarea', 'value' => $data->content, 'class' => 'full', 'rows' => 5, 'validator' => array('validate_match', REGEX_CONTENT, __(':name field is not valid.', array('!name' => __('Content'))))));
		$form->data->weight->attr(array('label' => __('Weight'), 'dir' => 'ltr', 'value' => $data->weight, 'type' => 'weight'));
		$form->data->status->attr(array('label' => __('Status'), 'dir' => 'ltr', 'value' => $data->status, 'type' => 'radio', 'options' => array(__('Hidden'), __('Visible'))));
		$form->button->submit['value'] = __('Save');
		return $form;
	}
	protected function archive() {
		return new \TableSelect(NULL, array('title' => array('data' => __('Title'), 'primary' => TRUE), 'weight' => array('data' => __('Weight'), 'sort' => 'asc', 'field' => 'weight'), 'status' => array('data' => __('Status'), 'field' => 'status')), \Model\Widget::all()->fields('id', 'status', 'weight', 'title', 'callback')->filter('service', SERVICE_MENU)->map(function($doc) {
			$doc->status = $doc->status ? __('Visible') : __('Hidden');
			$doc->actions['edit'] = array('type' => 'edit', 'href' => '/menu/'.$doc->id.'/edit');
			$doc->actions['delete'] = array('type' => 'delete', 'href' => '/menu/'.$doc->id.'/delete', 'disabled' => (bool)$doc->callback);
		}));
	}
	protected function edit($A, $D) {
		is_numeric($A->id) || die;
		if ($this->callback) {
			unset($D->callback);
			$D->id = $A->id;
			return (bool) API::save($D);
		}
		return $this->form($A->id ? API::load($A->id) : new \StdClass);
	}
	protected function delete($A) {
		is_numeric($A->id) && !API::load($A->id)->callback || die;
		return API::delete($A->id);
	}
}