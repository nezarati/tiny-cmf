<?
namespace Appearance;
class Controller extends \Controller {
	const KEY = 'theme', Access = 'theme', Table = 'theme';
	
	public static function clearCache() {
		TMP::del(self::KEY);
	}
	public static function language($S) {
		return preg_replace('#\{const:language.([A-z]+) value=\(([A-Z_]+)\)\}#e', '$1Lang::$2', $S);
	}
	public static function constant($S) {
		return preg_replace_callback('#\{const:(id|cms|url)\.([A-z_0-9]+)\}#', 'Theme::_constant', $S);
	}
	private static function _constant($N) {
		switch ($N[2]) {
			case 'navigation':
			case 'sidebar':
			case 'content':
			case 'loading':
			case 'toggle':
				return $N[2];
			case 'title':
				return TS_Title;
			case 'base_url':
				return TS_ShortURL;
			case 'url':
			case 'site':
				return TS_URL;
			case 'description':
				return TS_Description;
			case 'head':
				return Hook::apply($N[2]);
			case 'direction':
				return TS_Language == 'fa' ? 'rtl' : 'ltr';
			case 'profile':
				return 'profile.html';
			case 'contact':
				return Core::permalink(array('ext' => 'contact', 'act' => 'view'));
			case 'home_page':
				return TS_HOME_PAGE;
		}
	}
	protected $permission = array('add' => self::Access, 'set' => self::Access, 'edit' => self::Access, 'delete' => self::Access, 'get' => 1);
	private function template($N='', $M='', $B='', $C='', $S='', $P=0, $I='') {
		$this->validate = array('name' => array('validate_match', Regular_Title, ThemeLang::ERROR_NAME), 'main' => array('validate_match', Regular_Content, ThemeLang::ERROR_MAIN), 'block' => 'is_string', 'style' => 'is_string', 'script' => 'is_string', 'public' => 'is_int', 'image' => 'is_string');
		return HTML::form(array(ThemeLang::TITLE => array(ThemeLang::NAME => array('name' => 'name', 'value' => $N, 'class' => 'medium', 'required' => 1), ThemeLang::MAIN => array('name' => 'main', 'type' => 'textarea', 'dir' => 'ltr', 'value' => $M, 'rows' => 10, 'class' => 'full', 'required' => 1), ThemeLang::BLOCK => array('name' => 'block', 'type' => 'textarea', 'dir' => 'ltr', 'value' => $B, 'rows' => 10, 'class' => 'full'), ThemeLang::STYLE => array('name' => 'style', 'dir' => 'ltr', 'type' => 'textarea', 'value' => $C, 'rows' => 10, 'class' => 'full'), ThemeLang::SCRIPT => array('name' => 'script', 'dir' => 'ltr', 'type' => 'textarea', 'value' => $S, 'rows' => 10, 'class' => 'full'), ThemeLang::GENERAL => array('name' => 'public', 'type' => 'checkbox', 'value' => $P, 'explain' => ThemeLang::EXPLAIN_PUBLIC), CoreLang::IMAGE => array('name' => 'image', 'value' => $I, 'dir' => 'ltr', 'class' => 'full'))));
	}
	private static function permission($I) {
		return is_numeric($I) && DB::_query('select count(*) from #__theme where id=%d && (bind=%d || public=1);', $I, Bind)->result();
	}
	public function set($A) {
		if ($this->permission($A->id)) {
			$d = Lib::CFG('core');
			$d->theme = $A->id;
			Lib::CFG('core', $d);
			Hook::apply('theme-set');
			return CoreLang::SUCCESS.Lib::script('document.location.reload();');
		}
		for ($i=0, $q=DB::_query('select * from #__theme where public=1;'); $d=$q->fetch_object(); $r.=($i%2?'':'</tr><tr>').'<td>'.($d->image ? '<img src="'.$d->image.'" />' : '').'<br /><center>'.$d->name.'</center><br />'.($d->bind == 1 || $d->bind == 352 ? ThemeLang::STATE_VALID : '<span style="color: red">'.ThemeLang::STATE_INVALID.'</span>').'<br /><a href="'.Core::permalink(array('ext' => self::KEY, 'act' => 'set', 'id' => $d->id)).'" class="IMG Preference" title="'.sprintf(ThemeLang::SET_DEFAULT, $d->name).'" onclick="return Theme.set(this, \''.$d->image.'\')"></a><a href="'.Core::permalink(array('ext' => self::KEY, 'act' => 'add', 'id' => $d->id)).'" class="IMG Add" title="'.ThemeLang::ADD_TO_ARCHIVE.'" onclick="return _$(this)"></a></td>', $i++);
		return '<table style="width: 100%"><tr><tbody>'.$r.'</tr></tbody></table>';
	}
	public function add($A, $D) {
		if ($this->permission($A->id)) {
			DB::insert(self::Table, array('bind' => Bind, 'create' => GMT)+DB::_query('select name, main, block, style, script, image from #__theme where id=%d;', $A->id)->fetch_array());
			return CoreLang::SUCCESS;
		}
		# $D->public = 0;
		return $_POST['submit'] && DB::insert(self::Table, array('bind' => Bind, 'create' => GMT)+Lib::O2A($D)) ? CoreLang::SUCCESS : $this->template();
	}
	public function edit($A, $D) {
		if (is_numeric($A->id)) {
			if ($_POST['submit']) {
				# $D->public = 0;
				DB::update(self::Table, $D, 'bind='.Bind.' && id='.$A->id);
				TS_Theme == $A->id && Hook::apply('theme-set');
				return CoreLang::SUCCESS;
			}
			$d = DB::_query('select * from #__theme where bind=%d && id=%d;', Bind, $A->id)->fetch_object();
			return $this->template($d->name, $d->main, $d->block, $d->style, $d->script, $d->public, $d->image);
		}
		for ($q=DB::_query('select id, image, name from #__theme where bind=%d;', Bind); $d=$q->fetch_object(); $r.=Lib::item($d->id, array(Core::permalink(array('ext' => self::KEY, 'act' => 'set', 'id' => $d->id)) => 'default', Core::permalink(array('ext' => self::KEY, 'act' => 'edit', 'id' => $d->id)) => 'edit', Core::permalink(array('ext' => self::KEY, 'act' => 'delete', 'id' => $d->id)) => 'delete'), $d->name.($d->id == TS_Theme ? ' <span class="IMG Enable"></span>' : '').($d->image ? '<img src="'.$d->image.'" style="display: block" />' : '')));
		return $r ? $r : CoreLang::NOT_FOUND;
	}
	public static function delete($A) {
		is_numeric($A->id) || die;
		if (TS_Theme == $A->id)
		return ThemeLang::CAN_NOT_DELETE_DEFAULT;
		DB::delete(self::Table, 'bind='.Bind.' && id='.$A->id);
		return CoreLang::SUCCESS;
	}
	public function get() {
		$this->die = 1;
		switch ($_GET['mod']) {
			case 'js':
				$this->view->header['content-type'] = 'text/javascript';
				return Compress::JS(self::value('script'), TMP_DIR.Bind.'/'.self::KEY.'/');
			case 'css':
				$this->view->header['content-type'] = 'text/css';
				return Compress::CSS(self::value('style'), TMP_DIR.Bind.'/'.self::KEY.'/');
		}
	}
	public static function &value($S) {
		if (TMP::get($p = self::KEY.'/source/'.$S, $t))
			return $t;
		if (in_array($S, array('main', 'block', 'style', 'script')))
			return TMP::set($p, self::constant(self::language(DB::_query('select `%s` from #__theme where id=%d && (public=1 || bind=%d)', $S, TS_Theme, Bind)->fetchCol())));
		return TMP::set($p, self::constant(self::language(($v = SimpleXML_Load_String(self::value('block'))->$S) ? $v : SimpleXML_Load_File(EXT_DIR.'ext/theme/template.xml')->$S)));
	}
}