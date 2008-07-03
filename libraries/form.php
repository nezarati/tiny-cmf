<?
class Form implements ArrayAccess, Iterator, Countable {
	
	const METHOD_DELETE = 'delete', METHOD_GET = 'get', METHOD_POST = 'post', METHOD_PUT = 'put';
	const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded', ENCTYPE_MULTIPART = 'multipart/form-data';
	
	protected $attributes = array(), $children = array(), $value;
	
	public function __construct($value = '', $extend = FALSE) {
		$this->value = $value;
		if (!$extend) {
			$this['method'] = static::METHOD_POST;
			$this['action'] = PATH;
			$this['class'] = 'form';
			$this['rel'] = 'ajax';
			$this->button['type'] = 'div';
			$this->button->submit->attr(array('type' => 'submit', 'name' => 'submit'));
			$this->button->token->attr(array('type' => 'hidden', 'name' => 'token', 'value' => md5(uniqid(mt_rand(0, mt_getrandmax()), TRUE))));
		}
	}
	public function __set($name, $value) {
		if (empty($this->children[$name])) {
			$class = __CLASS__;
			$this->children[$name] = new $class($value, TRUE);
			$this->children[$name]['id'] = $this['id'].'-'.$name;
			return $this->children[$name];
		} else
			$this->children[$name]->value = $value;
	}
	public function __isset($name) {
		return isset($this->children[$name]);
	}
	public function __unset($name) {
		unset($this->children[$name]);
	}
	public function __get($name) {
		return $this->__isset($name) ? $this->children[$name] : $this->__set($name, '');
	}
	public function __toString() {
		return $this->value;
	}
	public function __clone() {
	}
	public function __destruct() {
	}
	
	public function offsetSet($offset, $value) {
		$this->attributes[$offset] = $value;
	}
	public function offsetExists($offset) {
		return isset($this->attributes[$offset]);
	}
	public function offsetUnset($offset) {
		unset($this->attributes[$offset]);
	}
	public function offsetGet($offset) {
		return $this->offsetExists($offset) ? $this->attributes[$offset] : NULL;
	}
	
	public function rewind() {
		reset($this->children);
	}
	public function valid() {
		return $this->current() !== FALSE;
	}
	public function current() {
		return current($this->children);
	}
	public function key() {
		return key($this->children);
	}
	public function next() {
		next($this->children);
	}
	
	public function count() {
		return count($this->children);
	}
	
	public function render() {
		$render = '<form'.$this->attr().'><div id="message"></div>'.$this.$this->asXML().'</form>';
		if ($this->button->token['value']) {
			$form = new \Model\Form(array('token' => $this->button->token['value'], 'data' => $this->validators(), 'created' => GMT), FALSE); # PHP Bug
			$form->put();
		}
		return $render;
	}
	public function asXML() {
		static $form;
		if (!$form)
			$form = $this;
		$output = $suffix = '';
		foreach ($this as $node => $child) {
			$type = $child['type'] ?: 'input';
			$label = $child['label'];
			unset($child['label'], $child['type']);
			$child['id'] = substr($child['id'], 1);
			if ($type == 'fieldset')
				$output .= Element::$type($child, $form);
			else if ($type == 'div')
				// $suffix = '<fieldset class="submit-buttons">'.$child->asXML($form).'</fieldset>';
				$suffix = '<div class="submit-buttons">'.$child->asXML($form).'</div>';
			else if (!$label && !$type)
				$output .= $child->value;
			else if (!$label)
				$output .= Element::$type($child, $form);
			else {
				isset($child['name']) || ($child['name'] = preg_replace('/^(.*?)\]\[/', '$1[', str_replace('-', '][', $child['id'])).']');
				$_prefix = $child['prefix'];
				$_suffix = $child['suffix'];
				unset($child['prefix'], $child['suffix']);
				if (isset($child['tip'])) {
					$tip = '<br /><small class="tip">'.$child['tip'].'</small>';
					unset($child['tip']);
				} else
					$tip = '';
				$output .= '<tr><td><label for="'.$child['id'].'">'.$label.'</label></td><td>'.$_prefix.Element::$type($child, $form).$_suffix.$tip.'</td></tr>';
				//$output .= '<dl><dt><span class="IMG '.(isset($child['validator']) ? 'FlagRed' : 'FlagGreen').'"></span><label for="'.$child['id'].'">'.$label.'</label>'.$tip.'</dt><dd>'.$_prefix.Element::$type($child, $form).$_suffix.'</dl>';
			}
		}
		return ($output ? $output.$this->value : '<tr><td colspan="3">'.$this->value.'</td></tr>').$suffix;
	}
	public function attr(Array $attr = NULL) {
		if (is_array($attr)) {
			foreach ($attr as $key => $value)
				$this->attributes[$key] = $value;
			return;
		}
		$attr = array();
		foreach ($this->attributes as $name => $value)
			$attr[] = $name.'="'.htmlSpecialChars($value).'"';
		return ' '.implode(' ', $attr);
	}
	public function validators($name = NULL, $value = NULL) {
		static $validators = array();
		if ($name == NULL && $value == NULL)
			return $validators;
		$name = trim(preg_replace('/(\]|\[|\]\[)/', '-', $name), '-');
		if (is_array($value)) {
			$arg = $value;
			$func = array_shift($arg);
		} else {
			$arg = array();
			$func = $value;
		}
		$validators[$name] = array('callback' => $func, 'arguments' => $arg);
	}
}
class Element {
	public static function fieldset($obj, Form $form) {
		$legend = $obj['legend'];
		unset($obj['legend']);
		return '<table><thead><tr><th colspan="3"'.$obj->attr().'>'.$legend.'</th></tr></thead><tbody>'.$obj->asXML($form).'</tbody></table><br />';
		//return '<fieldset'.$obj->attr().'><legend>'.(empty($legend) ? '' : '<span class="IMG FlagYellow"></span>').$legend.'</legend>'.$obj->asXML($form).'</fieldset>';
	}
	
