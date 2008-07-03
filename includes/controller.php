<?
const ACCESS_DENIED = 'Access denied'; # TODO
abstract class Controller { # 8/7/19 12:12 - 09/01/29 19:04:30 ->->->
	protected $callback = FALSE, $view;
	public function __construct() {
		$this->view = new View;
		$method = $_REQUEST['action'].'Action';
		$this->$method(isset($_REQUEST['arg']) ? (object)$_REQUEST['arg'] : new StdClass, isset($_POST['data']) ? (object)$_POST['data'] : new StdClass);
	}
	public function __call($method, $arg) {
		$method = preg_replace('/Action$/', '', $method);
		$this->view->header['Expires'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
		$this->view->header['Last-Modified'] = gmdate('D, d M Y H:i:s').' GMT';
		$this->view->header['Cache-Control'] = array('no-cache, must-revalidate', 'post-check=0, pre-check=0');
		$this->view->header['Pragma'] = 'no-cache';
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$valid = TRUE;
			# DB::insert('attempt')->fields(array('hostname' => IP, 'page' => ROUTE, 'created' => GMT))->execute(); # TODO: Attempt
			if (is_array($validator = \Model\Form::all()->fields('data')->filter('token', $_POST['token'])->fetchField())) {
				foreach ($validator as $field => $data) {
					$input = $_POST;
					foreach (explode('-', $field) as $parent)
						if (is_array($input) && array_key_exists($parent, $input))
							$input = $input[$parent];
						else {
							$input = NULL;
							break;
						}
					array_unshift($data['arguments'], $input);
					if (($msg = call_user_func_array($data['callback'], $data['arguments'])) !== TRUE) {
						View::error($msg);
						$valid = FALSE;
					}
				}
			} else
				$valid = FALSE;
			if ($valid)
				$this->callback = TRUE;
			else if (!empty($_POST))
				return;
		}
		$this->view->pageTitle[] = Registry::getInstance()->title;
		if (method_exists($this, $method) && \User\API::access($this->permission[$method]) && ($this->view->content = call_user_func_array(array($this, $method), $arg)) !== ACCESS_DENIED) {
			if ($this->view->content === TRUE && $this->callback)
				\Model\Form::all()->filter('token', $_POST['token'])->delete();
			if ($this->view->content === FALSE || $this->view->content === TRUE)
				$this->view->content = NULL;
		} else {
			$this->view->pageTitle = array(__('Access denied'));
			$this->view->content = __('You are not authorized to access this page.');
		}
	}
	public function __toString() {
		return $this->view->render();
	}
}