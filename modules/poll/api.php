<?
namespace Poll;
class API {
	public static function widget() {
		return;
	}
	public static function block($service = 'core', $node = 1) {
		foreach (\DB::query('select id from {poll} where service = :0 && node = :1 && expire > :2', $service, $node, GMT) as $row)
			$r .= '<div id="poll-'.$service.'-'.$row->id.'"></div><script>Poll.view("'.$service.'", '.$row->id.')</script>';
		return $r;
	}
	public static function save($data) {
		\View::status(__('Poll :title has been created.', array('%title' => $data->title)));
	}
}