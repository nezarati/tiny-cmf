<?
#TODO: License, Quota, Photo Location, Tags, Comment
namespace Album;
class Controller extends \Controller {
	const FORMAT_DATE = 'M j, Y', SIZE_LIMIT = 4194304;
	protected $permission = array(
		'chooser' => 'administer users',
		'information' => 'access content',
		'upload' => 'administer users',
		'edit' => 'administer users',
		'archive' => 'administer albums',
		'delete' => 'administer albums',
		'index' => 'access content',
	);
	
	protected function archive($A) {
		return is_numeric($A->id) ? new \GridView(NULL, \Model\Storage::feed(SERVICE_STORAGE_ALBUM)->filter('taxonomy', (int)$A->id)->map(function($doc) {
			$doc->content = '
	<div class="PhotoAlbum-photoFrame">
		<img src="'.\Model\Storage::thumbnail($doc, 128, 96).'" />
	</div>
	<p>'.$doc->description.'</p>
	<p>'.\View::operations(array('album-edit' => array('type' => 'edit', 'onclick' => '$.PhotoAlbum.edit('.$doc->id.')'), 'album-delete' => array('type' => 'delete', 'href' => '/album/'.$doc->id.'/delete'))).'</p>
			';
		}), 'There are no photos in this album.') : new \GridView(NULL, \Taxonomy\API::feed(SERVICE_TAXONOMY_ALBUM)->map(function($doc) {
			$doc->content = '
<a href="/album/'.$doc->id.'/archive" rel="ajax">
	<div class="PhotoAlbum-coverFrame">
		<img src="'.($doc->cover ? \Model\Storage::thumbnail(\Model\Storage::url(SERVICE_STORAGE_ALBUM, $doc->cover), 144, 144) : JOORCHIN_STATIC_FILE.'module/album/cover.jpg').'" />
	</div>
	<p>
		<strong>'.$doc->term.'</strong>
	</p>
</a>
<p>'.format_date($doc->published, \Album\Controller::FORMAT_DATE).'</p>
<p>
	<small>'.\Album\__('photos: :count', array('count' => (int)$doc->count)).'</small>
</p>
			';
		}), __('No albums available.'), 4);
	}
	protected function upload($A) {
		if (!is_numeric($A->id)) {
			$form = new \Form;
			
			$data = $form->data;
			$data->attr(array('legend' => __('Album'), 'type' => 'fieldset'));
			$data->taxonomy->attr(array('label' => __('Choose an album'), 'type' => 'taxonomy', 'service' => SERVICE_TAXONOMY_ALBUM, 'blank' => TRUE));
			
			$form->upload->attr(array('type' => 'fieldset', 'legend' => __('Select photos to upload')));
			$form->upload = \View::script('$(document).ready(function() {
	$("#data-taxonomy").change(function() {
		new qq.FileUploader({
			element: document.getElementById("file-uploader-demo1"),
			action: "/album/"+this.value+"/upload",
			allowedExtensions: ["jpeg", "jpg", "png"],
			sizeLimit: '.self::SIZE_LIMIT.'
		});
	});
});').'<div id="file-uploader-demo1">
	<noscript>
		<p>Please enable JavaScript to use file uploader.</p>
		<!-- or put a simple form for upload here -->
	</noscript>
</div>';
			
			unset($form->button);
			
			return $form;
		}
		list($width, $height, $type) = @getImageSize('php://input');
		if (!in_array($type, array(IMAGETYPE_JPEG, IMAGETYPE_PNG)))
			\View::error(__('Not a valid file type.'));
		else if (strlen($bytes = file_get_contents('php://input'))>self::SIZE_LIMIT)
			\View::error(__(':fileName is too large, maximum file size is :sizeLimit.', array('fileName' => $_SERVER['HTTP_X_FILE_NAME'], 'sizeLimit' => self::SIZE_LIMIT)));
		else {
			$doc = array('service' => SERVICE_STORAGE_ALBUM, 'filename' => $_SERVER['HTTP_X_FILE_NAME'], 'taxonomy' => (int)$A->id, 'contentType' => image_type_to_mime_type($type), 'data' => $bytes);
			$info =& $doc['metadata'];
			$info = compact('width', 'height');
			if ($type == IMAGETYPE_JPEG) {
				$PhotoInformation = new PhotoInformation('php://input');
				foreach (array('camera', 'model', 'iso', 'exposure', 'aperture', 'focalLength', 'flash', 'latitude', 'longitude') as $key)
					if ($value = $PhotoInformation->$key)
						$info[$key] = $value;
				$info['published'] = $PhotoInformation->created ?: GMT;
			} else
				$info['published'] = GMT;
			
			$storage = new \Model\Storage($doc); # PHP Bug
			$storage->put();
			
			\Taxonomy\API::count(SERVICE_TAXONOMY_ALBUM, $A->id);
			
			die(json_encode(array('success' => 'Ok')));
		}
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
	}
	
	private static function form($doc) {
		$form = new \Form;

		$data = $form->data;
		$data->attr(array('type' => 'fieldset', 'legend' => ''));
		$data->filename->attr(array('label' => __('File Name'), 'value' => $doc->filename, 'dir' => 'ltr', 'prefix' => '<img src="'.\Model\Storage::thumbnail($doc, 128, 96).'" />'));
		$data->description->attr(array('label' => __('Caption'), 'type' => 'textarea', 'value' => $doc->description));
		$data->taxonomy->attr(array('label' => __('Taxonomy'), 'type' => 'taxonomy', 'service' => SERVICE_TAXONOMY_ALBUM, 'value' => $doc->taxonomy));
		$data->downloads->attr(array('label' => __('Downloads'), 'class' => 'inputbox tiny', 'value' => $doc->downloads));
		$data->weight->attr(array('label' => __('Weight'), 'type' => 'weight', 'value' => $doc->weight));
		
		$form->button->submit['value'] = __('Save');
		
		return $form;
	}
	protected function edit($A, $D) {
		is_numeric($A->id) || die;
		
		if (!$this->callback)
			return self::form(\Model\Storage::load(SERVICE_STORAGE_ALBUM, $A->id));

		$D->service = SERVICE_STORAGE_ALBUM;
		$D->id = $A->id;

		$storage = new \Model\Storage; # PHP Bug
		
		if ($A->id) {
			$doc = \Model\Storage::load(SERVICE_STORAGE_ALBUM, $A->id);
			$D->taxonomy != $doc->taxonomy && \Taxonomy\API::count(SERVICE_TAXONOMY_ALBUM, $D->taxonomy, $doc->taxonomy);
		} else
			\Taxonomy\API::count(SERVICE_TAXONOMY_ALBUM, $D->taxonomy);
		
		return (bool)$storage->put($D);
	}
	protected function chooser($A) {
		is_numeric($A->id) || die;
		$this->view->pageTitle[0] = __('Photo Chooser');
		$result = array();
		foreach (\Model\Storage::all()->filter('service', SERVICE_STORAGE_ALBUM)->filter('taxonomy', $A->id)->fields('service', 'id')->map() as $doc)
			$result[$doc->id] = \Model\Storage::thumbnail($doc, 43, 43);
		if (!$result)
			\View::warning(__('No Images Found.'));
		return $result;
	}
	protected function information($A) {
		is_numeric($A->id) || die;
		$this->view->pageTitle[0] = __('Photo Information');

		$doc = \Model\Storage::load(SERVICE_STORAGE_ALBUM, $A->id);
		$info = (object)$doc->metadata;

		$reduce = array(
			format_date($info->published, self::FORMAT_DATE),
			$info->width.'×'.$info->height.' pixels – '.\Main\API::byteConvert($doc->length),
			'Filename' => $doc->filename,
			'Camera' => $info->camera,
			'Model' => $info->model,
			'ISO' => $info->iso,
			'Exposure' => $info->exposure ? $info->exposure.'sec' : NULL,
			'Aperture' => $info->aperture,
			'Focal Length' => $info->focalLength ? $info->focalLength.'mm' : NULL,
			'Flash Used' => $info->flash ? (in_array(PhotoInformation::flash($info->flash), 'Fired') ? __('Yes') : __('No')) : NULL,
			'Latitude' => $info->latitude ? $info->latitude.'° N' : NULL,
			'Longitude' => $info->longitude ? $info->longitude.'° E' : NULL,
			__('Downloads: :count', array('count' => $doc->downloads)),
			__('Date added: :date', array('date' => format_date($doc->created, self::FORMAT_DATE)),
			__('Date taken: :date', array('date' => format_date($info->published, self::FORMAT_DATE)),
			__('Added by: :name', array('name' => \User\API::load($doc->user)->name))
		);
		foreach ($reduce as $label => $value)
			$ret[] = '<li>'.(!is_numeric($label) ? __($label).': '.($value ?: 'n/a') : $value).'</li>';
		return '<ul class="PhotoAlbum-information">'.implode($ret).'</ul>';
	}
	protected function delete($A) {
		is_numeric($A->id) || die;

		$doc = \Model\Storage::load(SERVICE_STORAGE_ALBUM, $A->id);
		\Taxonomy\API::count(SERVICE_TAXONOMY_ALBUM, 0, $doc->taxonomy);

		return \Model\Storage::all()->filter('service', SERVICE_STORAGE_ALBUM)->filter('id', $A->id)->fields('_id')->delete();
	}
	protected function index($A) {
		ob_start();
?>
<link type="text/css" rel="stylesheet" href="/galleryview/galleryview.css" />
<script type="text/javascript" src="/galleryview/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="/galleryview/jquery.galleryview-2.1.js"></script>
<script type="text/javascript" src="/galleryview/jquery.timers-1.2.js"></script>
<script type="text/javascript"> 
	$(document).ready(function() {
		$('#photos').galleryView({
			panel_width: 650,
			panel_height: 500,
			frame_width: 100,
			frame_height: 38,
			transition_speed: 1200,
			background_color: '#222',
			border: 'none',
			easing: 'easeInOutBack',
			pause_on_hover: true,
			nav_theme: 'custom',
			overlay_height: 75,
			filmstrip_position: 'top',
			overlay_position: 'top'
		});
	});
</script>
<div id="photos" class="gallery">
	<?foreach ($result = \Model\Storage::feed(SERVICE_STORAGE_ALBUM)->filter('taxonomy', $A->id) as $doc) {?>
	<div class="panel" style="overflow: auto">
		<img src="<?=\Model\Storage::thumbnail($doc, 650, 500)?>" />
		<div class="panel-overlay">
			<h2><?=$doc->description?> <?=\Rate\Hook::_block(SERVICE_RATE_ALBUM, 'album', $doc->id)?> <a href="<?=\Model\Storage::url($doc->service, $doc->id)?>" title="View full-size photo here" onclick="return $.PhotoAlbum.save(this)"><span class="IMG Save"></span></a></h2>
			<ul>
				<li><a href="#" onclick="return $.PhotoAlbum.information(<?=$doc->id?>)"><?=__('Photo Information')?></a></li>
			</ul>
		</div>
		<div class="overlay-background"></div>
	</div>
	<?}?>
	<ul class="filmstrip">
	<?foreach ($result as $doc) {?>
		<li class="frame">
			<img src="<?=\Model\Storage::thumbnail($doc, 100, 38)?>" />
			<div class="caption"><?=$doc->description?></div>
		</li>
	<?}?>
	</ul>
</div>
<?
		$contents = ob_get_clean();
		return $result->count() ? $contents : __('This user has no Public Albums.');
	}
}