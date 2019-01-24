<?php
go(function () {
	$id = Co::getuid();
	Context::put('info', "w s ".Co::getuid()); // get context of this coroutine
	$info = Context::get('info', Co::getuid()); // get context of this coroutine
	var_dump($info);
	defer(function () {
		echo '我是defer，我最后执行，是先进后出';
		var_dump(Co::getuid());
		Context::delete('info', Co::getuid()); // delete
		$info = Context::get('info', Co::getuid()); // get context of this coroutine
		var_dump("看看删除没有".$info);
	});
	go(function()use($id){
		var_dump('id:'.$id);
		var_dump(Co::getuid());
	});
	throw new Exception('something wrong');
	echo "never here\n";
});

use Swoole\Coroutine;

class Context {

	protected static $pool = [];

	public static function cid():int {
		return Coroutine::getuid();
	}

	public static function get($key, int $cid = null) {
		$cid = $cid ?? Coroutine::getuid();
		if ($cid < 0) {
			return null;
		}
		if (isset(self::$pool[$cid][$key])) {
			return self::$pool[$cid][$key];
		}
		return null;
	}

	public static function put($key, $item, int $cid = null) {
		$cid = $cid ?? Coroutine::getuid();
		if ($cid > 0) {
			self::$pool[$cid][$key] = $item;
		}
		return $item;
	}

	public static function delete($key, int $cid = null) {
		$cid = $cid ?? Coroutine::getuid();
		if ($cid > 0) {
			unset(self::$pool[$cid][$key]);
		}
	}

	public static function destruct(int $cid = null) {
		$cid = $cid ?? Coroutine::getuid();
		if ($cid > 0) {
			unset(self::$pool[$cid]);
		}
	}
}