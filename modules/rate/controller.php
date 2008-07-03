<?
namespace Rate;
class Controller extends \Controller {
	const MAX_RATINGS = 5;
	
	public function __construct() {
		\Service::exists('rate', $_REQUEST['arg']['service']) && define('SERVICE_RATE', \Service::identifier('rate', $_REQUEST['arg']['service']));
		parent::__construct();
	}
	
	protected $permission = array('vote' => 'post ratings', 'archive' => 'administer ratings');
	protected function vote($A) {
		is_numeric($A->id) && defined('SERVICE_RATE') && abs($A->score) <= self::MAX_RATINGS || die;
		$A->node = $A->id;
		
		$alreadyVoted = isset($_COOKIE['rate'][$A->service][$A->node]);
		if (!$alreadyVoted) {
			#\DB::query('LOCK TABLE {rate} WRITE', NULL, array('return' => \DB::RETURN_NULL));
			if (!$alreadyVoted = \Model\Rate::all()->filter('service', SERVICE_RATE)->filter('node', $A->node)->filter(USER_ID ? 'user' : 'hostname', USER_ID ?: IP)->limit(1)->count())
				\Model\Rate::save(array('service' => SERVICE_RATE, 'node' => (int)$A->node, 'user' => USER_ID, 'score' => (int)$A->score, 'hostname' => IP, 'created' => GMT));
			#\DB::query('UNLOCK TABLES', NULL, array('return' => \DB::RETURN_NULL));
		}
		$alreadyVoted ? \View::error(__('You already voted.')) : \View::status(__('Your vote was recorded. Thank you.'));
		return array('points' => \Model\Rate::points($A->node, SERVICE_RATE));
	}
	protected function archive($A) {
		$A->node = (int) $A->id;
		$services = \Service::required('rate');
		$query = \Model\Rate::all();
		if ($A->node)
			$query->filter('node', $A->node);
		defined('SERVICE_RATE') ? $query->filter('service', SERVICE_RATE) : $query->filter('service', array_keys(\Service::required('rate')), 'in');
		return new \TableSelect(NULL, array('module' => array('data' => __('Type'), 'field' => 'service'), 'username' => array('data' => __('Username'), 'field' => 'user'), 'score' => array('data' => __('Rating'), 'field' => 'score'), 'node' => array('data' => __('Node'), 'field' => 'node'), 'created' => array('data' => __('Date / Time'), 'field' => 'created'), 'hostname' => array('data' => __('IP / Host'), 'field' => 'hostname')), $query->map(function($model) use($services) {
			$service = $services[$model->service];
			
			$model->module = __($service->title);
			$model->created = format_date($model->created, 'short');
			$model->username = \User\API::load($model->user)->name;

		}), __('No Ratings Logs Found'));
	}
}