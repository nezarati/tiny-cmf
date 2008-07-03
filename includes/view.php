<?
function validate_match($value, $regex, $msg = NULL) {
	return preg_match($regex, $value) ? TRUE : $msg;
}
function validate_timestamp($d, $e = NULL, $y = 2000) {
	$e || ($e = __('Time is not valid.'));
	return is_numeric($d['year']) && $d['year']<2050 && is_numeric($d['month']) && 0<$d['month'] && $d['month']<13 && is_numeric($d['day']) && 0<$d['day'] && $d['day']<32 && is_numeric($d['hour']) && 0<=$d['hour'] && $d['hour']<24 && is_numeric($d['minute']) && 0<=$d['minute'] && $d['minute']<60 && is_numeric($d['second']) && 0<=$d['second'] && $d['second']<60 ? 1 : $e;
}
function validate_options($value, $options) {
	return count(array_diff((array)$value, $options)) ? __('An illegal choice has been detected. Please contact the site administrator.') : TRUE;
}
function validate_homepage($value) {
	if (!(trim($value) == '' || $value == 'http://') && (($url = parse_url($value)) === FALSE || empty($url['scheme']) || empty($url['host'])))
		return __(':name field is not valid.', array('!name' => __('Website')));
	return TRUE;
}
function isURL($value) {
	return filter_var($value, FILTER_VALIDATE_URL) !== FALSE;
}
const REGEX_URL = '#^https?\://([A-z\-0-9]+\.)+[a-z]{2,4}/?$#';
const REGEX_NAME = '/^.{3,64}$/u';
const REGEX_TITLE = '/^.{3,150}$/u';
const REGEX_CONTENT = '/.{3,51200}/u';
const REGEX_INTEGER = '/^-?[0-9]+$/';
const REGEX_FLOAT = '/^-?([0-9]*[\.|,]?[0-9]+)$/';
class View {
	public $pageTitle = array(), $title, $description, $content, $header = array('X-Powered-By' => 'Chonoo Framework/4.0.0'), $script;
	protected static $message = array('error' => array(), 'status' => array(), 'warning' => array());
	
	public static function status($entry, $result = NULL) {
		self::$message['status'][] = $entry;
	}
	public static function error($msg, $code = NULL) {
		self::$message['errors'][] = $msg;
	}
	public static function warning($msg) {
		self::$message['warnings'][] = $msg;
	}
	public static function operations($button) {
		$out = array();
		$type = array('edit' => array('text' => __('Edit'), 'title' => __('Edit this item'), 'rel' => 'ajax'), 'delete' => array('onclick' => '$(this).confirmDelete(this.parentNode.parentNode)', 'class' => 'delete-item', 'text' => __('Delete'), 'rel' => 'delete'));
		foreach ($button as $data) {
			isset($data['type']) && $data += $type[$data['type']];
			$tag = 'a';
			if (@$data['disabled']) {
				$data['class'] .= ' disabled';
				$data['href'] = $data['onclick'] = '';
				//$tag = 'span';
			}
			$out[] = '<'.$tag.' href="'.($data['href'] ?: 'javascript:void(0)').'" onclick="'.(isset($data['onclick']) ? 'return '.$data['onclick'] : '').'" title="'.$data['title'].'" class="'.$data['class'].'" rel="'.$data['rel'].'">'.$data['text'].'</'.$tag.'>';
		}
		return implode(' | ', $out);
	}
	public static function script($script) {
		return '<script type="text/javascript">'.$script.'</script>';
	}
	public static function jQuery($src = NULL) {
		return '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>'.($src ? self::script($src) : '');
	}
	public static function uploadFile($src) {
		return '<link href="/extends/fileuploader.css" rel="stylesheet" type="text/css" /><script type="text/javascript" src="/extends/fileuploader.js"></script>'.($src ? self::script($src) : '');
	}
	public static function editor($src) {
		return '<link href="/extends/cleditor/jquery.cleditor.css" rel="stylesheet" type="text/css" /><script type="text/javascript" src="/extends/cleditor/jquery.cleditor.min.js"></script><script type="text/javascript" src="/extends/cleditor/jquery.cleditor.xhtml.min.js"></script>'.($src ? self::script($src) : '');
	}
	
	protected function template($tpl) {
		$tpl->mapModifier('date',
			function() {
				return call_user_func_array('format_date', func_get_args());
			}
		);
		$tpl->mapModifier('_',
			function($msgstr, $arg) {
				return __($msgstr, $arg, 'layout');
			}
		);
		
		$rgt = \Registry::getInstance();
		foreach (
			array(
				'title' => $rgt->title,
				'description' => $rgt->slogan,
				'shortTitle' => $rgt->name,
				'url' => $rgt->home,
				'pageTitle' => implode(' | ', $this->pageTitle),
			)
			as $key => $value
		)
			$tpl['document.'.$key] = $value;
		$tpl['preferences.regional.direction'] = JOORCHIN_LANGUAGE == 'fa' ? 'rtl' : 'ltr';
		return $tpl;
	}
	
	public function render() {
		foreach ($this->header as $k => $v)
			header((is_numeric($k) ? '' : $k.': ').$v);
		$content = $this->content instanceof Form || $this->content instanceof TableSelect ? $this->content->render() : $this->content;
		if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			header('Content-Type: application/json; charset=UTF-8');
			return json_encode(array('message' => self::$message, 'script' => $this->script, 'results' => $content instanceof Template ? (string)$this->template($content) : $content, 'pageTitle' => implode(' | ', $this->pageTitle)));
		}
		
		$tpl = new Template(\Model\Layout::loadById()->main);
		$tpl->content = $content;
		$tpl->head = function() {
			return implode(Hook::invoke('head'));
		};
		$tpl->sidebar = array('widgets' => \Menu\API::widget());
		$this->template($tpl);
		
		return '<!-- Powered By: WWW.Chonoo.Com -->'."\n".$tpl;
	}
	public function __toString() {
		header('HTTP/1.1 404 Not Found');
		ob_start();
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<title>404 Not Found</title>
	</head>
	<body style="font-family: arial,sans-serif; color: #000000; background-color: #ffffff;">
		<table border="0" cellpadding="2" cellspacing="0" width="100%">
			<tr>
				<td rowspan="3" width="1%" nowrap style="font: 10mm imes; font-weight: bold">
					<span style="color: #0039b6">C</span>
					<span style="color: #c41200">h</span>
					<span style="color: #f3c518">o</span>
					<span style="color: #0039b6">n</span>
					<span style="color: #30a72f">o</span>
					<span style="color: #c41200">o</span>&nbsp;&nbsp;
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="background-color: #3366cc; color: #ffffff; font-weight: bold">Error</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
		</table>
		<blockquote>
			<H1>Not Found</H1>
			<p>The requested URL was not found on this server. There are so many reasons that this might have happened we can scarcely bring ourselves to type them all out. You might have typed the URL incorrectly, for instance. Or (less likely but certainly plausible) we might have coded the URL incorrectly. Or (far less plausible, but theoretically possible, depending on which ill-defined Grand Unifying Theory of physics one subscribes to), some random fluctuation in the space-time continuum might have produced a shatteringly brief but nonetheless real electromagnetic discombobulation which caused this error page to appear.</p>
		</blockquote>
		<div style="background-color: #3366cc; width: 100%; height: 5"></div>
	</body>
</html>
<?
		return ob_get_clean();
	}
}