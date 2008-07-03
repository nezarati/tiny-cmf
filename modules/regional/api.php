<?
namespace Regional;
class API {
	public static function install($D) {
		$cfg = \Registry::getInstance('regional', \Service::install('regional'));
		if ($D->language)
			$cfg->language = $D->language;
	}
	
	public static function time($name, $time = GMT) {
		$time || ($time = GMT);
		list($h, $m, $s) = explode(':', date('H:i:s', $time));
		for ($r.='<select name="'.$name.'[hour]" size="1">', $i=0; $i<24; $r.='<option value="'.$i.'"'.($h == $i ? ' selected="selected"' : '').'>'.$i++.'</option>');
		$r .= '</select> : ';
		for ($r.='<select name="'.$name.'[minute]" size="1">', $i=0; $i<60; $r.='<option value="'.$i.'"'.($m == $i ? ' selected="selected"' : '').'>'.$i++.'</option>');
		$r .= '</select> : ';
		for ($r.='<select name="'.$name.'[second]" size="1">', $i=0; $i<60; $r.='<option value="'.$i.'"'.($s == $i ? ' selected="selected"' : '').'>'.$i++.'</option>');
		$r .= '</select>';
		return $r;
	}
	public static function timestamp($data) {
		if (is_array($data))
			$data = (object)$data;
		$date = new \DateTime('now');
		return call_user_func('\Regional\DateTime'.ucFirst(JOORCHIN_CALENDAR).'::mktime', $data) - $date->getOffset();
	}
	/**
	* Generate an array of time zones and their local time&date.
	*
	* @param $blank
	*   If evaluates true, prepend an empty time zone option to the array.
	*/
	public static function time_zones($blank = NULL) {
		$zones = $blank ? array('' => __('- None selected -')) : array();
		$format = \Registry::getInstance('regional', SERVICE_MAIN)->formatLong;
		foreach (\DateTimeZone::listIdentifiers() as $zone)
			// Because many time zones exist in PHP only for backward compatibility
			// reasons and should not be used, the list is filtered by a regular
			// expression.
			if (preg_match('!^((Africa|America|Antarctica|Arctic|Asia|Atlantic|Australia|Europe|Indian|Pacific)/|UTC$)!', $zone))
				$zones[$zone] = __(':zone: :date', array('@zone' => str_replace('_', ' ', $zone), '@date' => format_date(GMT, $format.' O', $zone)));
		// Sort the translated time zones alphabetically.
		asort($zones);
		return $zones;
	}
}