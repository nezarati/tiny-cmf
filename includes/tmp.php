<?
final class TMP {
	public function __set($K, $V) {
		return apc_store($K, $V);
	}
	public function &__get($K) {
		return apc_fetch($K);
	}
	public function __isset($K) {
		apc_fetch($K, $s);
		return $s;
	}
	public function __unset($K) {
		return apc_delete($K);
	}
	public static function add($K, $V, $E = JOORCHIN_TIME_TO_LIVE) {
		return apc_add($K, $V, $E);
	}
	public static function &set($namespace, $value, $expire = JOORCHIN_TIME_TO_LIVE) {
		return apc_store($namespace, $value, $expire) ? $value : FALSE;
	}
	public static function &get($namespace) {
		return apc_fetch($namespace);
	}
	public static function delete() {
		foreach (func_get_args() as $namespace)
			apc_delete($namespace);
		return TRUE;
	}
	public static function clear() {
		return apc_clear_cache();
	}
	public static function define_constant($K, $C, $S=1) {
		return apc_define_constants($K, $C, $S);
	}
	public static function load_constant($K) {
		return apc_load_constants($K);
	}
	public static function compile($F) {
		return apc_compile_file($F);
	}
	public static function __set_state($A=null) {
		return apc_cache_info();
	}
	public function __toString() {
		return apc_sma_info();
	}
	private function __clone() {
	}
}