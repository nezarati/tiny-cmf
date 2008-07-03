<?
namespace Statistics;
class GoogleAnalytics {
	const DEVELOPMENT_MODE = FALSE, MAX_RESULTS = 10000, START_INDEX = 1;
	const URL_CLIENT_LOGIN = 'https://www.google.com/accounts/ClientLogin', URL_ACCOUNT_DATA = 'https://www.google.com/analytics/feeds/accounts/default', URL_REPORT_DATA = 'https://www.google.com/analytics/feeds/data';
	const AUTH_TOKEN = '', PROFILE_ID = '';
	protected $startDate, $endDate, $authToken, $profileID;
	public $totalResults, $startIndex, $itemsPerPage, $aggregate;
	public function __construct() {
		$this->startDate = strtotime('first day');
		$this->endDate = time();
	}
	public function count() {
		return $this->totalResults;
	}
	protected static function request($url, Array $data = NULL, Array $header = NULL) {
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		if (count($data)>0) {
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		} else {
			$header[] = array('application/x-www-form-urlencoded');
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		
		$response = curl_exec($ch);
        $info = curl_getinfo($ch);
		
        curl_close($ch);
		if ($info['http_code'] == 200)
			return $response;
		else
			throw new \Exception('Not a valid response - '.$response, $info['http_code']);
		return $response;
	}
	protected function callAPI($url, Array $query = NULL) {
		return $this->request($url.(is_array($query) ? '?'.http_build_query($query) : ''), NULL, array('Authorization: GoogleLogin auth='.static::AUTH_TOKEN));
	}
	public function authenticateUser($email, $passwd) {
		$response = $this->request(static::URL_CLIENT_LOGIN, array('accountType' => 'GOOGLE', 'Email' => $email, 'Passwd' => $passwd, 'service' => 'analytics', 'source' => 'Chonoo-CMS-7.0.0'));
		preg_match('/Auth=(.*)/', $response, $matches);
		return $this->authToken = $matches[1];
	}
	public function getProfiles() {
		$xml = new \SimpleXMLElement($this->callAPI(static::URL_ACCOUNT_DATA), LIBXML_NOCDATA);
		$this->openSearch($xml, $ns = $xml->getNamespaces(1));
		$result = array();
		foreach ($xml->entry as $entry) {
			$row =& $result[];
			foreach ($entry->children($ns['dxp'])->property as $property)
				$row->{str_replace('ga:', '', $property->attributes()->name)} = (string)$property->attributes()->value;
			$row->title = (string)$entry->title;
			$row->updated = strtotime($entry->updated);
		}
		return $result;
	}
	
	/*
		@filter
			; => AND
			, => OR: Not use dimensions or metrics
			1 or 2 AND 3 or 4
			1 and 2 OR 3 and 4
			AND priority
	*/
	public function getReport($dimensions, $metrics = NULL, $sort = NULL, $filter = NULL, $start_date = NULL, $end_date = NULL, $start_index = 1, $max_results = \TablePagination::PER_PAGE) {
		$parameters = array('ids' => 'ga:'.static::PROFILE_ID, 'dimensions' => $this->prepare($dimensions), 'metrics' => $this->prepare($metrics), 'sort' => $this->prepare($sort));
		$parameters['filters'] = $this->processFilter($filter) ?: $filter;
		# $parameters['segment'] = $segment; // gaid::10 OR dynamic::ga:medium==referral
		$parameters['start-date'] = date('Y-m-d', $start_date ?: $this->startDate);
		$parameters['end-date'] = date('Y-m-d', $end_date ?: $this->endDate);
		$parameters['start-index'] = max(static::START_INDEX, min(static::MAX_RESULTS, $start_index));
		$parameters['max-results'] = max(0, min(static::MAX_RESULTS, $max_results));    
		$parameters['prettyprint'] = static::DEVELOPMENT_MODE ? 'true' : 'false';
		
		$xml = new \SimpleXMLElement($this->callAPI(static::URL_REPORT_DATA, $parameters), LIBXML_NOCDATA);
		$this->openSearch($xml, $ns = $xml->getNamespaces(1));
		foreach ($xml->children($ns['dxp'])->aggregates->children($ns['dxp'])->metric as $metric)
			$this->aggregate->{str_replace('ga:', '', $metric->attributes()->name)} = (float)$metric->attributes()->value;
		$result = array();
		foreach ($xml->entry as $entry) {
			$row =& $result[];
			foreach ($entry->children($ns['dxp'])->dimension as $dimension)
				$row->{str_replace('ga:', '', $dimension->attributes()->name)} = (string)$dimension->attributes()->value;
			foreach ($entry->children($ns['dxp'])->metric as $metric)
				$row->{str_replace('ga:', '', $metric->attributes()->name)} = (float)$metric->attributes()->value;
			$row->title = (string)$entry->title;
			$row->updated = strtotime($entry->updated);
		}
		return $result;
	}
	protected function openSearch($xml, $ns) {
		foreach ($xml->children($ns['openSearch']) as $key => $value)
			$this->$key = (int)$value;
	}
	/* TODO: Array sort */
	protected function prepare($value) {
		return is_array($value) ? 'ga:'.implode(',ga:', $value) : trim(preg_replace('/(-)?([A-z]+)(,)?/', '$1ga:$2$3', $value), ',');
	}
	protected static function processFilter($filter) {
		$valid_operators = '(!~|=~|==|!=|>|<|>=|<=|=@|!@)';
		
		$filter = preg_replace('/\s\s+/', ' ', trim($filter)); //Clean duplicate whitespace
		$filter = str_replace(array(',', ';'), array('\,', '\;'), $filter); //Escape Google Analytics reserved characters
		$filter = preg_replace('/(&&\s*|\|\|\s*|^)([a-z]+)(\s*' . $valid_operators . ')/i', '$1ga:$2$3', $filter); //Prefix ga: to metrics and dimensions
		$filter = preg_replace('/[\'\"]/i', '', $filter); //Clear invalid quote characters
		$filter = preg_replace(array('/\s*&&\s*/', '/\s*\|\|\s*/', '/\s*' . $valid_operators . '\s*/'), array(';',',','$1'), $filter); //Clean up operators
		
		return $filter;
	}
}
