<?
namespace Main;
class Smilies {
	protected static $smilies = array('8-)' => 'cool', '8-O' => 'eek', ':-(' => 'sad', ':-)' => 'smile', ':-?' => 'confused', ':-D' => 'biggrin', ':-P' => 'razz', ':-o' => 'surprised', ':-x' => 'mad', ':-|' => 'neutral', ';-)' => 'wink', ':!:' => 'exclaim', ':?:' => 'question', ':arrow:' => 'arrow', ':-((' => 'cry', '>:)' => 'devil', '*-:)' => 'idea', ':-))' => 'lol', ':-&' => 'sick', ':-$' => 'embarrassed', '8-|' => 'rolling eyes', 'X-(' => 'angry');
	public static function init() {
		foreach (static::$smilies as &$description)
			$description = ' <span class="smiley" style="background-position: -'.($position++*15).'px 0" title="'.$description.'"></span> ';
		krsort(static::$smilies);
		$search = '/(?:\s|^)';
		$subchar = '';
		foreach (static::$smilies as $smiley => $img ) {
			$firstchar = substr($smiley, 0, 1);
			$rest = substr($smiley, 1);
			// new subpattern?
			if ($firstchar != $subchar) {
				if ($subchar != '')
					$search .= ')|(?:\s|^)';
				$subchar = $firstchar;
				$search .= preg_quote($firstchar, '/') . '(?:';
			} else
				$search .= '|';
			$search .= preg_quote($rest, '/');
		}
		$search .= ')(?:\s|$)/m';
		return $search;
	}
	public static function convert($content) {
		static $search;
		if (!$search)
			$search = self::init();
		#foreach (array('3:-O' => '50', '#:-S' => '18', ':)>-' => '67', '(~~)' => '56', '[-O<' => '63', ':(|)' => '51', '**==' => '55', '*-:)' => '58', 'O:-)' => '25', '@};-' => '53', '\\:D/' => '69', ':-??' => '106', '>:D<' => '6', ':-SS' => '42', '^:)^' => '77', '<:-P' => '36', '<):)' => '48', '%%-' => '54', ':^o' => '44', '@-)' => '43', ':-w' => '45', '~:>' => '52', '%-(' => '107', ':-<' => '46', ':o3' => '108', '>:P' => '47', ':@)' => '49', '=:)' => '60', ':-j' => '78', ':-@' => '76', ';))' => '71', '(*)' => '79', 'o->' => '72', '(%)' => '75', 'o-+' => '74', 'o=>' => '73', '>:/' => '70', '[-X' => '68', '>-)' => '61', '=D>' => '41', '8-X' => '59', ':-L' => '62', '$-)' => '64', 'b-(' => '66', ':-\"' => '65', '~O)' => '57', '=P~' => '38', ':))' => '21', ':((' => '20', '>:)' => '19', '/:)' => '23', '#-o' => '40', ':-c' => '101', ':-B' => '26', ':-S' => '17', 'B-)' => '16', ':-/' => '7', ';;)' => '5', ':\">' => '9', ':-*' => '11', ':-O' => '13', '=((' => '12', ':)]' => '100', '=))' => '24', '[-(' => '33', '~X(' => '102', ':-$' => '32', ':O)' => '34', '8-}' => '35', '(:|' => '37', ':-?' => '39', ':-&' => '31', 'L-)' => '30', '8->' => '105', ':-h' => '103', 'I-)' => '28', '8-|' => '29', ':-t' => '104', ':(' => '2', ';)' => '3', ':D' => '4', ':)' => '1', ':>' => '15', '=;' => '27', ':|' => '22', 'X(' => '14', ':P' => '10', ':x' => '8') as $c => $l)
		#	$S = preg_replace('/(\s|^)'.preg_quote($c, '/').'(\s|$)/', '<img src="http://us.i1.yimg.com/us.yimg.com/i/mesg/emoticons7/'.$l.'.gif" />', $S);
		return preg_replace_callback($search, '\Main\Smilies::translate', $content);
	}
	public static function translate($smiley) {
		return static::$smilies[trim(reset($smiley))];
	}
}