<?
namespace Forum;
define('SERVICE_POST_FORUM', \Service::identifier('post', 'forum'));
define('SERVICE_TAXONOMY_FORUM', \Service::identifier('taxonomy', 'forum'));
define('SERVICE_COMMENT_FORUM', \Service::identifier('comment', 'forum'));
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'forum');
}