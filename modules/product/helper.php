<?
namespace Product;
define('SERVICE_POST_PRODUCT', \Service::identifier('post', 'product'));
define('SERVICE_TAXONOMY_PRODUCT', \Service::identifier('taxonomy', 'product'));
define('SERVICE_STORAGE_PRODUCT', \Service::identifier('storage', 'product'));
//define('SERVICE_COMMENT_PRODUCT', \Service::identifier('post', 'comment', 'product'));
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'product');
}