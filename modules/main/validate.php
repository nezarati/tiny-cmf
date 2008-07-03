<?
namespace Main;
class Validate {
	public static function frontPage($value) {
		\Main\API::rewrite($value, $result);
		if (empty($result) || !in_array(\Service::id($result['module']), \Service::loaded()))
			return __(':name field is not valid.', array('!name' => __('Default front page')));
		return TRUE;
	}
	public static function subdomain($value) {
		if (empty($value))
			return __(':name field is required.', array('!name' => __('Domain')));
		else if (strlen($value)>30 || strlen($value)<4)
			return __(':name cannot be longer than :max characters but is currently :length characters long.', array('!name' => __('Domain'), '%max' => 30, '%length' => strlen($value)));
		else if (!preg_match('/^[A-z0-9\-]{4,30}$/', $value))
			return __('The :field contains an illegal character.', array('!field' => __('Domain')));
		else if (in_array($value, array('static', 'mail', 'api', 'ftp', 'blog', 'news', 'www', 'labs')) || \Model\Domain::all()->filter('host', $value)->limit(1)->count())
			return __('The :field :value is already taken.', array('!field' => __('Domain'), '%value' => $value));
		return TRUE;
	}
}