<?php

namespace fashop;


use Core\AbstractInterface\ErrorHandlerInterface;
use Core\Http\Request as EsRequest;
class ErrorHandler implements ErrorHandlerInterface
{

	function handler( $msg,$file = null,$line = null,$errorCode = null,$trace )
	{
	}

	function display($msg,$file = null,$line = null,$errorCode = null,$trace )
	{
		// 判断是否在HTTP模式下
		if(EsRequest::getInstance()){
			if (!is_null($error = error_get_last()) && self::isFatal($error['type'])) {
				$this->appError( $error['type'], $error['message'], $error['file'], $error['line'], $trace );
			}
		}
	}

	function log( $msg,$file = null,$line = null,$errorCode = null,$trace )
	{
		Log::write("文件：{$file}，第{$line}行，错误：{$msg}",self::isFatal($errorCode) ? 'error' : 'notice');
	}

	/**
	 * 确定错误类型是否致命
	 *
	 * @param  int $type
	 * @return bool
	 */
	protected static function isFatal( $type )
	{
		return in_array( $type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE] );
	}
	/**
	 * Error Handler
	 * @param  integer $errno   错误编号
	 * @param  integer $errstr  详细错误信息
	 * @param  string  $errfile 出错的文件
	 * @param  integer $errline 出错行号
	 * @param array    $errcontext
	 * @throws ErrorException
	 */
	public static function appError( $errno, $errstr, $errfile = '', $errline = 0, $errcontext = [] )
	{
		$exception = new \fashop\exception\ErrorException( $errno, $errstr, $errfile, $errline, $errcontext );
		// todo report
		self::getExceptionHandler()->render( $exception );
	}

	/**
	 * Shutdown Handler
	 */
	public static function appShutdown()
	{
		if( !is_null( $error = error_get_last() ) && self::isFatal( $error['type'] ) ){
			$exception = new \fashop\exception\ErrorException( $error['type'], $error['message'], $error['file'], $error['line'] );
			self::appException( $exception );
		}
		// 写入日志
//		Log::save();
	}

	/**
	 * Get an instance of the exception handler.
	 *
	 * @return Handle
	 */
	public static function getExceptionHandler()
	{
		// 异常处理handle
		$class = \fashop\Config::get( 'exception_handle' );
		if( $class && class_exists( $class ) && is_subclass_of( $class, "\\fashop\\exception\\Handle" ) ){
			$handle = new $class;
		} else{
			$handle = new \fashop\exception\Handle;
			if( $class instanceof \Closure ){
				$handle->setRender( $class );
			}
		}
		return $handle;
	}

}