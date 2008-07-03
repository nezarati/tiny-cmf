<?
class TableSelect {
	protected $result, $caption, $headers, $empty, $total, $keys;
	public function __construct($caption, $headers, $query, $empty = NULL) {
		if (empty($_REQUEST['sort']))
			foreach ($headers as $key => $data)
				if (isset($data['sort'])) {
					$_REQUEST['sort'] = ($data['sort'] == 'desc' ? '-' : '').$key;
					break;
				}
		$_REQUEST['sort'] = preg_replace_callback('/([A-z0-9_.]+)/', function($match) use($headers) {
			return $headers[$match[1]]['field'];
		}, $_REQUEST['sort']);
		$this->caption = $caption;
		$this->headers = $headers;
		$this->empty = $empty;
		$this->result = $query->extend('TablePagination')->extend('TableSort');
		$this->total = $query->count();
		$this->keys = array_keys($this->headers);
	}
	public function __toString() {
		reset($_REQUEST['sort']);
		$order = key($_REQUEST['sort']);
		$sort = current($_REQUEST['sort']);
		foreach ($this->headers as $key => $data) {
			#if ($order == $data['field']) TODO:
			#	$data['style']['background-color'] = 'blue';
			if (is_array($data['style'])) {
				foreach ($data['style'] as $property => &$value)
					$value = $property.':'.$value;
				$style = 'style="'.implode(';', $data['style']).'" ';
			} else
				$style = '';
			$col .= '<col '.$style.'/>';
			$head .= isset($data['field']) ? '<th title="'.__('Sort by :name', array('@name' => $data['data'])).'" class="sort'.($order == $data['field'] ? ' active '.($sort == '-' ? 'desc' : 'asc') : '').'"><a href="'.url(array('sort' => ($order != $data['field'] ? $sort : ($sort == '-' ? '' : '-')).$key, 'offset' => NULL, 'page' => NULL)).'" rel="nofollow ajax">'.$data['data'].'</a>'.($order == $data['field'] ? ($sort == '-' ? '˅' : '˄') : '').'</th>' : '<th>'.$data['data'].'</th>';

			if ($data['primary'])
				$primary = $key;
		}
		
		$this->results = array();
		if ($primary)
			foreach ($this->result as $doc) {
				$doc->$primary = '<strong>'.$doc->$primary.'</strong><div class="row-actions">'.\View::operations($doc->actions).'</div>';
				$this->results[] = $doc;
			}
		else
			$this->results = $this->result;
		
		$index = 0;
		foreach ($this->results as $row) {
			$tds = '';
			foreach ($this->keys as $key)
				$tds .= '<td>'.$row->$key.'</td>';
			$body .= '<tr class="'.($index+1 & 1 ? 'odd' : 'even').'">'.$tds.'</tr>';
			$index++;
		}
		return $this->table($col, $head, $body);
	}
	protected function table($col, $head, $body) {
		return '<table><caption>'.$this->caption.'</caption>'.$col.'<thead><tr>'.$head.'</tr></thead><tbody>'.($body ?: $this->emptyMessage()).'</tbody></table>'.$this->tfoot();
	}
	protected function emptyMessage() {
		return '<tr class="odd"><td class="empty message" colspan="'.count($this->headers).'">'.$this->empty.'</td></tr>';
	}
	protected function perPage() {
		foreach (array_merge(range(5, 30, 5), array(50, 100)) as $count)
			$per_page[$count] = '<a href="'.url(array('count' => $count, 'offset' => NULL, 'page' => NULL, 'sort' => empty($_REQUEST['sort']) ? $_REQUEST['sort'] : NULL)).'" rel="nofollow ajax">'.$count.'</a>';
		$per_page[$_REQUEST['count']] = $_REQUEST['count'];
		return implode(', ', $per_page);
	}
	protected function tfoot() {
		return '<div class="tablenav-pages">'.($this->total <= $_REQUEST['count'] ? '' : '<div class="paginationLabel">'.__('Displaying :start-:end of :page', array('@start' => $_REQUEST['offset']+1, '@end' => min($_REQUEST['offset']+$_REQUEST['count'], $this->total), '@page' => number_format($this->total))).'</div>'.new Pagination($this->total, NULL, $_REQUEST['count'])).'</div>';
	}
	public function render() {
		return $this->__toString();
	}
}