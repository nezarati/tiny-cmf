<?
namespace Taxonomy;
class Hook {
	public function __construct() {
		\Hook::add('permission', '\Taxonomy\Hook::permission');
		\Hook::add('menu', '\Taxonomy\Hook::menu');
		\Hook::add('script', '\Taxonomy\Hook::script');
		\Hook::add('install', '\Taxonomy\API::install');
		
		foreach (\Service::required('taxonomy') as $module) {
			\Hook::add($module->name.'_presave', '\Taxonomy\Hook::postPresave');
			\Hook::add($module->name.'_delete', '\Taxonomy\Hook::postDelete');
			\Hook::add($module->name.'_form', '\Taxonomy\Hook::postForm');
		}
	}
	
	public static function permission() {
		$permissions = array(
			'administer taxonomy' => array(
				'title' =>__('Administer vocabularies and terms')
			)
		);
		foreach (\Service::required('taxonomy') as $module)
			$permissions += array(
				'edit terms in '.$module->id => array(
					'title' => __('Edit terms in :vocabulary', array('%vocabulary' => __($module->title))),
				),
				'delete terms in '.$module->id => array(
					'title' => __('Delete terms from :vocabulary', array('%vocabulary' => __($module->title))),
				),
			);
		return $permissions;
	}
	public static function menu() {
		$menu['taxonomy/archive'] = array(
			'title' => __('Taxonomy'),
			'description' => __('Manage tagging, categorization, and classification of your content.'),
			'access arguments' => 'administer taxonomy',
			'parent' => 'structure',
		);
		foreach (\Service::required('taxonomy') as $module)
			$menu['taxonomy/0/'.$module->name.'/edit'] = array(
				'title' => __('Add terms into :name', array('@name' => __($module->title))),
				'access arguments' => 'administer taxonomy',
				'parent' => 'taxonomy/archive',
			);
		return $menu;
	}
	public static function script() {
		return __DIR__.'/script.js';
	}
	
	public static function postPresave($data) {
		$serviceTag = \Service::identifier('taxonomy', $_REQUEST['arg']['service']);
		$servicePost = \Service::identifier('post', $_REQUEST['arg']['service']);
		
		$tags_new = (array)$data->tags;
		$tags_old = $data->id ? \Model\Post::loadById($data->id, $servicePost)->tags : array();
		foreach (array_diff_key($tags_old, $tags_new) as $id)
			\Taxonomy\API::count($serviceTag, 0, $id);
		foreach (array_diff_key($tags_new, $tags_old) as $id)
			\Taxonomy\API::count($serviceTag, $id);
		$data->tags = array_keys($tags_new);
	}
	public static function postDelete($service, $id) {
		$serviceTag = \Service::identifier('taxonomy', $_REQUEST['arg']['service']);
		$servicePost = \Service::identifier('post', $_REQUEST['arg']['service']);
		
		foreach (\Model\Post::loadById($id, $servicePost)->tags as $id)
			\Taxonomy\API::count($serviceTag, 0, $id);
	}
	public static function postForm($form, $data) {
		$serviceTag = \Service::identifier('taxonomy', $_REQUEST['arg']['service']);
		$servicePost = \Service::identifier('post', $_REQUEST['arg']['service']);
		
		$form->tags->attr(array('legend' => __('Tags'), 'type' => 'fieldset'));
		$form->tags->list->attr(array('name' => 'data[tags]', 'label' => __('Separate tags with commas'), 'value' => isset($data->tags) ? $data->tags : array(), 'type' => 'taxonomy', 'service' => $serviceTag, 'render' => 'checkbox'));
	}
}