<?
namespace Poll;
class Validate {
	public static function choice($d) {
		$i = 1;
		foreach ($d['title'] as $k => $t) {
			$t != '' || $d['delete'][$k] || ($e[] = __('Please fill currect value for answer :index.', array('%index' => $i)));
			is_numeric($d['vote'][$k]) || $d['delete'][$k] || ($e[] = __('Please fill currect value for vote of answer :index.', array('%index' => $i)));
			$i++;
		}
		return $e ? $e : 1;
	}
	public static function answer($d) {
		if (is_array($_POST['data']['option']['delete']))
			foreach ($_POST['data']['option']['delete'] as $v)
				$v && $i++;
		return count($_POST['data']['answer'])+count($_POST['data']['option']['title'])-$i<3 ? __('You must fill in at least :number choices.', array('%number' => (int)$_POST['data']['multiple']>1 ? $_POST['data']['multiple'] : 2)) : 1;
	}
	public static function multiple($v) {
		return is_numeric($v) && $v>0 ? 1 : PollLang::VALIDATE_MULTIPLE;
	}
}