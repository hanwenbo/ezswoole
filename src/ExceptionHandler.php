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

namespace ezswoole;

use EasySwoole\Core\Http\AbstractInterface\ExceptionHandlerInterface;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

class ExceptionHandler implements ExceptionHandlerInterface
{
	private $request;
	private $response;
	public function handle( \Throwable $exception, Request $request, Response $response )
	{
		$this->request = $request;
		$this->response = $response;
		$error_exception = new \ezswoole\exception\ErrorException($exception->getCode(),$exception->getMessage(),$exception->getFile(),$exception->getLine());
		$this->getExceptionHandler()->render( $error_exception );
	}
	private function getExceptionHandler()
	{
		$class = \ezswoole\Config::get( 'exception_handle' );
		if( $class && class_exists( $class ) && is_subclass_of( $class, "\\ezswoole\\exception\\Handle" ) ){
			$handle = new $class;
		} else{
			$handle = new \ezswoole\exception\Handle;
			if( $class instanceof \Closure ){
				$handle->setRender( $class );
			}else{
				$handle->setRequest($this->request);
				$handle->setResponse($this->response);
			}
		}
		return $handle;
	}

}