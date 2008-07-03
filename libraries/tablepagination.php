<?
class TablePagination extends \Model\SelectQueryExtender {
	const MAX_RESULTS = 100, PER_PAGE = 15, START_INDEX = 0;
	public function preExecute() {
		$_REQUEST['count'] = isset($_REQUEST['count']) ? max(1, min((int)$_REQUEST['count'], static::MAX_RESULTS)) : static::PER_PAGE;
		$_REQUEST['offset'] = isset($_REQUEST['page']) ? abs((int)$_REQUEST['page']-1) * $_REQUEST['count'] : (isset($_REQUEST['offset']) ? max(0, (int)$_REQUEST['offset']) : static::START_INDEX);
		if ($_REQUEST['offset']>$this->query->count())
			throw new Exception('Offset '.$_REQUEST['offset'].' is invalid for results!');
		$this->query->limit($_REQUEST['count'], $_REQUEST['offset']);
		return TRUE;
	}
}