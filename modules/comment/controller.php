<?
namespace Comment;
final class Controller extends \Controller {
	public function __construct() {
		\Service::exists('comment', $_REQUEST['arg']['service']) && define('SERVICE_COMMENT', \Service::identifier('comment', $_REQUEST['arg']['service']));
		parent::__construct();
		$this->view->pageTitle[] = __('Comment');
	}
	
	protected $permission = array('preferences' => 'administer comments', 'add' => 1, 'archive' => 'administer comments', 'edit' => 'administer comments', 'delete' => 'administer comments', 'status' => 'administer comments', 'index' => 'access comments');
	protected function preferences($A, $D) {
		is_numeric($A->id) && defined('SERVICE_COMMENT') || die;
		$cfg = \Registry::getInstance('comment', SERVICE_COMMENT);
		if ($this->callback) {
			foreach ($cfg as $key => $value)
				$cfg->$key = $D->$key;
			\View::status(__('The configuration options have been saved.'));
			return TRUE;
		}
		
		$form = new \Form;
		
		$form->data->attr(array('type' => 'fieldset'));
		$form->data->order->attr(array('label' => __('Ordering'), 'value' => $cfg->order, 'type' => 'radio', 'options' => array('created' => __('Created'))));
		$form->data->sort->attr(array('label' => __('Sort by'), 'value' => $cfg->sort, 'type' => 'sort'));
		$form->data->perPage->attr(array('label' => __('Number Of Items Per Page'), 'value' => $cfg->perPage, 'type' => 'limit'));
		# $form->data->status->attr(array('label' => __('Registration by user'), 'value' => $cfg->status, 'type' => 'radio', 'options' => array(\Model\Comment::STATUS_NOT_PUBLISH => __('Not published'), \Model\Comment::STATUS_PUBLISH => __('Published'), \Model\Comment::STATUS_PRIVATE => __('Private'))));
		
		$form->button->submit['value'] = __('Save configuration');
		return $form;
	}
	protected function index($A) {
		is_numeric($A->id) && defined('SERVICE_COMMENT') || die;
		$A->node = $A->id;
		
		$cfg = \Registry::getInstance('comment', SERVICE_COMMENT);
		$query = \Model\Comment::all()->fields('id', 'created', 'name', 'mail', 'homepage', 'content', 'parent', 'node')->filter('service', SERVICE_COMMENT)->filter('node', $A->node)->filter('status', \Model\Comment::STATUS_PUBLISH)->orderBy($cfg->order, $cfg->sort)->orderBy('__parent')->extend('TablePagination')->map(function($model) use($A) {
			$model->email = md5($model->mail);
			$model->content = \Hook::filter('HTML', nl2br(htmlspecialchars($model->content)));
			if ($model->parent)
				$model->content = '<span class="quote">'.nl2br(htmlspecialchars(API::load($model->parent, SERVICE_COMMENT)->content)).'</span>'.$model->content;
			$model->detail = __(':name about :time seid:', array('name' => $model->homepage ? '<a href="'.$model->homepage.'" rel="nofollow">'.$model->name.'</a>' : $model->name, 'time' => format_date($model->created, 'period')));
			$model->action = '<ul class="comment-img"><li class="agree first" title="'.__('Agree').'" onclick="$.Rate.vote(\'comment\', '.$model->id.', +1)"></li><li class="point" title="'.__('Point').'" id="rate-comment-'.$model->id.'">'.\Model\Rate::points($model->id).'</li><li class="disagree" title="'.__('Disagree').'" onclick="$.Rate.vote(\'comment\', '.$model->id.', -1)"></li><li class="quote last" title="'.__('Reply').'" onclick="$.Comment.add(\''.$A->service.'\', '.$model->node.', '.$model->id.')"></li></ul>';
		});
		$_REQUEST['count'] = $cfg->perPage;
		
		$tpl = new \Template(\Model\Layout::block('comment'));
		$tpl->comment = array(
			'service' => $A->service,
			'node' => $A->node,
			'pagination' => ($total = $query->count()) ? new \Pagination($total, '', $cfg->perPage, 0, 'comment-'.$A->service.'-'.$A->node) : '',
			'entries' => $total ? $query : __('No one has left a comment yet. Be the first!'),
		);
		return $tpl;
	}
	protected function add($A, $D) {
		is_numeric($A->id) || die;
		if (!\User\API::access('post comments') && !\User\API::access('post comments without approval'))
			return ACCESS_DENIED;
		$A->node = $A->id;
		$A->parent = (int)$A->parent;
		
		if ($this->callback) {
			$akismet = new \API\Akismet(\Registry::getInstance()->home);
			$akismet->comment_author = $D->name;
			$akismet->comment_author_email = $D->mail;
			$akismet->comment_author_url = $D->homepage;
			$akismet->comment_content = $D->content;
			if ($akismet->isSpam()) {
				\View::error(__('Your comment detected as spam.'));
				return FALSE;
			}
			$D->node = $A->node;
			$D->parent = (int)$D->parent;
			$D->mail = strToLower($D->mail);
			$D->homepage = $D->homepage == 'http://' ? '' : trim($D->homepage);
			$D->created = GMT;
			$D->user = USER_ID;
			$D->hostname = IP;
			$D->status = \User\API::access('post comments without approval') ? 1 : 0;
			return (bool) API::save($D);
		}
		if (USER_ID) {
			$data = \User\API::load();
			$name = $data->name;
			$mail = $data->mail;
		} else
			$name = $mail = '';
		
		$form = new \Form;
		$form->data->attr(array('type' => 'fieldset', 'legend' => __('Leave a Reply')));
		$form->data->name->attr(array('validator' => array('validate_match', REGEX_NAME, __(':name field is required.', array('!name' => __('Name')))), 'label' => __('Name'), 'value' => $name));
		$form->data->mail->attr(array('dir' => 'ltr', 'validator' => '\User\validate::mail', 'label' => __('E-mail address'), 'tip' => __('will not be published'), 'value' => $mail));
		$form->data->homepage->attr(array('dir' => 'ltr', 'label' => __('Website'), 'value' => 'http://', 'validator' => 'validate_homepage'));
		#$form->data->subject->attr(array(/*'validator' => array('validate_match', REGEX_TITLE, __(':name field is required.', array('!name' => __('Subject')))), */'label' => __('Subject'))); # TODO
		$form->data->content->attr(array('type' => 'textarea', 'class' => 'full', 'rows' => 10, 'validator' => array('validate_match', REGEX_CONTENT, __(':name field is required.', array('!name' => __('Content')))), 'label' => __('Content')));
		$form->button->submit['value'] = __('Submit Comment');
		$form->button->reset->attr(array('value' => __('Reset'), 'type' => 'reset'));
		$form->button->parent->attr(array('value' => $A->parent, 'type' => 'hidden', 'name' => 'data[parent]'));
		return $form;
	}
	protected function archive($A) {
		$A->node = (int) $A->id;
		$query = \Model\Comment::all();
		if ($A->node)
			$query->filter('node', $A->node);
		defined('SERVICE_COMMENT') ? $query->filter('service', SERVICE_COMMENT) : $query->filter('service', array_keys(\Service::required('comment')), 'in');
		return new \TableSelect(NULL, array('author' => array('data' => __('Author')), 'content' => array('data' => __('Comment')), 'created' => array('data' => __('Time'), 'field' => 'created', 'sort' => 'desc')), $query->map(function($doc) {
			$doc->created = format_date($doc->created, 'short');
			$doc->content = \Hook::filter('HTML', nl2br(htmlspecialchars($doc->content)), $service->name);
			
			$referrer = call_user_func('\\'.$service->name.'\API::permalink', array('id' => $doc->node));
			$doc->author = $doc->name.'<br /><a href="mailto:'.$doc->mail.'">'.$doc->mail.'</a><br /><a href="'.$doc->homepage.'" target="_blank">'.preg_replace('#^.*?://(?:www\.)?#', '', trim($doc->homepage, '/')).'</a><br />'.$doc->hostname.'<br /><a href="'.$referrer.'" rel="ajax">'.trim($referrer, '/').'</a>';

			$doc->actions['edit'] = array('href' => '/comment/'.$doc->id.'/'.$service->name.'/edit', 'type' => 'edit');
			$doc->actions['status'] = $doc->status == \Model\Comment::STATUS_NOT_PUBLISH ? array('href' => '', 'class' => 'IMG Enable', 'title' => __('Publish'), 'onclick' => '$.Comment.status(this.parentNode.parentNode, \''.$service->name.'\', '.$doc->id.', '.\Model\Comment::STATUS_PUBLISH.')') : ($doc->status == \Model\Comment::STATUS_PUBLISH ? array('class' => 'IMG Disable', 'title' => __('Private'), 'onclick' => '$.Comment.status(this.parentNode.parentNode, \''.$service->name.'\', '.$doc->id.', '.\Model\Comment::STATUS_PRIVATE.')') : array());
			\Hook::call('comment_action', 'comment', $doc);
			$doc->actions['delete'] = array('href' => '/comment/'.$doc->id.'/'.$service->name.'/delete', 'type' => 'delete');
		}), __('No comments available.'));
	}
	protected function edit($A, $D) {
		is_numeric($A->id) && defined('SERVICE_COMMENT') || die; # TODO
	}
	protected function delete($A) {
		is_numeric($A->id) && defined('SERVICE_COMMENT') || die;
		return (bool) API::delete($A->id);
	}
	protected function status($A) {
		is_numeric($A->id) && is_numeric($A->flag) && defined('SERVICE_COMMENT') || die;
		API::save((object) array('id' => $A->id, 'status' => $A->flag));
		return TRUE;
	}
}