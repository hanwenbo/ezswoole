<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2019-02-14
 * Time: 14:37
 *
 */

namespace ezswoole;


class Loader
{

	/**
	 * 字符串命名风格转换
	 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
	 * @param string  $name    字符串
	 * @param integer $type    转换类型
	 * @param bool    $ucfirst 首字母是否大写（驼峰规则）
	 * @return string
	 */
	public static function parseName( $name, $type = 0, $ucfirst = true )
	{
		if( $type ){
			$name = preg_replace_callback( '/_([a-zA-Z])/', function( $match ){
				return strtoupper( $match[1] );
			}, $name );
			return $ucfirst ? ucfirst( $name ) : lcfirst( $name );
		} else{
			return strtolower( trim( preg_replace( "/[A-Z]/", "_\\0", $name ), "_" ) );
		}
	}
}