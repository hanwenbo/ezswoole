<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2018/9/1
 * Time: 下午11:40
 *
 */

namespace ezswoole;


class WsDebug extends \wsdebug\WsDebug
{
	public function send( $message, string $type = 'info' )
	{
		if( config( 'app_debug' ) === true ){
			parent::send( $message, $type );
		} else{
			return false;
		}
	}

	public function getHtml() : string
	{
		if( config( 'app_debug' ) === true ){
			return parent::getHtml();
		} else{
			return 'closed';
		}
	}
}