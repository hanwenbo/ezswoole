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

use EasySwoole\Core\AbstractInterface\TriggerInterface;

class TriggerHandler implements TriggerInterface
{
	public static function error( $msg, $file = null, $line = null, $errorCode = E_USER_ERROR )
	{
		if( config( 'wsdebug.open' ) === true ){
			wsdebug()->send( [
				'code'    => $errorCode,
				'line'    => $line,
				'message' => $msg,
				'file'    => $file,
			], 'error' );
		}
	}

	public static function throwable( \Throwable $throwable )
	{
		if( config( 'wsdebug.open' ) === true ){
			wsdebug()->send( [
				'code'     => $throwable->getCode(),
				'line'     => $throwable->getLine(),
				'file'     => $throwable->getFile(),
				'message'  => $throwable->getMessage(),
				'previous' => $throwable->getPrevious(),
				'trace'    => $throwable->getTraceAsString(),
			], 'notice' );
		}
	}

}