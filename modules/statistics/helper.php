<?
namespace Statistics;
function __($msgid, Array $arg = NULL) {
	return \__($msgid, $arg, 'statistics');
}
function multiSort(&$array) {
	ksort($array);
	foreach ($array as $key => &$value)
		if (is_array($value))
			multiSort($value);
}