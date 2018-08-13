<?php

namespace ezswoole;
class Cookie
{
	public static $path = '/';
	public static $domain = null;
	public static $secure = false;
	public static $httponly = false;

	static function get( $key = null, $default = null )
	{
		if( is_null( $key ) ){
			return $_COOKIE;
		} else if( !isset( $_COOKIE[$key] ) ){
			return $default;
		} else{
			return $_COOKIE[$key];
		}
	}

	static function set( string $key, $value, $expire = 0 ) : void
	{
		if( $expire != 0 ){
			$expire = time() + $expire;
		}
		Response::getInstance()->getResponse()->getSwooleResponse()->cookie( $key, $value, $expire, self::$path, self::$domain, self::$secure, self::$httponly );
	}

	static function delete( string $key )
	{
		unset( $_COOKIE[$key] );
		self::set( $key, '' );
	}

	static function has( string $key )
	{
		return isset( $_COOKIE[$key] );
	}
}