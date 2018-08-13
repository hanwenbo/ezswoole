<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2018/1/20
 * Time: 上午12:23
 *
 */

namespace ezswoole\helper;


class Check
{
	static function unEmpty( $key ) : bool
	{
		if( isset( $key ) && !empty( $key ) ){
			return true;
		} else{
			return false;
		}
	}
	static function isEmpty($key):bool
	{
		if( !isset( $key ) || empty( $key ) ){
			return true;
		} else{
			return false;
		}
	}
}