<?
class TableSort extends \Model\SelectQueryExtender {
	public function preExecute() {
		$sort = is_string($_REQUEST['sort']) ? $_REQUEST['sort'] : '';
		$_REQUEST['sort'] = array();
		foreach (explode(',', $sort) as $field)
			$_REQUEST['sort'][preg_replace('/[^A-z0-9_.]+/', '', $field{0} == '-' ? substr($field, 1) : $field)] = $field{0} == '-' ? '-' : '';
		foreach ($_REQUEST['sort'] as $order => $sort)
			$this->query->sort($sort.$order);
		return TRUE;
	}
}