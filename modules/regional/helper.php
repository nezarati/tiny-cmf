<?
namespace Regional;
define('SERVICE_REGIONAL', \Service::identifier('regional'));
\Registry::setInstance('regional', array('language' => 'en', 'timezone' => 'UTC', 'calendar' => 'gregorian', 'formatShort' => 'm/d/Y - H:i', 'formatLong' => 'l, F j, Y - H:i', 'formatMedium' => 'D, m/d/Y - H:i', 'firstDay' => 0), TRUE);
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'regional');
}