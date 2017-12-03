<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2017/11/10
 * Time: 下午4:54
 *
 */
namespace fashop;
use Core\AbstractInterface\ExceptionHandlerInterface;
use Core\Http\Request as EsRequest;

class ExceptionHandler implements ExceptionHandlerInterface
{

	function handler(\Exception $exception)
	{
		// todo 这个方法干什么的
	}

	function display(\Exception $exception)
	{
		if( !$exception instanceof \Exception ){
			$exception = new \fashop\exception\ThrowableError( $exception );
		}
		Log::write($exception->getMessage(),'error');
		if(EsRequest::getInstance()){
			self::getExceptionHandler()->render( $exception );
		} else{
			// todo cli
		}
	}

	function log(\Exception $exception)
	{
		Log::write($exception->getMessage()." ".$exception->getTraceAsString(),'error');
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