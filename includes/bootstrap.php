<?
# JOORCHIN_*, SERVICE_*, REGEX_*, GMT, IP, HOST, DOMAIN, PATH, ROUTE, USER_ID, _DOMAIN, _SERVICE
const JOORCHIN_DEBUG_MODE = 1, JOORCHIN_MODULE_DIRECTORY = 'modules/', JOORCHIN_TIME_TO_LIVE = 3600, JOORCHIN_STATIC_FILE = '/img/';
class Bootstrap {
}
if (JOORCHIN_DEBUG_MODE) {
	set_exception_handler(function($e) {
		FB::Log($e->getCode().' - '.$e->getMessage(), '#'.$e->getFile().': '.$e->getLine());
	});
	set_error_handler(function($n, $e, $f, $l) {
		FB::Log($n.' - '.$e, $f.': '.$l);
	}, E_ALL & ~E_NOTICE);
}

define('GMT', $_SERVER['REQUEST_TIME']);
define('IP', $_SERVER['REMOTE_ADDR']);
define('HOST', preg_replace(array('/^www\./', '/\.chonoo\.com$/'), '', $_SERVER['HTTP_HOST']));

iconv_set_encoding('internal_encoding', 'UTF-8');
ob_start('ob_gzhandler');

spl_autoload_register(function($class) {
	$class = ltrim(str_replace('\\', DIRECTORY_SEPARATOR, strtolower($class)), DIRECTORY_SEPARATOR);
	if (is_file($file = JOORCHIN_EXT_DIR.'modules/'.$class.'.php')) {
		@include_once JOORCHIN_EXT_DIR.'modules'.DIRECTORY_SEPARATOR.dirname($class).DIRECTORY_SEPARATOR.'helper.php';
		require_once $file;
		return TRUE;
	}
});
spl_autoload_register('__autoload');

\Model\Model::connect();

if ($result = TMP::get('domain.'.HOST))
	define('DOMAIN', $result);
