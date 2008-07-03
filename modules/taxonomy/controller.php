<?
namespace Taxonomy;
class Controller extends \Controller {
	public function __construct() {
		\Service::exists('taxonomy', $_REQUEST['arg']['service']) && define('SERVICE_TAXONOMY', \Service::identifier('taxonomy', $_REQUEST['arg']['service']));
		parent::__construct();
		if (defined('SERVICE_TAXONOMY'))
			$this->view->pageTitle[] = __(\Service::load(\Service::id($_REQUEST['arg']['service']))->title);
		$this->view->pageTitle[] = __('Taxonomy');
	}
	protected $permission = array('archive' => 'administer taxonomy', 'edit' => 'administer taxonomy', 'delete' => 'administer taxonomy', 'index' => 1);
	private static function form($data = NULL) {
		$form = new \Form;
		$form->data['type'] = 'fieldset';
		$form->data->term->attr(array('label' => __('Name'), 'value' => $data->term, 'validator' => array('validate_match', REGEX_NAME, __(':name field is required.', array('!name' => __('Name'))))));
		$form->data->description->attr(array('type' => 'textarea', 'label' => __('Description'), 'value' => $data->description));
		$form->data->parent->attr(array('type' => 'taxonomy', 'service' => SERVICE_TAXONOMY, 'label' => __('Parent term'), 'ignore' => $data->id, 'blank' => TRUE, 'value' => $data->parent));
		$form->data->weight->attr(array('type' => 'weight', 'label' => __('Weight'), 'value' => $data->weight, 'tip' => __('Terms are displayed in ascending order by weight.')));
		$form->button->submit['value'] = __('Save');
		\Hook::call($_REQUEST['arg']['service'].'_taxonomy_form', $form, $data);
		return $form;
	}
	public function archive() {
		$services = \Service::required('taxonomy');
		return new \TableSelect(NULL, array('module' => array('data' => __('Type'), 'field' => 'service', 'sort' => 'asc'), 'term' => array('data' => __('Term'), 'field' => 'term', 'primary' => TRUE), 'parent' => array('data' => __('Parent'), 'field' => '_parent')), \Model\Taxonomy::all()->fields('service', 'id', 'term', 'parent', '__parent')->filter('service', array_keys($services), 'in')->map(function($doc) use($services) {
			$doc->parent = API::load($doc->parent, $doc->service)->term ?: __('- None -');
			$doc->module = __($services[$doc->service]->title);
			$doc->actions['edit'] = array('type' => 'edit', 'href' => '/taxonomy/'.$doc->id.'/'.$services[$doc->service]->name.'/edit');
			$doc->actions['delete'] = array('type' => 'delete', 'href' => '/taxonomy/'.$doc->id.'/'.$services[$doc->service]->name.'/delete');
		}), __('No terms available.'));
	}
	public function edit($A, $D) {
		is_numeric($A->id) && defined('SERVICE_TAXONOMY') || die;
		if ($this->callback) {
			$D->id = $A->id;
			\Hook::call($A->service.'_taxonomy_presave', $D);
			return (bool) API::save($D);
		}
		return $this->form($A->id ? API::load($A->id) : new \StdClass);
	}
	public function delete($A) {
		is_numeric($A->id) && defined('SERVICE_TAXONOMY') || die;
		return API::delete($A->id);
	}
	public function index($A) {
		defined('SERVICE_TAXONOMY') || die;
		$this->view->pageTitle[] = API::load($A->id, SERVICE_TAXONOMY)->term;
		$li = '';
		foreach (\Model\Taxonomy::all()->filter('service', SERVICE_TAXONOMY)->filter('parent', $A->id)->sort('weight') as $row)
			$li .= '<li><a href="/'.$A->service.'/taxonomy/'.$row->id.'/index" rel="ajax"><span onclick="$.Taxonomy.parent(this, \''.$A->service.'\', '.$row->id.')">→</span>'.$row->term.'</a></li>';
		return '<ul>'.$li.'</ul>';
	}
}