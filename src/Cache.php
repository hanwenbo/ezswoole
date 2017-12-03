<?php

namespace fashop;
use phpFastCache\Helper\Psr16Adapter;

/**
 * 缓存
 */
class Cache {
	protected static $instance;
	// 调度器
	protected $adapter;
	/**
	 * 初始化
	 * @access public
	 * @param array $options 参数
	 * @return \fashop\Cache
	 */
	public static function getInstance($options = []) {
		if (is_null(self::$instance)) {
			self::$instance = new static($options);
		}
		return self::$instance;
	}
	protected function __construct($options = []) {
		$this->adapter = new Psr16Adapter(Config::get('cache.type'), [
			'path'               => Config::get('cache.path'),
			'cacheFileExtension' => Config::get('cache.extension'),
		]);
	}

	public function get($key) {
		return $this->adapter->get($key);
	}

	public function set($key, $value, $ttl = null) {
		return $this->adapter->set($key, $value, $ttl);
	}

	public function delete($key) {
		return $this->adapter->delete($key);
	}
    // todo 增加tag Query里有牵连
	public function clear($tag = null) {
		return $this->adapter->clear();
	}

	public function getMultiple($keys, $default = null) {
		return $this->adapter->getMultiple($keys, $default);
	}

	public function setMultiple($values, $ttl = null) {
		return $this->adapter->setMultiple($values, $ttl);
	}

	public function deleteMultiple($keys) {
		return $this->adapter->deleteMultiple($keys);
	}

	public function has($key) {
		return $this->adapter->has($key);
	}
	// todo 增加tag
}
