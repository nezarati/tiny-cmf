<?
class Chart {
	const
		LINE = 'lc', SPARKLINE = 'ls', LINE_LXY = 'lxy',
		BAR_STACKED = 'bvs', BAR_STACKED_HORIZONTAL = 'bhs', BAR_STACKED_VERTICAL = 'bvs', BAR_GROUPED = 'bvg', BAR_GROUPED_HORIZONTAL = 'bhg', BAR_GROUPED_VERTICAL = 'bvg',
		PIE = 'p', PIE_3D = 'p3', PIE_CONCENTRIC = 'pc',
		VENN = 'v',
		SCATTER_PLOT = 's',
		RADAR = 'r', RADAR_SPLINES = 'rs',
		MAP = 't',
		GOOGLE_O_METERS = 'gom'
	;
	protected $property = array();
	public function __construct(array $option = NULL) {
		$option && $this->property += $option;
	}
	public function __invoke($type, array $option = NULL) {
		static $class = array(self::BAR_STACKED => 'ChartBarStacked', self::BAR_GROUPED => 'ChartBarGrouped', self::PIE => 'ChartPie');
		$extender_name = isset($class[$type]) ? $class[$type] : 'Charts';
		return new $extender_name($option+$this->property, $type);
	}
}
class ChartExtender implements ArrayAccess {
	const BASE = 'http://chart.apis.google.com/chart?';
	const MAX_WIDTH = 1000, MAX_HEIGHT = 1000, MAX_PIXEL = 300000;
	protected $property = array(), $query = array();
	
	# Implements Magic Methods
	public function __construct(array $option = NULL, $type = NULL) {
		$this->type = $type;
		$this->labelPosition = NULL; # (horizontal: b, t), (vertical: bv, tv, r, l)
		foreach ($option as $key => $value)
			$this->$key = $value;
		$this->fillSolid(self::FILL_BACKGROUND, 'FFFFFF00');
	}
	public function __toString() {
		$this->prepare();
		return sprintf('<div style="background: url(%s) no-repeat; width: %spx; height: %spx;" title="%s"></div>', self::BASE.http_build_query($this->query), $this->width, $this->height, $this->title);
	}
	public function __set($name, $value) {
		$this->property[$name] = $value;
	}
	public function __isset($name) {
		return isset($this->property[$name]);
	}
	public function __unset($name) {
		unset($this->property[$name]);
	}
	public function &__get($name) {
		return $this->property[$name];
	}
	
	# Implements ArrayAccess
	public function offsetSet($name, $value) {
		$this->query[$name] = $value;
	}
	public function offsetExists($name) {
		return isset($this->query[$name]);
	}
	public function offsetUnset($name) {
		unset($this->query[$name]);
	}
	public function offsetGet($name) {
		return $this->query[$name];
	}
	
	public function addDataSet($data) {
		$this->data[] = is_array($data) ? $data : func_get_args();
		return $this;
	}
	public function addLabel($label) {
		foreach (is_array($label) ? $label : func_get_args() as $label)
			$this->label[] = $label;
		return $this;
	}
	public function addColor($color) {
		foreach (is_array($color) ? $color : func_get_args() as $color)
			$this->color[] = $color;
		return $this;
	}
	
	const FILL_BACKGROUND = 'bg', FILL_CHART_AREA = 'c', FILL_TRANSPARENCY_CHART = 'a';
	public function fillSolid($type, $color) {
		$this->fill[] = array($type, 's', $color);
		return $this;
	}
	public function fillArea() {
		$fill = func_get_args();
		// Add remaining params
		for ($count = count($fill), $i = 0; $i<$count; ++$i)
			$fill[$i] = array('b', $fill[$i], $i, $i+1, 0);
		$this->chm = $fill;
		return $this;
	}
	
	# TODO: trim($query, '|')
	# Specify multiple axes within (x = bottom x-axis, t = top x-axis, y = left y-axis, r = right y-axis)
	public function axisType($type_n) {
		$this['chxt'] = implode(',', func_get_args());
		return $this;
	}
	# Specify labels with
	public function axisLabel($index, $label_n) {
		$this['chxl'] .= $index.':|'.implode('|', is_array($label_n) ? $label_n : array_slice(func_get_args(), 1)).'|';
		return $this;
	}
	# Specify label positions with
	public function axisLabelPosition($index, $label_n_Position) {
		$this['chxp'] .= implode(',', func_get_args()).'|';
		return $this;
	}
	# Specify a range for axis labels with
	public function axisRange($index, $start_of_range, $end_of_range, $interval = 1) {
		$this['chxr'] .= implode(',', func_get_args()).'|';
		return $this;
	}
	# Specify font size, color, and alignment for axis labels with
	/*
		[font size]: specifies the font size in pixels.,
		<alignment>:
			* -1 to make the axis labels left-aligned.
			* 0 to make the axis labels centered.
			* 1 to make the axis labels right-aligned.
		,
		<drawing control>:
			* l to draw axis lines only.
			* t to draw tick marks only.
			* lt to draw axis lines and tick marks.
		,
		<tick mark color>
	*/
	public function axisStyle($index, $axis_color, $font_size, $alignment, $drawing_control, $tick_mark_color) {
		$this['chxs'] .= implode(',', func_get_args()).'|';
		return $this;
	}
	
