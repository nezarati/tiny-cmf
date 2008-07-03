<?
namespace Role;
class Controller extends \Controller {
	protected $permission = array('index' => 'administer permissions', 'edit' => 'administer permissions', 'delete' => 'administer permissions');
	private static function form($data) {
		$form = new \Form;
		$form->data['type'] = 'fieldset';
		$form->data->name->attr(array('label' => __('Role name'), 'value' => $data->name, 'validator' => array('validate_match', '/^.{3,64}$/', __('You must specify a valid role name.'))));
		$form->data->permission->attr(array('label' => __('Permission'), 'type' => 'checkbox', 'value' => $data->permission));
		$permissions = array();
		foreach (\Hook::invoke('permission') as $permission => $data)
			$permissions[$permission] = $data['title'];
		$form->data->permission['options'] = $permissions;
		$form->button->submit['value'] = __('Save role');
		return $form;
	}
	protected function index() {
		return new \TableSelect(NULL, array('name' => array('data' => __('Role'), 'sort' => 'asc', 'primary' => TRUE)), \Model\Role::all()->filter('service', SERVICE_ROLE)->map(function($doc) {
			$doc->actions['edit'] = array('href' => '/role/'.$doc->id.'/edit', 'type' => 'edit', 'disabled' => $model->id == 1);
			$doc->actions['delete'] = array('href' => '/role/'.$doc->id.'/delete', 'type' => 'delete', 'disabled' => $model->id <= 3);
		}));
	}
	protected function edit($A, $D) {
		is_numeric($A->id) || die;
		if ($this->callback) {
			if ($A->id == 1)
				return FALSE;
			$D->id = $A->id;
			return (bool) API::save($D);
		}
		return $this->form($A->id ? API::load($A->id) : new \StdClass);
	}
	protected function delete($A) {
		is_numeric($A->id) && !in_array($A->id, array(1, 2, 3)) || die;
		return API::delete($A->id);
	}
}