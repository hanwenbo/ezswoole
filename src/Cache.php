<?php

namespace ezswoole;
use phpFastCache\Helper\Psr16Adapter;

/**
 * 缓存
 */
class Cache {
	protected static $instance;
	// 调度器
	protected $adapter;
	/**
	 * @param array $options
	 * @return Cache
	 */
	public static function getInstance($options = []) {
		if (is_null(self::$instance)) {
			self::$instance = new static($options);
		}
		return self::$instance;
	}

	/**
	 * Cache constructor.
	 * @param array $options
	 */
	public function __construct($options = []) {
		$this->adapter = new Psr16Adapter(Config::get('cache.type'), [
			'path'               => Config::get('cache.path'),
			'cacheFileExtension' => Config::get('cache.extension'),
		]);
	}

	/**
	 * @param $key
	 * @return mixed|null
	 */
	public function get($key) {
		return $this->adapter->get($key);
	}

	/**
	 * @param      $key
	 * @param      $value
	 * @param null $ttl
	 * @return bool
	 */
	public function set($key, $value, $ttl = null) {
		return $this->adapter->set($key, $value, $ttl);
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function delete($key) {
		return $this->adapter->delete($key);
	}

	/**
	 * @param null $tag
	 * @return bool
	 */
	public function clear() {
		return $this->adapter->clear();
	}

	/**
	 * @param      $keys
	 * @param null $default
	 * @author 韩文博
	 */
	public function getMultiple($keys, $default = null) {
		return $this->adapter->getMultiple($keys, $default);
	}

	/**
	 * @param      $values
	 * @param null $ttl
	 * @return bool
	 */
	public function setMultiple($values, $ttl = null) {
		return $this->adapter->setMultiple($values, $ttl);
	}

	/**
	 * @param $keys
	 * @return bool
	 */
	public function deleteMultiple($keys) {
		return $this->adapter->deleteMultiple($keys);
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function has($key) {
		return $this->adapter->has($key);
	}
}
