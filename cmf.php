<?PHP
/*******************************************************************
 | 						Raha TinyCMF v6.7.3
 | 			------------------------------------------------
 |	@copyright: 		(C) 2008-2009 Raha Group, All Rights Reserved
 |	@license:		CC-BY-SA-4.0 <https://creativecommons.org/licenses/by-sa/4.0>
 |	@author: 		Mahdi NezaratiZadeh <HTTPS://Raha.Group>
 |	@since:			2008-07-03 22:35:16 GMT+0330 - 2009-03-04 11:29:23 GMT+0330
********************************************************************/
const JOORCHIN_EXT_DIR = './';
function __autoload($class) {
	$class = ltrim(str_replace('\\', '/', strtolower($class)), '/');
	if (is_file($file = JOORCHIN_EXT_DIR.'includes'.DIRECTORY_SEPARATOR.$class.'.php') || is_file($file = JOORCHIN_EXT_DIR.'libraries'.DIRECTORY_SEPARATOR.$class.'.php') || is_file($file = JOORCHIN_EXT_DIR.'models'.DIRECTORY_SEPARATOR.$class.'.php')) {
		require_once $file;
		return TRUE;
	}
}
new Bootstrap;
?>
