<?php

namespace ezswoole;
class Error
{
	public static function error()
	{
	}

	public static function exception( \Exception $exception )
	{
		$handle = new ExceptionHandler();
		$handle->handle( $exception, Request::getInstance()->getEasySwooleRequest(), Response::getInstance()->getResponse() );
	}
}