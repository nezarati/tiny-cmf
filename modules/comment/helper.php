<?
namespace Comment;
\Registry::setInstance('comment', array('order' => 'created', 'sort' => 'desc', 'perPage' => 15));
define('SERVICE_RATE_COMMENT', \Service::identifier('rate', 'comment'));
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'comment');
}