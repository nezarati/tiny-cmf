<?
class GridView extends TableSelect {
	protected $perRow;
	public function __construct($caption, $query, $empty = NULL, $perRow = 5) {
		parent::__construct($caption, array(), $query, $empty);
		$this->perRow = $perRow;
	}
	public function __toString() {
		$index = 1;
		foreach ($this->result as $doc) {
			$body .= '<td>'.$doc->content.'</td>';
			if ($index%$this->perRow == 0)
				$body .= '</tr><tr>';
			$index++;
		}
		
		return $this->table($col, $head, $body ? '<tr>'.$body.'</tr>' : NULL);
	}
}