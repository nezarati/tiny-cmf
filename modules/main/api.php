<?
namespace Main;
class API {
	public static function install($D) {
		$cfg = \Registry::getInstance('main', \Service::install('main'));
		foreach (array('title' => $D->title, 'home' => 'http://'.$D->domain.'.chonoo.com/', 'name' => ucfirst($D->domain).'.Chonoo.Com') as $key => $value)
			$cfg->$key = $value;
		\Service::install('link', 'main');
		\Service::install('poll', 'main');
		\Menu\API::save((object)array('module' => 'main', 'title' => __('Quick Access'), 'content' => '<ul>
	<li><a href="/article" rel="ajax">'.__('Homepage').'</a></li>
</ul>', 'status' => 1, 'weight' => 0), _SERVICE_MENU);
	}
	
	public static function rewrite($route, &$result) {
		$rewrite = array(
			# Module
			'^([A-z_\-0-9]+)$' => 'module=$1&action=index',
			# Module/Id
			'^([A-z_\-0-9]+)/([0-9]+)$' => 'module=$1&action=index&arg[id]=$2',
			# Module/Id/Action
			'^([A-z_\-0-9]+)/([0-9]+)/([A-z_\-0-9]+)$' => 'module=$1&action=$3&arg[id]=$2',
			# Module/Action
			'^([A-z_\-0-9]+)/([A-z_\-0-9]+)$' => 'module=$1&action=$2',
			# Module/Action/Node
			'^([A-z_\-0-9]+)/([A-z_\-0-9]+)/([0-9]+)$' => 'module=$1&action=$2&arg[node]=$3',
			# Dependence/Module/Id
			'^([A-z_\-0-9]+)/([A-z_\-0-9]+)/([0-9]+)$' => 'module=$2&action=index&arg[dependence]=$1&arg[id]=$3',
			# Dependence/Module/Id/Action
			'^([A-z_\-0-9]+)/([A-z_\-0-9]+)/([0-9]+)/([A-z_\-0-9]+)$' => 'module=$2&action=$4&arg[dependence]=$1&arg[id]=$3',
			# Dependence/Module/Action/Node
			'^([A-z_\-0-9]+)/([A-z_\-0-9]+)/([A-z_\-0-9]+)/([0-9]+)$' => 'module=$2&action=$3&arg[dependence]=$1&arg[node]=$4',
			# Dependence/Module/Action
			'^([A-z_\-0-9]+)/([A-z_\-0-9]+)/([A-z_\-0-9]+)$' => 'module=$2&action=$3&arg[dependence]=$1',
			# Module/Id/Service/Action
			'^([A-z_\-0-9]+)/([0-9]+)/([A-z_\-0-9]+)/([A-z_\-0-9]+)$' => 'module=$1&action=$4&arg[id]=$2&arg[service]=$3',
		);
		# \Hook::invoke('rewrite')
		$rewrite['^robots\.txt$'] = 'module=main&action=robots';
		$rewrite['^extend\.js$'] = 'module=main&action=extendJS';
		$rewrite['^extend\.css$'] = 'module=main&action=extendCSS';
		$result = (array) $result;
		foreach ($rewrite as $path => $query)
			if (preg_match("!$path!", $route)) {
				parse_str(preg_replace("!$path!", $query, $route), $result);
				break;
			}
	}
	public static function byteConvert($bytes) {
		static $sizes = array('B', 'Kb', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		return round($bytes/pow(1024, $e = floor(log($bytes, 1024))), 3).$sizes[$e];
	}
	public static function getPreferredLanguage() {
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($n = preg_match_all('/([\w\-]+)\s*(;\s*q\s*=\s*(\d*\.\d*))?/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) > 0) {
			$languages = array();
			for ($i=0; $i<$n; ++$i)
				$languages[strtolower(str_replace('-', '_', $matches[1][$i]))] = empty($matches[3][$i]) ? 1.0 : floatval($matches[3][$i]);
			arsort($languages);
			return $languages;
		}
		return FALSE;
	}
}