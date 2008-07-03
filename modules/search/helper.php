<?
namespace Search;
\Registry::setInstance('search', array('perPage' => 5));
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'search');
}