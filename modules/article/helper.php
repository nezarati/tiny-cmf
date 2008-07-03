<?
namespace Article;
define('SERVICE_POST_ARTICLE', \Service::identifier('post', 'article'));
define('SERVICE_TAXONOMY_ARTICLE', \Service::identifier('taxonomy', 'article'));
define('SERVICE_COMMENT_ARTICLE', \Service::identifier('comment', 'article'));
define('SERVICE_RATE_ARTICLE', \Service::identifier('rate', 'article'));
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'article');
}