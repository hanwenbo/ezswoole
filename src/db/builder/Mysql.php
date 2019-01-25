<?php

namespace ezswoole\db\builder;

use ezswoole\db\Builder;

/**
 * mysql数据库驱动
 */
class Mysql extends Builder {
	protected $updateSql = 'UPDATE %TABLE% %JOIN% SET %SET% %WHERE% %ORDER%%LIMIT% %LOCK%%COMMENT%';

	/**
	 * 字段和表名处理

	 * @param string $key
	 * @param array  $options
	 * @return string
	 */
	protected function parseKey($key, $options = []) {
		$key = trim($key);
		if (strpos($key, '$.') && false === strpos($key, '(')) {
			// JSON字段支持
			list($field, $name) = explode('$.', $key);
			$key                = 'json_extract(' . $field . ', \'$.' . $name . '\')';
		} elseif (strpos($key, '.') && !preg_match('/[,\'\"\(\)`\s]/', $key)) {
			list($table, $key) = explode('.', $key, 2);
			if ('__TABLE__' == $table) {
				$table = $this->query->getTable();
			}
			if (isset($options['alias'][$table])) {
				$table = $options['alias'][$table];
			}
		}
		if (!preg_match('/[,\'\"\*\(\)`.\s]/', $key)) {
			$key = '`' . $key . '`';
		}
		if (isset($table)) {
			if (strpos($table, '.')) {
				$table = str_replace('.', '`.`', $table);
			}
			$key = '`' . $table . '`.' . $key;
		}
		return $key;
	}

	/**
	 * 随机排序

	 * @return string
	 */
	protected function parseRand() {
		return 'rand()';
	}

}