	public static function input($obj, Form $form) {
		isset($obj['class']) || ($obj['class'] = 'inputbox medium');
		if (isset($obj['validator'])) {
			$form->validators($obj['name'], $obj['validator']);
			unset($obj['validator']);
		}
		return '<input'.$obj->attr().' />';
	}
	public static function radio($_obj, Form $form) {
		$obj = clone $_obj;
		$output = '';
		$checked = $obj['value'];
		$id = $obj['id'];
		$obj['type'] = 'radio';
		$options = $obj['options'];
		unset($obj['options']);
		if (empty($obj['validator']))
			$obj['validator'] = array('validate_options', array_keys($options));
		foreach ($options as $value => $label) {
			$obj['value'] = $value;
			if ($checked == $value)
				$obj['checked'] = 'checked';
			else
				unset($obj['checked']);
			$obj['class'] = '';
			$obj['id'] = $id.'-'.$value;
			$output .= static::input($obj, $form).' <label for="'.$obj['id'].'">'.$label.'</label><br />';
		}
		return $output;
	}
	public static function sort($obj, Form $form) {
		$obj['options'] = array('asc' => __('Ascent'), 'desc' => __('Descent'));
		return static::radio($obj, $form);
	}
	public static function checkbox($_obj, Form $form) {
		$obj = clone $_obj;
		$output = '';
		$checked = isset($obj['value']) ? $obj['value'] : array();
		$id = $obj['id'];
		$name = $obj['name'];
		$obj['type'] = 'checkbox';
		$options = $obj['options'];
		unset($obj['options']);
		$obj['class'] = '';
		if (is_array($options))
			foreach ($options as $value => $label) {
				$obj['value'] = 1;
				if (in_array($value, $checked))
					$obj['checked'] = 'checked';
				else
					unset($obj['checked']);
				$obj['id'] = $id.'-'.$value;
				$obj['name'] = $name.'['.$value.']';
				$obj['class'] = '';
				$output .= static::input($obj, $form).' <label for="'.$obj['id'].'">'.$label.'</label><br />';
			}
		else
			$output = static::input($obj, $form);
		return $output;
	}
	public static function file($obj, Form $form) {
		$obj['type'] = 'file';
		return static::input($obj, $form);
	}
	public static function password($obj, Form $form) {
		$obj['type'] = 'password';
		return static::input($obj, $form);
	}
	public static function button($obj, Form $form) {
		$obj['type'] = 'button';
		isset($obj['class']) || ($obj['class'] = 'button');
		return static::input($obj, $form);
	}
	public static function submit($obj, Form $form) {
		isset($obj['class']) || ($obj['class'] = 'button');
		$obj['type'] = 'submit';
		return static::input($obj, $form);
	}
	public static function reset($obj, Form $form) {
		isset($obj['class']) || ($obj['class'] = 'button');
		$obj['type'] = 'reset';
		return static::input($obj, $form);
	}
	public static function hidden($obj, Form $form) {
		$obj['type'] = 'hidden';
		unset($obj['id']);
		return static::input($obj, $form);
	}
	public static function link($obj) {
		return '<a'.$obj->attr().'>'.$obj.'</a>';
	}
	
