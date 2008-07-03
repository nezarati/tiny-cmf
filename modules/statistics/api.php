<?
namespace Statistics;
class API {
	public static function normalize(&$data, $default) {
		$max = 0;
		foreach ($data as $month => &$value) {
			$max = max($max, max($value));
			$value += $default;
		}
		return $max;
	}
}