<?
namespace User;
class Controller extends \Controller {
	const ROLE_GUEST = 2, STATUS_ACTIVE = 1, ROLE_REGISTER = 3; # TODO
	protected $permission = array('register' => 1, 'login' => 1, 'account' => 'administer account', 'logout' => 'administer account', 'edit' => 'administer users', 'index' => 'administer users', 'delete' => 'administer users');
	protected function register($A, $D) {
		$this->view->pageTitle[] = __('Create new account');
		if ($this->callback) {
			$this->view->script = empty($_POST['continue']) ? 'navigation()' : 'document.location.href = "'.$_POST['continue'].'"';
			$_SESSION['user']['permission'] = \Role\API::load(self::ROLE_REGISTER)->permission;
			return (bool) $_SESSION['user']['id'] = API::save((object)array('name' => $D->username, 'username' => strtolower($D->username), 'password' => API::hashPassword($D->password), 'mail' => strtolower($D->mail), 'created' => GMT, 'status' => self::STATUS_ACTIVE, 'role' => self::ROLE_REGISTER));
		}
		$form = new \Form;
		$form->data->attr(array('type' => 'fieldset', 'legend' => __('Account information')));
		$form->data->username->attr(array('dir' => 'ltr', 'validator' => array('\User\validate::username', 0), 'label' => __('Username')));
		$form->data->password->attr(array('type' => 'password', 'dir' => 'ltr', 'validator' => '\User\validate::password', 'label' => __('Password')));
		$form->data->repassword->attr(array('type' => 'password', 'dir' => 'ltr', 'validator' => array('\User\validate::passwords', 'password'), 'label' => __('Confirm password')));
		$form->data->mail->attr(array('dir' => 'ltr', 'validator' => array('\User\validate::mail', 0), 'label' => __('E-mail address')));
		$form->button->continue->attr(array('type' => 'hidden', 'name' => 'continue', 'value' => $_REQUEST['continue']));
		$form->button->submit['value'] = __('Create new account');
		return $form;
	}
	protected function login($A) {
		$this->view->pageTitle[] = __('Log in');
		if ($this->callback) {
			$D = (object)$_POST['login'];
			if (($result = \Model\User::all()->filter('service', SERVICE_USER)->filter('username', strtolower($D->username))->map()->fetch()) && API::checkPassword($D->password, $result->password)) {
				if ($result->status == 0) {
					\View::error(__('The username :name has not been activated or is blocked.', array('%name' => $result->username)));
					return;
				}
				$result->permission = \Role\API::load($result->role)->permission;
				$_SESSION['user'] = (array)$result;
				if ($D->persistent) {
					\Cookie::set('user[username]', $result->username);
					\Cookie::set('user[password]', $result->password);
				}
				\View::status(__('Welcome dear :name ...', array('%name' => $result->name)));
				return \View::script(empty($_POST['continue']) ? 'navigation()' : 'document.location.href = "'.$_POST['continue'].'"');
			} else
				\View::error(__('Sorry, unrecognized username or password.'));
			return FALSE;
		}
		$form = new \Form;
		$form->login->attr(array('type' => 'fieldset', 'legend' => __('User login')));
		$form->login->username->attr(array('dir' => 'ltr', 'validator' => '\User\Validate::username', 'label' => __('Username')));
		$form->login->password->attr(array('type' => 'password', 'dir' => 'ltr', 'validator' => '\User\Validate::password', 'label' => __('Password')));
		$form->login->persistent->attr(array('type' => 'checkbox', 'label' => __('Keep me logged in')));
		$form->button->continue->attr(array('type' => 'hidden', 'name' => 'continue', 'value' => $_REQUEST['continue']));
		$form->button->submit['value'] = __('Log in');
		$form->button->register->attr(array('type' => 'link', 'onclick' => 'return $(this).dialog()', 'href' => '/user/register?continue='.$_REQUEST['continue'], 'class' => 'button'));
		$form->button->register = __('Create new account');
		# $form->button->reset->attr(array('type' => 'button', 'value' => __('Forgot your password?')));
		return $form;
	}
	protected static function logout() {
		session_destroy();
		\Cookie::delete('user[username]', 'user[password]');
		return \View::script('document.location = "/"').__('Please wait...');
	}
	protected function account($A, $D) {
		$A->id = USER_ID;
		return $this->edit($A, $D);
	}
	protected function edit($A, $D) {
		$access = API::access('administer users');
		(is_numeric($A->id) && ($A->id == USER_ID || $access)) || die;
		$data = API::load($A->id);
		if ($this->callback) {
			$D->id = $A->id;
			if (!$access || $A->id == USER_ID)
				unset($D->role, $D->status);
			if ($_POST['change_passwd']['passwd'])
				$D->password = API::hashPassword($_POST['change_passwd']['passwd']);
			return (bool) API::save($D);
		}
		$form = new \Form;
		$form->data->attr(array('legend' => __('Account information'), 'type' => 'fieldset'));
		$access && $form->data->username->attr(array('label' => __('Username'), 'value' => $data->username, 'dir' => 'ltr', 'validator' => array('\User\validate::username', $A->id)));
		$form->data->name->attr(array('label' => __('Name'), 'value' => $data->name, 'validator' => '\User\validate::name'));
		$form->data->mail->attr(array('label' => 'E-mail address', 'value' => $data->mail, 'dir' => 'ltr', 'validator' => array('\User\validate::mail', $A->id)));
		$access && $form->data->role->attr(array('type' => 'radio', 'label' => __('Role'), 'options' => \Role\API::feed()->fetchAllKeyed('id', 'name'), 'value' => $data->role));
		$access && $A->id != USER_ID && $form->data->status->attr(array('type' => 'radio', 'label' => __('Status'), 'options' => array(__('Blocked'), __('Active')), 'value' => $data->status));
		$form->change_passwd->attr(array('legend' => __('Change password'), 'type' => 'fieldset'));
		$form->change_passwd->passwd->attr(array('type' => 'password', 'dir' => 'ltr', 'label' => __('Password'), 'validator' => array('\User\validate::changePassword', 'repasswd')));
		$form->change_passwd->repasswd->attr(array('type' => 'password', 'dir' => 'ltr', 'label' => __('Confirm password')));
#		\View::script('$("#change_passwd-passwd").keyup(check_pass_strength);')-'<div id="pass-strength-result">'.__('Strength indicator').'</div>' TODO
		$form->button->submit['value'] = __('Save');
		return $form;
	}
	protected function index($A) {
		return new \TableSelect(NULL, array('username' => array('data' => __('Username'), 'field' => 'username', 'primary' => TRUE), 'status' => array('data' => __('Status'), 'field' => 'status'), 'memberFor' => array('data' => __('Member for'), 'field' => 'created', 'sort' => 'asc'), 'lastAccess' => array('data' => __('Last access'), 'field' => 'accessed')), \Model\User::all()->fields('id', 'username', 'status', 'created', 'accessed')->filter('service', SERVICE_USER)->map(function($model) {
			$model->status = $model->status ? __('active') : __('blocked');
			$model->memberFor = format_date($model->created, 'period');
			$model->lastAccess = $model->accessed ? format_date($model->accessed, 'period') : __('never');
			$model->actions['edit'] = array('href' => '/user/'.$model->id.'/edit', 'type' => 'edit');
			$model->actions['delete'] = array('href' => '/user/'.$model->id.'/delete', 'type' => 'delete', 'disabled' => $model->id == USER_ID);
			$model->operations = \View::operations($model->operations);
		}));
	}
	protected static function delete($A) {
		is_numeric($A->id) && $A->id != USER_ID || die;
		return (bool) API::delete($A->id);
	}
	
	protected function autocomplete($A) { # TODO
		return \DB::query('select username from {user} where lower(name) like lower(:name)', array('name' => $A->username))->fetchAllKeyed('username', 'username');
	}
}