	public static function textarea($obj, Form $form) {
		isset($obj['class']) || ($obj['class'] = 'medium');
		
		if (is_string($obj['value'])) {
			$value = $obj['value'];
			unset($obj['value']);
		} else
			$value = (string)$obj;
		if (isset($obj['validator'])) {
			$form->validators($obj['name'], $obj['validator']);
			unset($obj['validator']);
		}
		return '<textarea'.$obj->attr().'>'.htmlSpecialChars($value).'</textarea>';
	}
	
	public static function select($obj, Form $form) {
		$options = array();
		$multiple = $obj['multiple'];
		$values = $obj['value'];
		if ($multiple && !is_array($values))
			$values = array();
		foreach ($obj['options'] as $key => $value)
			$options[htmlSpecialChars($key)] = '<option value="'.htmlSpecialChars($key).'"'.($multiple && in_array($key, $values) || $values == $key ? ' selected="selected"': '').'>'.$value.'</option>';
		unset($obj['options'], $obj['value']);
		if ($multiple)
			$obj['name'] .= '[]';
		if (empty($obj['validator']))
			$obj['validator'] = array('validate_options', array_keys($options));
		if (isset($obj['validator'])) {
			$form->validators($obj['name'], $obj['validator']);
			unset($obj['validator']);
		}
		return '<select'.$obj->attr().'>'.implode($options).'</select>';
	}
	public static function weight($obj, Form $form) {
		isset($obj['value']) || ($obj['value'] = 0);
		$obj['options'] = array_combine(range(15, -15), range(15, -15));
		return static::select($obj, $form);
	}
	public static function taxonomy($obj, Form $form) {
		$taxonomy = array();
		$ignore = $obj['ignore'];
		foreach (\Taxonomy\API::feed($obj['service'])->fields('id', 'parent', 'term') as $row)
			if ($ignore != $row->id)
				$taxonomy[$row->parent][$row->id] = $row->term;
#		$taxonomy = array(0 => array(1 => 'News', 3 => 'Web'), 1 => array(5 => 'Sport', 6 => 'Economy'), 3 => array(2 => 'ServerSide'), 2 => array(66 => 'PHP'));
		$obj['options'] = ($obj['blank'] ? array(__('- None -')) : array())+static::taxonomyOptions($taxonomy);
		
		$render = isset($obj['render']) ? 'static::'.$obj['render'] : 'static::select';
		
		unset($obj['service'], $obj['blank'], $obj['ignore'], $obj['render']);
		return call_user_func($render, $obj, $form);
	}
	protected static function taxonomyOptions(Array &$taxonomy, $parent = 0, $repeat = 0) {
		$options = array();
		if (is_array($taxonomy[$parent]))
			foreach ($taxonomy[$parent] as $id => $term) {
				$options[$id] = str_repeat('»', $repeat).' '.$term;
				isset($taxonomy[$id]) && ($options += static::taxonomyOptions($taxonomy, $id, $repeat+1));
			}
		return $options;
	}
	
	public static function limit($obj, Form $form) {
		$obj['options'] = array_combine($value = array_merge(range(5, 30, 5), array(50, 100)), $value);
		return static::select($obj, $form);
	}
	
	public static function datetime($obj, Form $form) {
		$date = new \DateTime('now');
		$obj['value'] = ($obj['value'] ?: GMT) + $date->getOffset();
		return call_user_func('\Regional\DateTime'.ucFirst(JOORCHIN_CALENDAR).'::dateForm', $obj['name'], $obj['value']).' @'.\Regional\API::time($obj['name'], $obj['value']);
	}
}