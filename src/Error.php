<?php
namespace fashop;
use Core\Component\Error\Trigger;
class Error
{
	public static function error(  )
	{
		Trigger::error($msg, $file = null, $line = null, $errorCode = E_USER_ERROR, $trace = null);
	}

	public static function exception( \Exception $exception )
	{
		Trigger::exception($exception);
	}
}