else if ($result = \Model\Domain::id(HOST)) {
	define('DOMAIN', $result);
	TMP::set('domain.'.HOST, DOMAIN);
} else { # TODO: Status, NotFound
	header('refresh: 10; url=http://www.chonoo.com/main/quick?arg[domain]='.urlencode(HOST));
	header('Content-Type: text/html; charset=UTF-8');
	Registry::setInstance('main');
	$li = '';
	foreach (DB::query('select s.id serviceId, d.host from (select if(parent = 0, id, parent) domainId, host from {domain} where status = 1 && (soundex(host) = :0 || soundex(reverse(host)) = :1) limit 10) d, {service} s where s.domain = d.domainId && s.module = 1', array(soundex(HOST), soundex(strrev(HOST)))) as $row) {
		$cfg = Registry::getInstance('main', $row->serviceId);
		$li .= '<li style="margin-bottom: 4px; color: #444444"><a href="'.$cfg->home.'?ref=spelling" style="color: #21759B"><strong>'.$cfg->name.'</strong> &mdash; '.$cfg->title.'</a></li>';
	}
	die('
<div style="background-color: #FFFBCC;">
	<p style="margin-left: 0; margin-right: 12px; color:#444444; font-size: 15px; line-height: 1.6em; margin-bottom: 1.4em">'.__('Did you mean to visit any of these blogs:').'</p>
	<ul style="font-size: 15px; line-height: 1.6em; margin-bottom: 20px; margin-left: 24px">'.$li.'</ul>
</div>
');
}
register_shutdown_function('Hook::call', 'destruct');

new Session;

Service::init();
Hook::init();
Hook::call('construct');

ini_set('date.timezone', JOORCHIN_TIMEZONE);

define('PATH', preg_replace('#\?.*#', '', $_SERVER['REQUEST_URI']));
define('ROUTE', ltrim(PATH, '/') ?: Registry::getInstance()->frontPage);
Hook::call('route');

$controller = ($_REQUEST['arg']['dependence'] ?: $_REQUEST['module']).'\Controller';
$controller = (empty($_REQUEST['arg']['dependence']) && in_array(Service::id($_REQUEST['module']), Service::loaded())) || Service::exists($_REQUEST['module'], $_REQUEST['arg']['dependence']) ? new $controller : new View;
Hook::call('render');
echo $controller;

function __($msgid, $arg = NULL, $domain = 'main') {
	static $translate = array(), $loaded = array();
	is_array($arg) || ($arg = array());
	#if (JOORCHIN_LANGUAGE != 'en' && !in_array($domain, $loaded)) {
	#	$translate += \Model\I18n::all()->fields('msgid', 'msgstr')->filter('module', Service::id($domain))->filter('language', JOORCHIN_LANGUAGE)->fetchAllKeyed('msgid', 'msgstr');
	#	$loaded[] = $domain;
	#}
	$_arg = array();
	foreach ($arg as $placeholder => &$value) {
		switch ($placeholder[0]) {
			case '%':
				$value = '<em>'.htmlspecialchars($value).'</em>';
				$_arg[':'.substr($placeholder, 1)] = $value;
				break;
			case '@':
				$value = htmlspecialchars($value);
				$_arg[':'.substr($placeholder, 1)] = $value;
				break;
			case '!':
				$_arg[':'.substr($placeholder, 1)] = $value;
				break;
			default:
				$_arg[':'.$placeholder] = $value;
		}
	}
	if (JOORCHIN_LANGUAGE == 'en')
		return strtr($msgid, $_arg);
	if (!$translate[$msgid])
		$translate[$msgid] = TMP::get($key = 'i10n.'.JOORCHIN_LANGUAGE.'."'.$msgid.'"') ?: TMP::set($key, \Model\I18n::all()->fields('msgstr')->filter('language', JOORCHIN_LANGUAGE)->filter('msgid', $msgid)->filter('msgstr', '', '!=')->fetch()->msgstr, 0);
	if (empty($translate[$msgid]))
		try {
			$translate[$msgid] = $msgid;
			\Model\I18n::put(array('module' => Service::id($domain), 'language' => JOORCHIN_LANGUAGE, 'msgid' => $msgid, 'msgstr' => NULL)); # MongoDB
		} catch (Exception $e) {
		}
	return strtr($translate[$msgid] ?: $msgid, $_arg);
}

function format_plural($count, $singular, $plural, array $args = array()) {
	$args['@count'] = $count;
	if ($count == 1)
		return __($singular, $args);
	// Get the plural index through the gettext formula.
	$index = (function_exists('locale_get_plural')) ? locale_get_plural($count, NULL) : -1;
	// Backwards compatibility.
	if ($index < 0)
		return __($plural, $args);
	else
		switch ($index) {
			case "0":
				return __($singular, $args);
			case "1":
				return __($plural, $args);
			default:
				unset($args['@count']);
				$args['@count[' . $index . ']'] = $count;
				return __(strtr($plural, array('@count' => '@count[' . $index . ']')), $args);
		}
}
function format_interval($timestamp, $granularity = 2) {
	$units = array(
		'1 year|:count years' => 31536000,
		'1 month|:count months' => 2592000,
		'1 week|:count weeks' => 604800,
		'1 day|:count days' => 86400,
		'1 hour|:count hours' => 3600,
		'1 min|:count min' => 60,
		'1 sec|:count sec' => 1
	);
	$output = '';
	foreach ($units as $key => $value) {
		$key = explode('|', $key);
		if ($timestamp >= $value) {
			$output .= ($output ? ' ' : '') . format_plural(floor($timestamp / $value), $key[0], $key[1]);
			$timestamp %= $value;
			$granularity--;
		}
		if ($granularity == 0)
			break;
	}
	return $output ? $output : __('0 sec');
}
function format_date($timestamp, $format = 'medium', $zone = NULL) {
	$cfg = Registry::getInstance('regional', SERVICE_REGIONAL);
	static $timezone = array();
	if (!$zone)
		$zone = $cfg->timezone;
	if (!isset($timezone[$zone]))
		$timezone[$zone] = new DateTimeZone($zone);
	$className = '\Regional\DateTime'.ucFirst(JOORCHIN_CALENDAR);
	$date_time = new $className('@'.$timestamp);
	switch ($format) {
		case 'period':
			static $period = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade');
			$difference = GMT - $date_time->getTimestamp();
			foreach (array(60, 60, 24, 7, 4.35, 12, 10) as $index => $length)
				if ($difference >= $length)
					$difference /= $length;
				else
					break;
			return __(':length :period ago', array('@length' => round($difference), '%period' => __($period[$index])));
			break;
		case 'short':
			$format = $cfg->formatShort;
			break;
		case 'long':
			$format = $cfg->formatLong;
			break;
		case 'medium':
			$format = $cfg->formatMedium;
	}
	$date_time->setTimezone($timezone[$zone]);
	return $date_time->format($format);
}

function url(Array $data = array()) {
	return PATH.'?'.http_build_query($data+$_GET);
}