<?
namespace Regional;
class DateTimeJalali extends \DateTime { # 2009-02-16 17:52
	protected static 
		$weekDays = array('یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'),
		$months = array(1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند');

	public static function leapYear($year) {
		static $remaining = array(1 => 1, 5 => 1, 9 => 1, 13 => 1, 17 => 1, 22 => 1, 26 => 1, 30 => 1);
		return $remaining[$year%33];
	}

	public function format($format) {
		list($week, $hour, $minute, $second) = explode(',', parent::format('w,H,i,s'));
		list($month, $day, $year) = static::JD2Persian($jd = unixToJD($this->getTimestamp() + $this->getOffset()));
		for ($i = 0, $length = strLen($format); $i<$length; $i++)
			switch ($format[$i]) {
				# Day
				case 'd':
					$result .= str_pad($day, 2, 0, STR_PAD_LEFT);
				break;
				case 'D':
					$result .= iconv_subStr(static::$weekDays[$week], 0, 1);
				break;
				case 'j':
					$result .= $day;
				break;
				case 'l':
					$result .= static::$weekDays[$week];
				break;
				case 'N':
					$result .= $week + 1;
				break;
				case 'S':
					$result .= 'ام';
				break;
				case 'w':
					$result .= $week;
				break;
				case 'z':
					$result .= $jd - static::Persian2JD($year, 1, 1) + 1;
				break;
				
				# Week
				case 'W': # TODO: week number of year, weeks starting on Monday
				break;
				
				# Month
				case 'F':
					$result .= static::$months[$month];
				break;
				case 'm':
					$result .= str_pad($month, 2, 0, STR_PAD_LEFT);
				break;
				case 'M': # TODO: abbreviation
					$result .= iconv_subStr(static::$months[$month], 0, 3);
				break;
				case 'n':
					$result .= $month;
				break;
				case 't':
					$result .= $month <= 6 ? 31 : ($month <= 11 || static::leapYear($year) ? 30 : 29);
				break;
				
				# Year
				case 'L':
					$result .= (int)static::leapYear($year);
				break;
				case 'o': # TODO: 
				break;
				case 'Y':
					$result .= $year;
				break;
				case 'y':
					$result .= $year%100;
				break;
				
				# Time
				case 'a':
					$result .= $hour<11 ? 'ق.ظ' : 'ب.ظ';
				break;
				case 'A':
					$result .= $hour<11 ? 'قبل‌ازظهر' : 'بعدازظهر';
					break;
				case 'B': # TODO: Swatch Internet time
				break;
				case 'g':
					$result .= $hour>11 ? $hour-12 : ($hour>9 ? $hour : $hour%10);
				break;
				case 'G':
					$result .= $hour>9 ? $hour : $hour%10;
				break;
				case 'h':
					$result .= $hour>11 ? $hour-12 : $hour;
				break;
				case 'H':
					$result .= $hour;
				break;
				case 'i':
					$result .= $minute;
				break;
				case 's':
					$result .= $second;
				break;
				
				case 'u':
				
				# Timezone
				case 'e':
				case 'I':
				case 'O':
				case 'P':
				case 'T':
				case 'Z':
				
				# Full Date/Time
				case 'c':
				case 'r':
				case 'U':
					$result .= parent::format($format[$i]);
				break;
				
				case '\\':
					$result .= $format[++$i];
					break;
				default:
					$result .= $format[$i];
			}
		return $result;
	}
	
	# http://www.fourmilab.ch/documents/calendar/
	const PERSIAN_EPOCH = 1948320.5;
	public static function Persian2JD($year, $month, $day) {
		$epbase = $year - (($year >= 0) ? 474 : 473);
		$epyear = 474 + $epbase%2820;
		return $day + (($month <= 7) ? (($month - 1) * 31) : ((($month - 1) * 30) + 6)) + (int)((($epyear * 682) - 110) / 2816) + ($epyear - 1) * 365 + (int)($epbase / 2820) * 1029983 + (static::PERSIAN_EPOCH - 1);
	}
	public static function JD2Persian($jd) { # Calculate Persian date from Julian day
		$jd = (int)$jd + .5;

		$depoch = $jd - static::Persian2JD(475, 1, 1);
		$cycle = (int)($depoch / 1029983);
		$cyear = $depoch%1029983;
		if ($cyear == 1029982)
			$ycycle = 2820;
		else {
			$aux1 = (int)($cyear / 366);
			$aux2 = $cyear%366;
			$ycycle = (int)(((2134 * $aux1) + (2816 * $aux2) + 2815) / 1028522) + $aux1 + 1;
		}
		$year = $ycycle + (2820 * $cycle) + 474;
		if ($year <= 0)
			$year--;
		$yday = ($jd - static::Persian2JD($year, 1, 1)) + 1;
		$month = $yday <= 186 ? ceil($yday / 31) : ceil(($yday - 6) / 30);
		$day = (int)($jd - static::Persian2JD($year, $month, 1)) + 1;
		return array($month, $day, $year);
	}
	
	public static function dateForm($name, $time) {
		list($m, $d, $y) = static::JD2Persian(unixToJD($time));
		$r = '<input name="'.$name.'[year]" maxlength="4" value="'.$y.'" dir="ltr" class="tiny" />, ';
		for ($r.='<select name="'.$name.'[month]" size="1">', $i=1; $i<=12; $r.='<option value="'.$i.'"'.($m == $i ? ' selected="selected"' : '').'>'.self::$months[$i].'</option>', $i++);
		$r .= '</select> ';
		for ($r.='<select name="'.$name.'[day]" size="1">', $i=1; $i<=31; $r.='<option value="'.$i.'"'.($d == $i ? ' selected="selected"' : '').'>'.$i++.'</option>');
		$r .= '</select>';
		return $r;
	}
	public static function mktime($data) {
		return strToTime(JDToGregorian(static::Persian2JD($data->year, $data->month, $data->day))) + $data->hour*60*60 + $data->minute*60 + $data->second;
	}
	public static function calendar($A) { # TODO
		isset($A) && ($y = (int)$A->year) && ($m = (int)$A->month);
		for ($y||($y=$JAL->date('Y')), $m||($m=$JAL->date('m')), $p=strtotime($JAL->gregorian($y-($m-1?0:1), $m-1?$m-1:12)), $n=strtotime($JAL->gregorian($y+($m>11?1:0), $m>11?1:$m+1, 2)), $q=DB::_query('select title, `create` from #__post where bind=%d && `create` between %d and %d;', Bind, $p, $n); $d=$q->fetch_object(); $e[$JAL->date('m-d', $d->create)][].=$d->title);
		foreach (array('ش', 'ي', 'د', 'س', 'چ', 'پ', 'ج') as $d)
		$r .= '<th class="weekday">'.$d.'</th>';
		for ($s=strtotime($JAL->gregorian($y, $m, 2)), $d=1, $w=($v=$JAL->date('w', $s))?$v-1:0, $t=$JAL->date('d'), $c=$JAL->date('t', $s)+1, $r='<table dir="rtl" id="calendar"><caption>'.$JAL->date('F Y', $s).'</caption><thead><tr>'.$r.'</tr></thead><tr><tfoot><tr><td colspan="3" id="prev"><a href="calendar-'.$JAL->date('Y-m', $p).'.html" title="نمايش مطالب براي '.$JAL->date('F Y', $p).'" onclick="return _$(this.href, \'calendar-layer\')">« '.$JAL->date('F', $p).'</a></td><td class="pad">&nbsp;</td><td colspan="3" id="next"><a href="calendar-'.$JAL->date('Y-m', $n).'.html" title="نمايش مطالب براي '.$JAL->date('F Y', $n).'" onclick="return _$(this.href, \'calendar-layer\')">'.$JAL->date('F', $n).' »</a></td></tr></tfoot><tbody>'.($w>0 ? '<td colspan="'.$w.'">&nbsp;' : ''); $c>$d; $w!=7 || ($r.='<tr>') && ($w=0), $r.=(isset($e[$m.'-'.$d]) ? '<td class="event"><a href="archive-post-'.$y.'-'.$m.'-'.$d.'.html" title="'.implode(', ', (array)$e[$m.'-'.$d]).'" onclick="return _$(this)">'.$d.'</a>' : '<td class="'.($d == $t && $m == $JAL->date('m') && $y == $JAL->date('Y') ? 'today' : ($w == 6 ? 'endday' : 'day')).'">'.$d).'</td>', $d++, $w++);
		return '<div id="calendar-layer">'.$r.($w != 7 ? '<td colspan="'.(7-$w).'">&nbsp;' : '').'</tbody></table></div>';
	}
	
	public static function convert($content) {
		return preg_replace_callback('/(?:&#\d{2,4};)|(\d+[\.\d]*)|<\s*[^>]+>/', function($matches) {
			return isset($matches[1]) ? preg_replace('/(\d)/e', 'pack("C*", 0xDB, 0xB0+$1)', $matches[1]) : $matches[0];
		}, $content);
	}
	public static function normalize($value) {
		return is_array($value) ? array_map(array(__CLASS__, 'normalize'), $value) : str_replace(array('ي', 'ك', '٤', '٥', '٦'), array('ی', 'ک', '۴', '۵', '۶'), $value);
	}
}