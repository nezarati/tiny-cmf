<?
namespace Link;
define('SERVICE_LINK_MAIN', \Service::identifier('link', 'main'));
\Registry::setInstance('link', array('order' => 'created', 'sort' => 'desc', 'perPage' => 20));
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'link');
}