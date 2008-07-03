<?
namespace Role;
define('SERVICE_ROLE', \Service::identifier('role'));
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'user');
}