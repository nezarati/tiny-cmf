<?
namespace Search;
class API {
	public static function widget() {
		return '<form method="get" onsubmit="return $.Search.quick(this)" action="/search"><input name="arg[query]" class="inputbox" class="float: left" /> <input type="submit" class="button" value="'.__('Find').'" /></form>';
	}
	public static function install() {
		\Service::install('search');
		# \Menu\API::save((object)array('module' => 'search', 'title' => __('Search'), 'callback' => '\Search\API::widget', 'status' => 1, 'weight' => 4), _SERVICE_MENU);
	}
	public function prepareSearchContent($text, $length = 200, $searchword) {
		// strips tags won't remove the actual jscript
		$text = preg_replace("'<script[^>]*>.*?</script>'si", "", $text );
		$text = preg_replace('/{.+?}/', '', $text);
		//$text = preg_replace( '/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text );
		// replace line breaking tags with whitespace
		$text = preg_replace( "'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text );
		return static::smartSubstr(strip_tags($text), $length, $searchword);
	}
	protected function smartSubstr($text, $length = 200, $searchword) {
		# TODO: UTF-8 Function
		$textlen = strlen($text);
		$lsearchword = strtolower($searchword);
		$wordfound = false;
		$pos = 0;
		while ($wordfound === false && $pos<$textlen) {
			$chunk_size = ($wordpos = @strpos($text, ' ', $pos+$length)) !== false ? $wordpos-$pos : $length;
			$chunk = substr($text, $pos, $chunk_size);
			$wordfound = strpos(strtolower($chunk), $lsearchword);
			if ($wordfound === false)
			$pos += $chunk_size + 1;
		}
		return $wordfound !== false ? ($pos>0 ? '...&nbsp;' : '').$chunk.'&nbsp;...' : (($wordpos = @strpos($text, ' ', $length)) !== false ? substr($text, 0, $wordpos).'&nbsp;...' : substr($text, 0, $length));
	}
}