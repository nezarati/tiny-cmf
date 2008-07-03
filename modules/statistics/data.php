<?
namespace Statistics;
class Data {
	protected $obj, $report;
	public function __construct($obj, $report) {
		$this->obj = $obj;
		$this->report = $report;
	}
	public function count() {
		return $this->obj->totalResults;
	}
	public function execute() {
		return $this->report;
	}
}