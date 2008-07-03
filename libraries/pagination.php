<?
class Pagination { # 2009/02/10 09:19:24 - 09/03/06 19:36
	protected $total, $current, $queryString, $range = 3, $result;
	public function __construct($total, $queryString = '', $perPage = \TablePagination::PER_PAGE, $current = 0, $result = NULL) {
		$this->total = ceil($total/($perPage ?: $_REQUEST['length']));
		$this->current = max(min((int)$current ?: ceil(((int)$_REQUEST['offset'] ?: 0) / ((int)$_REQUEST['length'] ?: $perPage)) + 1, $this->total), 1);
		$this->queryString = $queryString ?: url(is_numeric($_REQUEST['page']) ? array('page' => '{page}') : array('offset' => '{offset}', 'length' => '{length}'));
		$this->result = $result;
		if (!(int)$_REQUEST['length'])
			$_REQUEST['length'] = $perPage;
	}
	public function __toString() {
		$min = min($this->total, $this->current+$this->range);
		$page = $min == 0 ? array() : array_fill_keys(range(max(1, $this->current-$this->range), $min), 'default');
		$page[$this->current] = 'active';
		foreach ($page as $num => $class)
			$r .= '<li class="'.$class.'"><a rel="nofollow ajax" href="'.$this->queryString($num).'" target="'.$this->result.'">'.$num.'</a></li>';
		return '<ul class="pagination">'.($this->current>1 ? '<li class="first"><a rel="nofollow ajax" href="'.$this->queryString(1).'" target="'.$this->result.'" title="'.__('First').'">'.__('«').'</a></li><li class="previous"><a rel="nofollow ajax" href="'.$this->queryString($this->current-1).'" target="'.$this->result.'" title="'.__('Previous').'">'.__('‹').'</a></li>' : '').($this->current-$this->range>1 ? '<li class="ellipsis"><a rel="nofollow ajax" href="'.$this->queryString($this->current-$this->range-1).'" target="'.$this->result.'">…</a></li>' : '').$r.($this->total-$this->range>$this->current ? '<li class="ellipsis"><a rel="nofollow ajax" href="'.$this->queryString($this->current+$this->range+1).'" target="'.$this->result.'">…</a></li>' : '').($this->current != $this->total ? '<li class="next"><a rel="nofollow ajax" href="'.$this->queryString($this->current+1).'" target="'.$this->result.'" title="'.__('Next').'">'.__('›').'</a></li><li class="last"><a rel="nofollow ajax" href="'.$this->queryString($this->total).'" target="'.$this->result.'" title="'.__('Last').'">'.__('»').'</a></li>' : '').'</ul>';
	}
	private function queryString($num) {
		return str_replace(array('%7Boffset%7D', '%7Blength%7D', '%7Bpage%7D'), array(($num-1)*$_REQUEST['length'], $_REQUEST['length'], $num), $this->queryString);
	}
	public function getPagesCounter() {
		# if ($this->total>1)
		# $this->current - $this->total
		# صفحه %d از %d
		# Page %d of %d
		# Move Up - Move Down
		# 201-300 of 1,481
		# حرکت رو به بالا - حرکت رو پایین
	}
}