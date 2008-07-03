<?
namespace Regional;
class Controller extends \Controller {
	protected $permission = array('preferences' => 'administer regional', 'translate' => 'translate interface', 'edit' => 'translate interface');
	protected function preferences($A, $D) {
		$cfg = \Registry::getInstance('regional', SERVICE_REGIONAL);
		if ($this->callback) {
			foreach ($cfg as $key => $value)
				$cfg->$key = $D->$key;
			\View::status(__('The configuration options have been saved.'));
			return TRUE;
		}
		
		$form = new \Form;
		
		$form->locale->attr(array('type' => 'fieldset', 'legend' => __('Locale')));
		$form->locale->firstDay->attr(array('name' => 'data[firstDay]', 'label' => __('First day of week'), 'type' => 'select', 'value' => $cfg->firstDay, 'options' => array(__('Sunday'), __('Monday'), 6 => __('Saturday'))));
		$form->locale->language->attr(array('name' => 'data[language]', 'label' => __('Language'), 'type' => 'radio', 'value' => $cfg->language, 'options' => array('en' => 'English', 'fr' => 'French', 'fa' => 'Persian')));
		$form->locale->calendar->attr(array('name' => 'data[calendar]', 'label' => __('Calendar'), 'type' => 'radio', 'value' => $cfg->calendar, 'options' => array('gregorian' => 'Gregorian', 'jalali' => 'Jalali')));
		
		$form->timezone->attr(array('type' => 'fieldset', 'legend' => __('Time zones')));
		$form->timezone->default->attr(array('name' => 'data[timezone]', 'label' => __('Default time zone'), 'type' => 'select', 'value' => $cfg->timezone, 'options' => API::time_zones(TRUE)));
		
		$form->format->attr(array('type' => 'fieldset', 'legend' => __('Date type')));
		$fn = function($format) {
			return format_date(GMT, $format);
		};
		$dateFormats = array(
			'short' => array('Y-m-d H:i', 'Y M j - g:ia', 'j M Y - g:ia', 'M j Y - g:ia', 'Y M j - H:i', 'j M Y - H:i', 'M j Y - H:i', 'Y/m/d - g:ia', 'd/m/Y - g:ia', 'm/d/Y - g:ia', 'd.m.Y - H:i', 'Y/m/d - H:i', 'd/m/Y - H:i', 'm/d/Y - H:i'),
			'medium' => array('D, d/m/Y - g:ia', 'D, Y/m/d - g:ia', 'F j, Y - g:ia', 'j F Y - g:ia', 'Y, F j - g:ia', 'j. F Y - G:i', 'D, m/d/Y - g:ia', 'Y, F j - H:i', 'D, Y-m-d H:i', 'D, m/d/Y - H:i', 'D, d/m/Y - H:i', 'D, Y/m/d - H:i', 'F j, Y - H:i', 'j F, Y - H:i'),
			'long' => array('l, Y,  F j - g:ia', 'l, j F Y - g:ia', 'l, F j, Y - g:ia', 'l, Y,  F j - H:i', 'l, j F, Y - H:i', 'l, F j, Y - H:i', 'l, j. F Y - G:i')
		);
		$form->format->formatLong->attr(array('name' => 'data[formatLong]', 'label' => __('Long'), 'type' => 'select', 'value' => $cfg->formatLong, 'options' => array_combine($dateFormats['long'], array_map($fn, $dateFormats['long']))));
		$form->format->formatMedium->attr(array('name' => 'data[formatMedium]', 'label' => __('Medium'), 'type' => 'select', 'value' => $cfg->formatMedium, 'options' => array_combine($dateFormats['medium'], array_map($fn, $dateFormats['medium']))));
		$form->format->formatShort->attr(array('name' => 'data[formatShort]', 'label' => __('Short'), 'type' => 'select', 'value' => $cfg->formatShort, 'options' => array_combine($dateFormats['short'], array_map($fn, $dateFormats['short']))));

		$form->button->submit['value'] = __('Save configuration');
		return $form;
	}
	
	protected function translate($A, $D) {
		return ('<center><input onblur="_$({url: \'/regional/translate\', data: {arg: {msgid: this.value}}, target: function(data){$(\'#content\').html(data.data)}})" size="100" style="direction: ltr" value="'.$A->msgid.'" /></center>').new \TableSelect(NULL, array('module' => array('data' => __('Module'), 'field' => 'module'), 'msgid' => array('data' => __('Message'), 'field' => 'msgid'), 'msgstr' => array('data' => __('Translate'), 'field' => 'msgstr', 'sort' => 'asc')), \Model\I18n::all()->fields('msgid', 'msgstr', 'module')->filter('language', JOORCHIN_LANGUAGE)->filter('msgid', $A->msgid ?: '//', '=~')->map(function($model) {
			$model->module = \Service::load($model->module)->title;
			$model->msgstr = '<textarea class="full" onblur="_$({url: \'/regional/edit\', data: {arg: {msgid: this.title, msgstr: this.value}}})" title="'.htmlspecialchars($model->msgid).'">'.$model->msgstr.'</textarea>';
			$model->msgid = '<div style="width: 250px">'.$model->msgid.'</div>';
		}));
	}
	protected function edit($A) {
		$msgid = $A->msgid;
		unset($A->msgid);
		\Model\I18n::all()->filter('language', JOORCHIN_LANGUAGE)->filter('msgid', $msgid)->update($A, \Model\Model::MULTIPLE);
		return TRUE;
	}
}