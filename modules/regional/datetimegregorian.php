<?
namespace Regional;
class DateTimeGregorian extends \DateTime {
	public static function dateForm($name, $time) {
		list($d, $n, $y) = explode('-', date('j-n-Y', $time));
		$M = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		for ($r='<select name="'.$name.'[month]" size="1">', $i=1; $i<=12; $r.='<option value="'.$i.'"'.($n == $i ? ' selected="selected"' : '').'>'.$M[$i].'</option>', $i++);
		$r .= '</select> ';
		for ($r.='<select name="'.$name.'[day]" size="1">', $i=1; $i<=31; $r.='<option value="'.$i.'"'.($d == $i ? ' selected="selected"' : '').'>'.$i++.'</option>');
		$r .= '</select>, ';
		$r .= '<input name="'.$name.'[year]" maxlength="4" value="'.$y.'" dir="ltr" class="tiny" />';
		return $r;
	}
	public static function mktime($data) {
		return mktime($data->hour, $data->minute, $data->second, $data->month, $data->day, $data->year);
	}
}