<?
namespace Model;
class Rate extends Model {
	public static $_schema = array(
		'fields' => array(
			'service' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'node' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'user' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'score' => array(
				'type' => 'integer',
				'length' => 2,
				'not null' => TRUE,
			),
			'hostname' => array(
				'type' => 'string',
				'length' => 128,
				'not null' => TRUE,
			),
			'created' => array(
				'type' => 'timestamp',
				'not null' => TRUE,
			),
		),
		'indexes' => array(
			'user' => array('service', 'node', 'user'),
			'hostname' => array('service', 'node', 'hostname'),
			'created' => array('service', 'node', 'created'),
		),
	);
	
	public static function points($node, $service = SERVICE_RATE_COMMENT) {
		# $points = \DB::query('select ifnull(sum(score), 0) from {rate} where service = :0 && node = :1', array($service, $node))->fetchField();
		$points = \Model\Rate::group(array('service' => 1, 'node' => 1), array('points' => 0), new \MongoCode('function(doc, out) {out.points += doc.score}'), array('condition' => array('service' => (int)$service, 'node' => (int)$node)));
		$points = (int)$points['retval'][0]['points'];
		return $points >= 0 ? '<span style="color: #2D944A">'.($points ? '+'.$points : $points).'</span>' : '<span style="color: #B01616">'.$points.'</span>';
	}
	public static function scores($node, $service = SERVICE_RATE_ARTICLE) {
		# return \DB::query('select score, count(score) count from {rate} where service = :0 && node = :1 group by score', array($service, $node))->fetchAllKeyed('score', 'count');
		$group = \Model\Rate::group(array('score' => 1), array('count' => 0), new \MongoCode('function(doc, out) {out.count++}'), array('condition' => array('service' => (int)$service, 'node' => (int)$node)));
		$result = array();
		foreach ($group['retval'] as $doc)
			$result[$doc->score] = $doc->count;
		return $result;
	}
	public static function average($node, $service = SERVICE_RATE_ARTICLE) {
		$total = 0;
		foreach (self::points($node, $service) as $score => $count)
			$total += ($score+1)*$count;
		return $total/($score+1);
	}
}