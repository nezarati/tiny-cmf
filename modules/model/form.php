<?
namespace Model;
class Form extends Model {
	public static $_schema = array(
		'description' => 'Cache table for the form system to store recently built ...',
		'fields' => array(
			'token' => array(
				'type' => 'string',
				'length' => 255,
				'not null' => TRUE,
				'description' => 'Primary Key: Unique cache ID.',
			),
			'data' => array(
				'type' => 'string', # longblob
				'length' => \Model\Model::LONG_TEXT,
				'description' => 'A collection of data to cache.',
			),
			'expire' => array(
				'type' => 'timestamp',
				'description' => 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
				'default' => 0,
			),
			'created' => array(
				'type' => 'timestamp',
				'description' => 'A Unix timestamp indicating when the cache entry was created.',
				'default' => 0,
			),
		),
		'indexes' => array(
			'expire' => array('expire'),
		),
		'primary key' => array('token'),
	);
}