<?
class Template implements ArrayAccess { # Friday 26 Nov 2010
	const NS = 'a';
	public $_source, $__self, $_blocks, $_mapModifier, $_document;
	public $_render, $__prefix, $__loop, $__suffix, $_contentPrefix, $_contentLoop, $_contentSuffix;
	function __construct($source, $self = NULL) {
		$this->_source = $source;
		$this->__self = $self ?: $this;
	}
	function __set($name, $func) { # define block
		$this->_blocks[$name] = $func;
	}
	function mapModifier($name, $func) {
		$this->_mapModifier[$name] = $func;
	}
	
	function offsetSet($name, $value) { # define Assign
		$this->_document[$name] = $value;
	}
	function offsetExists($name) {
		return isset($this->_document[$name]);
	}
	function offsetGet($name) {
		return $this->_document[$name];
	}
	function offsetUnset($name) {
		unset($this->_document[$name]);
	}
	
	static function &_attr($string) {
		$data = array();
		if (!$string)
			return $data;
		if (preg_match_all('/([a-z_0-9]+)=(["\'])(.*?)\2/iS', $string, $matches, PREG_SET_ORDER))
			foreach ($matches as $match)
				$data[$match[1]] = $match[3];
		return $data;
	}
	static function __export($var) {
		return '"'.strtr($var, array('\\' => '\\\\', '"' => '\"')).'"';
	}
	function _resolve($string, &$data, $prefix = NULL) { # Parse variable
		$data = (array)$data;
		return preg_replace_callback('/{@'.$prefix.'([a-z0-9.]+)}|@'.$prefix.'([a-z0-9.]+)/iS', function($matches) use($data) {
			return isset($matches[2]) ? call_user_func(__CLASS__.'::__export', $data[$matches[2]]) : $data[$matches[1]];
		}, $string);
	}
	static function _loopController(&$data, &$attr) {
		if ($attr['sort'] == 'desc')
			$data = array_reverse($data);
		if (is_numeric($attr['offset']) || is_numeric($attr['length']))
			$data = array_slice($data, is_numeric($attr['offset']) ? $attr['offset'] : 0, is_numeric($attr['length']) ? $attr['length'] : NULL);
	}
	function _serve() { # Parse all directives and render HTML/XML template
		foreach (preg_split('!(</?'.self::NS.':[a-z]+[^>]*>)!iS', $this->_source, 0, PREG_SPLIT_DELIM_CAPTURE) as $string) {
			if (preg_match('!^<(/)?'.self::NS.':([a-z]+)([^>]*?)(/)?>$!i', $string, $matches)) {
				if ($matches[4] == '/') { # '<NS:[...]/>'
					if ($matches[2] == 'block')
						if (!$stackBlocks)
							$this->_render .= $this->_data($this->_attr($matches[3]));
						else
							$this->_copy($string);
				} else if ($matches[1] != '/') {
					if ($matches[2] == 'block') {
						if (!$stackBlocks) {
							$this->_contentPrefix = $this->_contentLoop = $this->_contentSuffix = NULL;
							$this->__prefix = TRUE;
							$_blockAttr = $this->_attr($matches[3]);
						} else
							$this->_copy($string);
						$stackBlocks++;
					} else if ($matches[2] == 'loop') {
						if (!$stackLoops && $stackBlocks == 1) { # $stackBlocks == 1: must not be loop nested new block
							$this->__loop = TRUE;
							$this->__prefix = FALSE;
							$_loopAttr = $this->_attr($matches[3]);
						} else
							$this->_copy($string);
						$stackLoops++;
					}
				} else {
					if ($matches[2] == 'block') {
						$stackBlocks--;
						if (!$stackBlocks) {
							$this->__prefix = $this->__loop = $this->__suffix = FALSE;
							
							$loop = '';
							if (!$data = $this->_data($_blockAttr))
								continue;
							if ($_loopAttr['iterator']) {
								$this->_loopController($tmp =& $data[$_loopAttr['iterator']], $_loopAttr);
								if (is_array($tmp) || $tmp instanceof Iterator)
									foreach ($tmp as $current) {
										$tpl = new self($this->_resolve($this->_contentLoop, $current, $_loopAttr['current'].'\.'), $this->__self);
										$tpl->_blocks =& $current;
										$loop .= $tpl;
									}
								else
									$loop = $tmp;
							}
							$tpl = new self($this->_resolve($this->_contentPrefix.$loop.$this->_contentSuffix, $data, $_blockAttr['name'].'\.'), $this->__self);
							$tpl->_blocks =& $data;
							$this->_render .= $tpl;
							
						} else
							$this->_copy($string);
					} else if ($matches[2] == 'loop') {
						$stackLoops--;
						if (!$stackLoops && $stackBlocks == 1) {
							$this->__loop = FALSE;
							$this->__suffix = TRUE;
						} else
							$this->_copy($string);
					}
				}
			} else
				$this->_copy($string);
		}
	}
	function _copy(&$string) {
		if ($this->__prefix)
			$this->_contentPrefix .= $string;
		else if ($this->__loop)
			$this->_contentLoop .= $string;
		else if ($this->__suffix)
			$this->_contentSuffix .= $string;
		else
			$this->_render .= $string;
	}
	function _data(&$attr) {
		if (substr($attr['name'], 0, 5) == 'root.')
			$obj =& $this->__self->_blocks[$attr['name'] = substr($attr['name'], 5)];
		else
			$obj =& $this->_blocks[$attr['name']];

		return is_callable($obj) ? $obj($attr) : $obj;
	}
	# TODO: BUG! {_(":created - :modified", {"created": date(@created, time()), "modified": date(@modified)})} => regex select this: date(@created, time()
	function _modifier($string) {
		$self = $this;
		return preg_replace_callback(
			'/{([a-z0-9._]+)\((.*)\)}/iS',
			function($matches) use($self) {
				return @call_user_func_array(
					$self->_mapModifier[$matches[1]],
					(array)json_decode('['.preg_replace_callback('/[a-z0-9._]+\(.*?\)/iS', function($matches) use($self) {
						return call_user_func(__CLASS__.'::__export', $self->_modifier('{'.$matches[0].'}'));
					}, $matches[2]).']', TRUE)
				);
			},
			$string
		);
	}
	function __toString() {
	try {
		$this->_serve();
		return $this->_document ? $this->_modifier($this->_resolve($this->_render, $this->_document)) : $this->_render;
	} catch (Exception $e) {
		print_r($e);
		return 'Bug';
	}
	}
}