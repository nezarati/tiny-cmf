<?
namespace Album;
define('SERVICE_ALBUM', \Service::identifier('album'));
define('SERVICE_TAXONOMY_ALBUM', \Service::identifier('taxonomy', 'album'));
define('SERVICE_STORAGE_ALBUM', \Service::identifier('storage', 'album'));
define('SERVICE_COMMENT_ALBUM', \Service::identifier('comment', 'album'));
define('SERVICE_RATE_ALBUM', \Service::identifier('rate', 'album'));
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'album');
}
