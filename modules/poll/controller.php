<?
namespace Poll;
class Controller extends \Controller {
	private static function form($data = NULL) {
		$form = new \Form;
		$form->data['type'] = 'fieldset';
		$form->data->question->attr(array('label' => __('Question'), 'value' => $data->question, 'validator' => array('validate_match', REGEX_TITLE, __(':name field is required.', array('!name' => __('Question'))))));
		$form->data->url->attr(array('label' => __('URL'), 'value' => $data->url ?: 'http://', 'dir' => 'ltr', 'validator' => array('validate_match', '#^https?\://([A-z\-0-9]+\.)+[a-z]{2,4}/?#', __(':name field is not valid.', array('!name' => __('URL'))))));
		$form->data->description->attr(array('label' => __('Description'), 'value' => $data->description, 'type' => 'textarea', 'rows' => 3));
		$form->data->status->attr(array('label' => __('Status'), 'value' => isset($data->status) ? $data->status : 1, 'type' => 'radio', 'options' => array(__('Inactive'), __('Active'))));
		$form->data->hit->attr(array('label' => __('Hit'), 'value' => (int) $data->hit, 'class' => 'tiny', 'dir' => 'ltr', 'validator' => array('validate_match', REGEX_INTEGER, __(':name field is not valid.', array('!name' => __('Hit'))))));
		$form->button->submit['value'] = __('Save');
		return $form;
	}
	protected $permission = array('view' => 1, 'archive' => 'access content', 'edit' => 'access content', 'delete' => 'access content', 'vote' => 'vote on polls', 'result' => 'inspect all votes');
	protected function archive() {
		return new \TableSelect(NULL, array('question' => array('data' => __('Question'), 'field' => 'question'), 'expire' => array('data' => __('Expire'), 'field' => 'expire'), 'status' => array('data' => __('Status'), 'sort' => 'asc', 'field' => 'status'), 'operations' => array('data' => __('Operations'))), \DB::select('poll', 'p')->fields('p', array('id', 'expire', 'question'))->addExpression('if((expire = 0 || expire>unix_timestamp()) && status = 1, 1, 0)', 'status')->map(function($model) {
			$model->status = $model->status ? __('Active') : __('Closed');
			$model->expire = $model->expire ? format_date($model->expire) : __('unlimited');
			$model->operations['edit'] = array('type' => 'edit', 'href' => '/poll/'.$model->id.'/edit');
			$model->operations['delete'] = array('type' => 'delete', 'href' => '/poll/'.$model->id.'/delete');
			$model->operations = \View::operations($model->operations);
		}), __('No polls yet.'));
	}
	protected function edit() {
		is_numeric($A->id) || die;
		if ($this->callback) {
			$D->id = $A->id;
			return (bool) API::save($D);
		}
		return $this->form($A->id ? API::load($A->id) : new \StdClass);
	}
	protected function delete() {
		return TRUE;
	}
	public function index($A) {
		is_extension($A->group) && is_numeric($A->id) || die;
		$g = $A->group;
		$k = $A->id;
		$this->die = 1;
		if (TMP::get($c = self::cache($g, $k).'block.html', $t))
		return $t;
		$T = new Template('poll-content');
		if ($d = DB::_query('select * from #__poll where bind=%d && `group`=%d && id=%d;', Bind, Group::identifier(self::KEY, $g), $k)->fetch_object()) {
			$i = 0;
			$o = array();
			$m = $d->multiple>1 ? 'checkbox' : 'radio';
			foreach (unserialize($d->option) as $t => $v)
			$o[] = array('title' => '<input type="'.$m.'" id="poll-'.$g.'-'.$k.'-'.$i.'" name="data[option][]" value="'.$i.'" /><label for="poll-'.$g.'-'.$k.'-'.$i++.'">'.$t.'</label>');
			$T->block('poll', array('title' => $d->title, 'submit' => '<input type="submit" name="submit" class="button" value="[value]" />', 'result' => Core::permalink(array('ext' => self::KEY, 'act' => 'result', 'group' => $g, 'key' => $d->key, 'id' => $d->id)), 'answer' => $o), '<form method="post" onsubmit="return _$(this)" id="poll-'.$g.'-'.$d->id.'" action="'.Core::permalink(array('ext' => self::KEY, 'act' => 'vote', 'group' => $g, 'key' => $d->key, 'id' => $d->id)).'">', '</form>');
			return TMP::set($c, $T->compile());
		}
	}
	/*private function form($G='', $K='', $Q='', $O=array(), $A='', $M=1, $P=1, $E=0) {
		$E || $E = GMT+60*60*24*30;
		$this->validate = array('answer' => 'Poll::validateAnswer', 'option' => 'Poll::validateOption', 'group' => 'is_string', 'key' => array('validate_match', Regular_Integer, PollLang::VALIDATE_KEY), 'title' => array('validate_match', Regular_Title, PollLang::VALIDATE_QUESTION), 'multiple' => 'Poll::validateMultiple', 'publish' => array('validate_match', Regular_Integer, PollLang::VALIDATE_STATUS), 'expire' => array('validate_timestamp', PollLang::VALIDATE_EXPIRE));
		foreach ($O as $t => $v)
		$o .= '
<tr>
	<td>
		<input name="data[option][title][]" value="'.$t.'" style="width: 100%" />
	</td>
	<td>
		<input name="data[option][vote][]" value="'.$v.'" style="width: 100%" dir="ltr" />
	</td>
	<td>
		<input type="checkbox" name="data[option][delete][]" value="1" />
	</td>
</tr>
				';
		return HTML::form(array(CoreLang::SETTING => array(CoreLang::GROUP => array('name' => 'group', 'type' => 'radio', 'option' => RGT::get(RGT::GROUP, self::KEY), 'value' => $G, 'required' => 1), CoreLang::KEY => array('name' => 'key', 'dir' => 'ltr', 'value' => $K, 'class' => 'tiny', 'required' => 'integer'), PollLang::QUESTION => array('name' => 'title', 'value' => $Q, 'class' => 'medium', 'required' => 1), PollLang::MAX_OPTION => array('name' => 'multiple', 'value' => $d->multiple, 'value' => $M, 'type' => 'select', 'option' => array_combine(range(1, 10), range(1, 10))), PollLang::STATUS => array('name' => 'publish', 'type' => 'radio', 'option' => array(1 => PollLang::OPEN, 0 => PollLang::CLOSE, 2 => PollLang::PRIVATE_RESULT), 'value' => $P), PollLang::EXPIRE => HTML::timestamp('expire', $E)), PollLang::OPTION => '<table id="poll" align="center"><thead><tr><th style="width: 80%">'.PollLang::OPTION.'</th><th>'.PollLang::COUNT.'</th><th style="width: 30px">'.CoreLang::DELETE.'</th></tr></thead><tbody>'.$o.'</tbody></table>'), array('submit' => CoreLang::UPDATE, 'button' => array('value' => PollLang::ADD_OPTION, 'onclick' => "_$('poll', [{tag: 'input', name: 'answer', style: 'width: 100%'}, {tag: 'input', style: 'width: 100%', disabled: 1}, {tag: 'input', type: 'checkbox', disabled: 1}], 0, 'data')"), 'explain' => PollLang::FORM_EXPLAIN), array('data[answer][]' => '', 'data[option][title][]' => '', 'data[option][vote][]' => '', 'data[option][delete][]' => 1));
	}*/
	private static function option($o, $a) {
		$r = array();
		foreach ($o->title as $k => $t)
			$o->delete->$k || ($r[$t] = $o->vote->$k);
		foreach ($a as $t)
			$t && ($r[$t] = 0);
		return serialize($r);
	}
	public function add($A, $D, $S) {
		return $S && DB::insert(self::Table, array('bind' => Bind, 'group' => Group::identifier(self::KEY, $D->group), 'key' => $D->key, 'title' => $D->title, 'multiple' => $D->multiple, 'publish' => $D->publish, 'create' => GMT, 'expire' => Lib::timestamp($D->expire), 'option' => self::option($D->option, $D->answer))) && Hook::apply('poll-add') ? CoreLang::SUCCESS : $this->template();
	}
	/*public function archive($A, $D, $S) {
		if ($_GET['mod'] == 'add')
		return $this->add($A, $D, $S);
		for (is_extension($_GET['mod']) && ($A->group = $_GET['mod']), $q=DB::_query('select id, title, `key` from #__poll'.($w = ' where bind='.Bind.' && `group`='.Group::identifier(self::KEY, $A->group).(is_numeric($A->id) ? ' && `key`='.$A->id : '')).DB::limit()); $d=$q->fetch_object(); $r.=Lib::item($d->id, array(Core::permalink(array('ext' => self::KEY, 'act' => 'result', 'group' => $A->group, 'id' => $d->id, 'key' => $d->key)) => array('onclick' => 'Poll.result(this)', 'class' => 'IMG Preference', 'title' => CoreLang::RESULT), Core::permalink(array('ext' => self::KEY, 'act' => 'edit', 'group' => $A->group, 'id' => $d->id)) => 'edit', Core::permalink(array('ext' => self::KEY, 'act' => 'delete', 'group' => $A->group, 'id' => $d->id)) => 'delete'), $d->title));
		if ($r) {
			$this->pagination = new Pagination(DB::query('select count(*) from {poll}'.$w)->result());
			return $r;
		}
		return CoreLang::NOT_FOUND;
	}
	public function edit($A, $D) {
		is_numeric($A->id) && is_extension($A->group) || die;
		$w = 'bind='.Bind.' && `group`='.Group::identifier(self::KEY, $A->group).' && id='.$A->id;
		if ($_POST['submit']) {
			TMP::del(self::cache($A->group, $A->id));
			DB::update(self::Table, array('title' => $D->title, 'multiple' => $D->multiple, 'publish' => $D->publish, 'expire' => Lib::timestamp($D->expire), 'option' => self::option($D->option, $D->answer)), $w);
			return CoreLang::SUCCESS;
		}
		$d = DB::_query('select * from #__poll where '.$w)->fetch_object();
		return $this->template(Group::key(self::KEY, $d->group), $d->key, $d->title, unserialize($d->option), $d->answer, $d->multiple, $d->publish, $d->expire);
	}
	public static function delete($A) {
		is_extension($A->group) && is_numeric($A->id) || die;
		TMP::del(self::cache($A->group, $A->id));
		DB::delete(self::Table, 'bind='.Bind.' && `group`='.Group::identifier(self::KEY, $A->group).' && id='.$A->id);
		return CoreLang::SUCCESS;
	}*/
	public static function vote($A, $D) {
		is_extension($A->group) && is_numeric($A->id) || die;
		TMP::del(self::cache($A->group, $A->id));
		$w = 'bind='.Bind.' && `group`='.Group::identifier(self::KEY, $A->group).' && id='.$A->id;
		$d = DB::_query('select * from #__poll where '.$w)->fetch_object();
		$o = array();
		if (is_object($D->option))
		foreach ($D->option as $k => $v)
		$d->multiple <= 1 ? $o[] = (int)$v : $v == 1 && ($o[] = (int)$k);
		if (isset($_COOKIE[self::KEY][$A->group][$A->id]))
			$e = PollLang::VOTED;
		else if (empty($o))
			$e = PollLang::SELECT_OPTION;
		else if ($d->expire<GMT)
			$e = PollLang::EXPIRED;
		else if ($d->publish == 0)
			$e = PollLang::CLOSED;
		else if ($d->multiple<count($o))
			$e = sprintf(PollLang::VALIDATE_MAX_OPTION, $d->multiple);
		if ($e)
			return '-'.$e;
		$i = 0;
		foreach (array_keys($O = unserialize($d->option)) as $k)
			in_array($i++, $o) && $O[$k]++;
		DB::update(self::Table, array('option' => serialize($O))+(USER_ID ? array('user' => $d->user.USER_ID.',') : array('ip' => $d->ip.IP.',')), $w);
		Cookie::set(self::KEY.'['.$A->group.']['.$A->id.']', implode(',', $o));
		return Lib::script('Poll.result({href: "/'.Core::permalink(array('ext' => self::KEY, 'act' => 'result', 'group' => $A->group, 'id' => $A->id)).'", title: "'.sprintf(PollLang::TITLE, $d->title).'"});_$("poll-'.$A->group.'-'.$A->id.'").innerHTML = "'.PollLang::SUCCESS.'";');
	}
	public function result($A) {
		is_extension($A->group) && is_numeric($A->id) || die;
		if (TMP::get($this->cache = self::cache($A->group, $A->id).'result.html', $t)) {
			$this->cache = null;
			return $t;
		}
		$T = new Template('poll-result');
		$d = DB::query('select * from {poll} where bind=%d && `group`=%d && id=%d;', Bind, Group::identifier(self::KEY, $A->group), $A->id)->fetch_object();
		$d->publish != 2 || User::permission(self::Access) || die(PollLang::VALIDATE_PRIVATE_RESULT);
		$d->option = unserialize($d->option);
		foreach ($d->option as $v)
		$c += $v;
		$o = array();
		$n = $c ? $c : 1;
		foreach ($d->option as $t => $v)
		$o[] = array('title' => $t, 'vote' => $v, 'percent' => (int)($v*100/$n));
		$T->block('poll_result', array('title' => $d->title, 'date' => Hook::apply('date', $d->create), 'time' => Hook::apply('time', $d->create), 'vote' => $c, 'answer' => $o));
		return $T->compile();
	}
}