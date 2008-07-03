<?
namespace Menu;
define('SERVICE_MENU', \Service::identifier('menu'));
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'menu');
}