<?
namespace Forum;
class Hook {
	public function __construct() {
		\Hook::add('forum_presave', '\Forum\Hook::postPresave');
		\Hook::add('forum_delete', '\Forum\Hook::postDelete');
		\Hook::add('forum_form', '\Forum\Hook::postForm');
		\Hook::add('schemaPost', '\Forum\Hook::schemaPost');
		\Hook::add('install', '\Forum\Hook::install');
	}
	
	public static function postPresave($data) {
		$data->content = \Post\API::tidy($data->content);
		
		if ($data->id) {
			if (($taxonomy = \Model\Post::loadById($data->id, SERVICE_POST_FORUM)->taxonomy) != $data->taxonomy)
				\Taxonomy\API::count(SERVICE_TAXONOMY_FORUM, $data->taxonomy, $taxonomy);
		} else
			\Taxonomy\API::count(SERVICE_TAXONOMY_FORUM, $data->taxonomy);
		
		$data->updated = GMT;
		$data->comment = array('count' => 0, 'status' => 1);
	}
	public static function postDelete($id) {
		\Taxonomy\API::count(SERVICE_TAXONOMY_FORUM, 0, \Model\Post::loadById($id, SERVICE_POST_FORUM)->taxonomy);
	}
	public static function postForm($form, $data) {
		$form->data->taxonomy->attr(array('label' => __('Taxonomy'), 'value' => is_numeric($_GET['arg']['taxonomy']) ? $_GET['arg']['taxonomy'] : $data->taxonomy, 'type' => 'taxonomy', 'service' => SERVICE_TAXONOMY_FORUM));
		$form->data->options['options'] += array('sticky' => __('Sticky at top of lists'));
	}
	
	public static function commentPresave($doc) {
		
	}
	public static function schemaPost(&$schema) {
		$schema['fields']['hits'] = array('type' => 'integer', 'not null' => TRUE);
		$schema['indexes']['taxonomyLastComment'] = array('service', 'taxonomy', 'status', 'updated'); # TODO: updated: -1; ForumLastReplay
	}

	public static function install() {
		\Service::install('post', 'forum');
		\Service::install('comment', 'forum');
		\Service::install('taxonomy', 'forum');
	}
}