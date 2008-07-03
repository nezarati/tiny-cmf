<?
namespace Album;
class Hook {
	public function __construct() {
		\Hook::add('permission', '\Album\Hook::permission');
		\Hook::add('menu', '\Album\Hook::menu');
		\Hook::add('install', '\Album\Hook::install');
		\Hook::add('album_taxonomy_form', '\Album\Hook::formTaxonomy');
		\Hook::add('album_taxonomy_presave', '\Album\Hook::presaveTaxonomy');
		\Hook::add('schemaTaxonomy', '\Album\Hook::schemaTaxonomy');
		\Hook::add('style', '\Album\Hook::style');
		\Hook::add('script', '\Album\Hook::script');
	}
	
	public static function install() {
		\Service::install('album');
		\Service::install('taxonomy', 'album');
		\Service::install('storage', 'album');
		\Service::install('comment', 'album');
		\Service::install('rate', 'album');
	}
	
	public static function formTaxonomy($form, $data) {
		$form->data->published->attr(array('label' => __('Date'), 'value' => $data->published, 'type' => 'datetime'));
		$form->data->location->attr(array('label' => __('Place Taken'), 'value' => $data->location));
		$form->data->cover->attr(array('label' => __('Album Cover'), 'value' => (int)$data->cover, 'class' => 'inputbox tiny', 'suffix' => '<span class="IMG Folder Pointer" onclick="$.PhotoAlbum.chooser(\'data-cover\', '.(int)$data->id.')"></span>'));
		# unset($form->data->parent); # TODO: I don't know!
	}
	public static function presaveTaxonomy($data) {
		$data->album['published'] = \Regional\API::timestamp($data->published);
		unset($data->published);
		$data->parent = 0;
		settype($data->album['cover'], 'int');
	}
	public static function schemaTaxonomy(&$schema) {
		$schema['fields'] += array(
			'published' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
			'location' => array(
				'type' => 'text',
				'length' => 128
			),
			'cover' => array(
				'type' => 'integer'
			)
		);
	}
	
	public static function menu() {
		return array(
			'album/archive' => array(
				'title' => __('Album'),
				'access arguments' => array('administer albums'),
				'parent' => 'content'
			),
			'album/upload' => array(
				'title' => __('Upload'),
				'access arguments' => array('administer albums'),
				'parent' => 'album/archive'
			),
		);
	}
	public static function permission() {
		return array(
			'administer albums' => array(
				'title' => __('Administer albums'),
			),
		);
	}
	public static function style() {
		return __DIR__.'/style.css';
	}
	public static function script() {
		return __DIR__.'/script.js';
	}
}