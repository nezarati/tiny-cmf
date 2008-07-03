<?
namespace User;
class Validate {
	public static function name($value) {
		return empty($value) ? __(':name field is required.', array('!name' => __('Name'))) : TRUE;
	}
	public static function password($value) {
		return empty($value) ? __(':name field is required.', array('!name' => __('Password'))) : TRUE;
	}
	public static function passwords($confirm, $key) {
		if (empty($_POST['data'][$key]) && empty($confirm))
			return __(':name field is required.', array('!name' => __('Password')));
		else if ($_POST['data'][$key] != $confirm)
			return __('The specified passwords do not match.');
		return TRUE;
	}
	public static function oldPassword($value, $oldpasswd, $username) {
		return empty($_POST['data']['password']) && empty($_POST['data']['repassword']) || $oldpasswd == API::password($value, $username) ? TRUE : __('Current password is not valid.');
	}
	public static function changePassword($value, $confirm) {
		return $value == $_POST['change_passwd'][$confirm] ? TRUE : __('The specified passwords do not match.');
	}
	public static function username($value, $id = -1, $service = SERVICE_USER) {
		if (empty($value))
			return __(':name field is required.', array('!name' => __('Username')));
		else if (strlen($value)>15 || strlen($value)<4)
			return __(strlen($value)<4 ? ':name cannot be shorter than :min characters but is currently :length characters long.' : ':name cannot be longer than :max characters but is currently :length characters long.', array('!name' => __('Username'), '%max' => 15, '%min' => 6, '%length' => strlen($value)));
		else if (!preg_match('/^[.A-z0-9_\-]{3,24}$/', $value))
			return __('The :field contains an illegal character.', array('!field' => __('Username')));
		else if ($id != -1 && \Model\User::all()->filter('service', $service)->filter('username', $value)->filter('id', $id, '!=')->limit(1)->count())
			return __('The :field :value is already taken.', array('!field' => __('Username'), '%value' => $value));
		return TRUE;
	}
	public static function mail($value, $id = -1, $service = SERVICE_USER) {
		if (empty($value))
			return __(':name field is required.', array('!name' => __('E-mail')));
		else if (!is_string(filter_var($value, FILTER_VALIDATE_EMAIL)) || !JOORCHIN_DEBUG_MODE && !getmxrr(substr($value, strrpos($value, '@') + 1), $hosts))
			return __('The :field :value is not valid.', array('!field' => __('e-mail address'), '%value' => $value));
		else if ($id != -1 && \Model\User::all()->filter('service', $service)->filter('mail', $value)->filter('id', $id, '!=')->limit(1)->count())
			return __('The :field :value is already taken.', array('!field' => __('e-mail address'), '%value' => $value));
		return TRUE;
	}
}