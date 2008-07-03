<?
namespace Search;
class Controller extends \Controller {
	protected $permission = array('index' => 'search content');
	protected function index($A, $D) {
		$this->view->pageTitle[] = __('Search');
		
		if ($A->keyword) {
			$D->query = $A->keyword;
			$D->area = $A->area;
			$this->callback = TRUE;
		} else
			$A->keyword = $D->query;
		
		if ($this->callback) {
			$this->view->pageTitle[] = __('Search Result');
			define('RGT_SEARCH_PERPAGE', \Registry::getInstance('search')->perPage); # TODO
			if ($D->match == 'exact') {
				$searchwords = array($D->query);
				$needle = $D->query;
			} else {
				$searchwords = preg_split('/\s+/', $D->query);
				$needle = $searchwords[0];
			}
			$tpl = new \Template(\Model\Layout::block('search'));
			$tpl->register('loop', 'search', function($attr, $val) use($D, $A, $searchwords, $needle) {
				$output = '';
				foreach ((array)$D->area as $module) {
					if (\Service::exists($module))
						continue;
					$tpl = new \TemplateEngine('search-block', $val);
					$result = call_user_func('\\'.ucfirst($module).'\API::search', $D->query, $D->match, $D->order);
					$tpl->register('loop', 'result', function($attr, $val) use($result, $searchwords, $needle) {
						$output = '';
						if ($result instanceof \Iterator)
							$result = iterator_to_array($result);
						foreach (array_slice($result, $_REQUEST['offset'], RGT_SEARCH_PERPAGE) as $row) {
							$row->summary = preg_replace('#('.implode('|', array_map('preg_quote', array_unique($searchwords))).')#iu', '<span class="highlight">\0</span>', \Search\API::prepareSearchContent($row->content, 200, $needle));
						}
						return $output ?: __('No results were found.');
					});
					'pagination' => ($total = count($result)) ? new \Pagination($total, '/search?arg[area]='.$module.'&arg[keyword]='.urlencode($A->keyword).'&offset=%7Boffset%7D', RGT_SEARCH_PERPAGE, 0, 'search-'.$module) : '';
					'search.total' => $total;
					'module.name' => __(\Service::load(\Service::id($module))->title);
					'elementId' => 'search-'.$module;
					});
					$output .= (string) $tpl;
				}
				return $output;
			});
			return (string) $tpl ?: __('No results were found.');
		}
		
		$form = new \Form;
		$form->data->attr(array('legend' => __('Search'), 'type' => 'fieldset'));
		$form->data->query->attr(array('label' => __('Search Keyword'), 'validator' => array('validate_match', '/^.{3,20}$/u', __('Search term must be a minimum of 3 characters and a maximum of 20 characters.')), 'value' => $A->query));
		$form->data->match->attr(array('label' => __('Match'), 'options' => array('exact' => __('Exact Phrase'), 'any' => __('Any words'), 'all' => __('All words')), 'type' => 'radio'));
		$form->data->order->attr(array('label' => __('Ordering'), 'type' => 'select', 'options' => array('newest' => __('Newest First'), 'oldest' => __('Oldest First'), 'popular' => __('Most Popular'), 'alpha' => __('Alphabetical'), 'category' => __('Category'))));
		$form->data->area->attr(array('label' => __('Search Only'), 'options' => \Hook::invoke('search'), 'type' => 'checkbox'));
		$form->button->submit['value'] = __('Search');
		return $form;
	}
}