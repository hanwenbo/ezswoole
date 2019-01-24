<?php

namespace ezswoole;

class Env {
	/**
	 * 获取环境变量值
	 * @param string    $name 环境变量名（支持二级 .号分割）
	 * @param string    $default  默认值
	 * @return mixed
	 */
	public static function get($name, $default = null) {
		$result = getenv( strtoupper(str_replace('.', '_', $name)));
		if (false !== $result) {
			if ('false' === $result) {
				$result = false;
			} elseif ('true' === $result) {
				$result = true;
			}
			return $result;
		} else {
			return $default;
		}
	}
}
