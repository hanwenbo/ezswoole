<?php

namespace fashop\exception;

/**
 * PDO异常处理类
 * 重新封装了系统的\PDOException类
 */
class PDOException extends DbException {
	/**
	 * PDOException constructor.
	 * @param \PDOException $exception
	 * @param array         $config
	 * @param string        $sql
	 * @param int           $code
	 */
	public function __construct(\PDOException $exception, array $config, $sql, $code = 10501) {
		$error = $exception->errorInfo;

		$this->setData('PDO Error Info', [
			'SQLSTATE'             => $error[0],
			'Driver Error Code'    => isset($error[1]) ? $error[1] : 0,
			'Driver Error Message' => isset($error[2]) ? $error[2] : '',
		]);

		parent::__construct($exception->getMessage(), $config, $sql, $code);
	}
}
