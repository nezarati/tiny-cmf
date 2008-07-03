<?
namespace Forum;
class API {
	public static function permalink(Array $data) {
		return '/forum/'.$data['id'].'/topic';
	}
}