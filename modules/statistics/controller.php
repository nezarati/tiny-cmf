<?
namespace Statistics;
class Controller extends \Controller {
	protected $permission = array();
	protected function index() {
		$ga = new GoogleAnalytics;
		#$v .= print_r($ga->authenticateUser('username@gmail.com', 'passwd'));
		#$v .= print_r($ga->getProfiles());
		is_numeric($_REQUEST['offset']) || ($_REQUEST['offset'] = \TablePagination::START_INDEX);
		is_numeric($_REQUEST['count']) || ($_REQUEST['count'] = \TablePagination::PER_PAGE);
		$_REQUEST['sort'] = array('country' => '');
		$browsers = $data = array();
		foreach ($result = $ga->getReport('country,month', 'visits,pageviews', $_REQUEST['sort'], 'hostname =~ ^(www.)?(fa|en|)\.chonoo\.com', strToTime('2009-05-01')) as $row) {
			$data[$row->month][$row->country] = $row->visits;
			$browsers[$row->country] = 0;
		}
		$max = API::normalize($data, $browsers);
		multiSort($data);
		$v .= print_r($result, 1);
		$v .= print_r($data, 1);
		# $color = array('ff3344', '11ff11', '22aacc', '3333aa');
		$color = array('99C754', '54C7C5', '999999');
		$months = array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		$chart = new \Chart(
			array(
				'color' => $color,
				'title' => 'Browser'
			)
		);
		error_reporting(0);
		$bar = $chart(\Chart::BAR_GROUPED, array('width' => 500, 'height' => 200))->axisType('x', 'y')->axisLabel(0, array_keys($browsers))->axisRange(1, 0, $max);
		foreach ($data as $month => $browser)
			$bar->addDataSet($browser)->addLabel($months[(int)$month]);
		return new \TableSelect(NULL, array('country' => array('data' => 'country', 'field' => 'country', 'sort' => 'asc'), 'month' => array('data' => 'month', 'field' => 'month'), 'visits' => array('data' => 'visits', 'field' => 'visits'), 'pageviews' => array('data' => 'pageviews', 'field' => 'pageviews')), new Data($ga, $result)).$bar;
	}
}
