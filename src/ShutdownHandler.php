<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2018/1/4
 * Time: 下午12:13
 *
 */

namespace fashop;


class ShutdownHandler
{
	static function handle()
	{
		if( !is_null( $error = error_get_last() ) && ErrorHandler::isFatal( $error['type'] ) ){
			$exception = new \fashop\exception\ErrorException( $error['type'], $error['message'], $error['file'], $error['line'] );
			$handler   = ErrorHandler::getExceptionHandler();
			$handler->report( $exception );
			$handler->render( $exception );
		}
	}
}