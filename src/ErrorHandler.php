<?php

namespace ezswoole;
class ErrorHandler
{
	public static function handle($errorCode, $description, $file = null, $line = null)
	{
		if( !is_null( $error = error_get_last() ) && self::isFatal( $error['type'] ) ){
			self::error( $error['type'], $error['message'], $error['file'], $error['line'] );
		}
	}

	public static function isFatal( $type )
	{
		return in_array( $type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE] );
	}

	public static function error( $errno, $errstr, $errfile = '', $errline = 0, $errcontext = [] )
	{
		$exception = new \ezswoole\exception\ErrorException( $errno, $errstr, $errfile, $errline, $errcontext );
		self::getExceptionHandler()->render( $exception );
	}


	public static function getExceptionHandler()
	{
		$class = \ezswoole\Config::get( 'exception_handle' );
		if( $class && class_exists( $class ) && is_subclass_of( $class, "\\ezswoole\\exception\\Handle" ) ){
			$handle = new $class;
		} else{
			$handle = new \ezswoole\exception\Handle;
			if( $class instanceof \Closure ){
				$handle->setRender( $class );
			}
		}
		return $handle;
	}

}