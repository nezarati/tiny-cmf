<?
namespace Model;
class I18n extends Model {
	public static $_schema = array(
		'fields' => array(
			'module' => array(
				'type' => 'integer',
				'length' => 10,
				'unsigned' => TRUE,
				'not null' => TRUE,
			),
			'language' => array(
				'type' => 'string',
				'length' => 5,
				'not null' => TRUE,
			),
			'msgid' => array(
				'type' => 'string',
				'length' => 255,
				'not null' => TRUE,
			),
			'msgstr' => array(
				'type' => 'string',
				'length' => 255,
				'not null' => TRUE,
			),
		),
		'indexes' => array(
			'module' => array('module'),
			'language' => array('language'),
			'msgid' => array('msgid'),
		),
		'primary key' => array('module', 'language', 'msgid'),
	);

}