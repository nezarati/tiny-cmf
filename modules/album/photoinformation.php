<?
namespace Album;
final class PhotoInformation {
	private $exif;
	public function __construct($filename) {
		$this->exif = exif_read_data($filename, 0, TRUE);

		$this->created = strtotime($this->exif['EXIF']['DateTimeDigitized'] ?: $this->exif['FILE']['FileDateTime']) ?: (int)$this->exif['FILE']['FileDateTime'];
		$this->width = $this->exif['COMPUTED']['Width'];
		$this->height = $this->exif['COMPUTED']['Height'];
		$this->fileName = htmlspecialchars($this->exif['FILE']['FileName']);
		$this->type = $this->exif['FILE']['FileType'];
		$this->mimeType = $this->exif['FILE']['MimeType'];
		$this->size = $this->exif['FILE']['FileSize'];
		$this->camera = htmlspecialchars($this->exif['IFD0']['Make']);
		$this->model = htmlspecialchars($this->exif['IFD0']['Model']);
		$this->iso = self::divide($this->exif['EXIF']['ISOSpeedRatings'], TRUE);
		$this->exposure = self::divide($this->exif['EXIF']['ExposureTime'], TRUE);
		$this->aperture = self::divide($this->exif['EXIF']['FNumber'], TRUE);
		$this->focalLength = self::divide($this->exif['EXIF']['FocalLength'], TRUE);
		$this->flash = (int)$this->exif['EXIF']['Flash'];
		$this->GPS();
	}
	
	private function GPS() {
		if (empty($this->exif['GPS']))
			return FALSE;
		$this->latitude = $this->toDecimal($this->exif['GPS']['GPSLatitude'][0], $this->exif['GPS']['GPSLatitude'][1], $this->exif['GPS']['GPSLatitude'][2], $this->exif['GPS']['GPSLatitudeRef']);
		$this->longitude = $this->toDecimal($this->exif['GPS']['GPSLongitude'][0], $this->exif['GPS']['GPSLongitude'][1], $this->exif['GPS']['GPSLongitude'][2], $this->exif['GPS']['GPSLongitudeRef']);
	}
	private function toDecimal($deg, $min, $sec, $hemi) {
		$deg = self::divide($deg);
		$min = self::divide($min);
		$sec = self::divide($sec);
		
		$d = $deg + $min/60 + $sec/3600;
		return $hemi == 'S' || $hemi == 'W' ? $d*-1 : $d;
	}
	private static function divide($value, $just10 = FALSE) {
		list($a, $b) = explode('/', $value);
		return $b ? ($just10 ? (preg_match('/^1[0]*$/', $b) ? $a / $b : (int)$a.'/'.(int)$b) : $a / $b) : (int) $value;
	}
	
	// flashvalue function taken from Exif reader 1.2 by Richard James Kendall, richard@richardjameskendall.com
	// takes the USHORT of the flash value, splits it up into itc component bits and returns the string it represents
	public static function flash($bin) {
		$retval = array();
		$bin = str_pad(decbin($bin), 8, '0', STR_PAD_LEFT);

		$flashfired = substr($bin, 7, 1);
		if ($flashfired == '1')
			$retval[] = 'Fired';
		else if ($flashfired == '0')
			$retval[] = 'Did not fire';

		$returnd = substr($bin, 5, 2);
		if ($returnd == '10')
			$retval[] = 'Strobe return light not detected';
		else if ($returnd == '11')
			$retval[] = 'Strobe return light detected';

		$flashmode = substr($bin, 3, 2);
		if ($flashmode == '01' || $flashmode == '10')
			$retval[] = 'Compulsory mode';
		else if ($flashmode == '11')
			$retval[] = 'Auto mode';

		$redeye = substr($bin, 1, 1);
		if ($redeye)
			$retval[] = 'Red eye reduction';
		else
			$retval[] = 'No red eye reduction';
		
		return $retval;
	}
}