	protected function prepare() {
		$this->scaledData = self::getScaledArray($this->data, $this->scalar());
		$this->width = min(static::MAX_WIDTH, $this->width);
		$this->height = min(static::MAX_HEIGHT, $this->height);
		if ($this->width*$this->height > static::MAX_PIXEL)
			throw new Exception('Chart may contain at most '.static::MAX_PIXEL.' pixels.');
		$this->query += array(
			'cht'	=> $this->type,
			'chtt'	=> is_array($this->title) ? implode('|', $this->title) : $this->title,
			'chd'	=> 't:'.$this->encode($this->scaledData),
			'chdl'	=> implode('|', (array)$this->label),
			'chdlp'	=> $this->labelPosition,
			'chs'	=> sprintf('%sx%s', $this->width, $this->height),
			'chco'	=> implode(',', (array)$this->color),
			'chf'	=> $this->encode($this->fill),
			'chm'	=> $this->encode($this->chm),
		);
	}
	public function draw() {
		return $this->__toString();
	}
	
	protected static function encode(array $dataSet = NULL) {
		if (empty($dataSet))
			return;
		foreach ($dataSet as &$data)
			$data = implode(',', $data);
		return implode('|', $dataSet);
	}
	protected function scalar() {
		$max = max(100, self::getMaxOfArray($this->data));
		return $max<100 ? 1 : 100/$max;
	}
	
	// Utility methods
	protected static function addArrays($mixed) {
		$summedArray = array();
		foreach($mixed as $temp) {
			$a = 0;
			if (is_array($temp))
				foreach ($temp as $tempSubArray)
					$summedArray[$a++] += $tempSubArray;
			else
				$summedArray[$a] += $temp;
		}
		return $summedArray;
	}
	protected static function getScaledArray($unscaledArray, $scalar) {
		$scaledArray = array();
		foreach ($unscaledArray as $key => $value)
			$scaledArray[$key] = is_array($value) ? self::getScaledArray($value, $scalar) : round($value * $scalar, 2);
		return $scaledArray;
	}
	protected static function getMaxOfArray($ArrayToCheck) {
		$maxValue = 1;
		foreach ($ArrayToCheck as $temp)
			$maxValue = is_array($temp) ? max($maxValue, self::getMaxOfArray($temp)) : max($maxValue, $temp);
		return $maxValue;
	}
	protected static function getMaxCountOfArray($ArrayToCheck) {
		$maxValue = count($ArrayToCheck);
		foreach($ArrayToCheck as $temp)
			if (is_array($temp))
				$maxValue = max($maxValue, self::getMaxCountOfArray($temp));
		return $maxValue;
	}
}
class Charts extends ChartExtender {
}

abstract class ChartBar extends ChartExtender implements Countable {
	abstract public function setHorizontal($bool);
	public function __construct(array $option, $type) {
		parent::__construct($option+array('barWidth' => 23, 'barSpacerWidth' => 4, 'groupSpacerWidth' => 8), $type);
	}
	public function count() { # Total bars
		return count($this->data, COUNT_RECURSIVE);
	}
	protected function prepare() {
		/*
		$totalGroups = self::getMaxCountOfArray($this->data);
		$chartSize = $this->horizontal ? $this->height - 50 : $this->width - 50;
		$chartSize -= $totalGroups * $this->groupSpacerWidth;
		$this->barWidth = round($chartSize/count($this));
		$this['chbh'] = array($this->barWidth, $this->barSpacerWidth, $this->groupSpacerWidth);
		*/
		$this['chbh'] = 'a'; // Automatically resizing bars so that they fit within the chart size
		parent::prepare();
	}
}
class ChartBarStacked extends ChartBar {
	public function setHorizontal($bool) {
		$this->horizontal = $bool;
		$this->type = $bool ? Chart::BAR_STACKED_HORIZONTAL : Chart::BAR_STACKED_VERTICAL;
		return $this;
	}
	public function count() {
		return self::getMaxCountOfArray($this->data);
	}
	
	protected function scalar() {
		$max = max(100, self::getMaxOfArray(self::addArrays($this->data)));
		return $max<100 ? 1 : 100/$max;
	}
}
class ChartBarGrouped extends ChartBar {
	public function setHorizontal($bool) {
		$this->horizontal = $bool;
		$this->type = $bool ? Chart::BAR_GROUPED_HORIZONTAL : Chart::BAR_GROUPED_VERTICAL;
		return $this;
	}
}

class ChartPie extends ChartExtender {
	public function addDataSet($data) {
		foreach (is_array($data) ? $data : func_get_args() as $data)
			$this->data[] = $data;
		return $this;
	}
	public function set3D($bool) {
		$this->type = $bool ? Chart::PIE_3D : Chart::PIE;
		return $this;
	}
	protected function prepare() {
		if ($this->type == Chart::PIE)
			$this->width = $this->height * 1.5;
		else if ($this->type == Chart::PIE_3D)
			$this->width = $this->height * 2;
		$this->data = array($this->data);
		parent::prepare();
		$this['chl'] = $this['chdl'];
		unset($this['chdl']);
	}
	protected function scalar() {
		return 1;
	}
}