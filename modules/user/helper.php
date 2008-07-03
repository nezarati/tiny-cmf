<?
namespace User;
define('SERVICE_USER', \Service::identifier('user'));
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'user');
}