<?php

use ezswoole\Cache;
use ezswoole\Config;
use ezswoole\Db;
use ezswoole\Debug;
use ezswoole\exception\HttpException;
use ezswoole\exception\HttpResponseException;
use ezswoole\Loader;
use ezswoole\Log;
use ezswoole\Model;
use ezswoole\Request;
use ezswoole\Cookie;
use ezswoole\Session;
use EasySwoole\Core\Component\Di;
use ezswoole\Response;
use ezswoole\WsDebug;
use EasySwoole\Core\Swoole\ServerManager;


if( !function_exists( 'wsdebug' ) ){
	/**
	 * @return WsDebug
	 */
	function wsdebug() : WsDebug
	{
		$di      = Di::getInstance();
		$wsdebug = $di->get( 'wsdebug' );
		if( $wsdebug instanceof WsDebug ){
			return $wsdebug;
		} else{
			$wsdebug = new WsDebug();
			$wsdebug->setServer( ServerManager::getInstance()->getServer() );
			$di->set( 'wsdebug', $wsdebug );
			return $wsdebug;
		}
	}
}

if( !function_exists( 'debug' ) ){
	/**
	 * 记录时间（微秒）和内存使用情况
	 * @param string         $start 开始标签
	 * @param string         $end   结束标签
	 * @param integer|string $dec   小数位 如果是m 表示统计内存占用
	 * @return mixed
	 */
	function debug( $start, $end = '', $dec = 6 )
	{
		if( '' == $end ){
			Debug::remark( $start );
		} else{
			return 'm' == $dec ? Debug::getRangeMem( $start, $end ) : Debug::getRangeTime( $start, $end, $dec );
		}
	}
}
if( !function_exists( 'lang' ) ){
	/**
	 * 获取语言变量值
	 * @param string $name 语言变量名
	 * @param array  $vars 动态变量值
	 * @param string $lang 语言
	 * @return mixed
	 */
	function lang( $name, $vars = [], $lang = '' )
	{
		return \ezswoole\facade\Lang::get( $name, $vars, $lang );
	}
}
if( !function_exists( 'config' ) ){
	/**
	 * 获取和设置配置参数
	 * @param string|array $name  参数名
	 * @param mixed        $value 参数值
	 * @param string       $range 作用域
	 * @return mixed
	 */
	function config( $name = '', $value = null, $range = '' )
	{
		if( is_null( $value ) && is_string( $name ) ){
			return 0 === strpos( $name, '?' ) ? Config::has( substr( $name, 1 ), $range ) : Config::get( $name, $range );
		} else{
			return Config::set( $name, $value, $range );
		}
	}
}


if( !function_exists( 'model' ) ){

	/**
	 * 实例化Model
	 * @param string $name
	 * @param string $layer
	 * @param bool   $appendSuffix
	 * @return object;
	 * @author 韩文博
	 */
	function model( $name = '', $layer = 'Model', $appendSuffix = false )
	{
		return Loader::model( $name, $layer, $appendSuffix );
	}
}

if( !function_exists( 'validate' ) ){
	/**
	 * 实例化验证器
	 * @method GET
	 * @param string $name         验证器名称
	 * @param string $layer        业务层名称
	 * @param bool   $appendSuffix 是否添加类名后缀
	 * @return false|object
	 * @author 韩文博
	 */
	function validate( $name = '', $layer = 'Validate', $appendSuffix = false )
	{
		return Loader::validate( $name, $layer, $appendSuffix );
	}
}

if( !function_exists( 'db' ) ){
	/**
	 * 实例化数据库类
	 * @param string       $name   操作的数据表名称（不含前缀）
	 * @param array|string $config 数据库配置参数
	 * @param bool         $force  是否强制重新连接
	 * @throws \ezswoole\Exception
	 * @return \ezswoole\db\Query
	 */

	function db( $name = '', $config = [], $force = false )
	{
		return Db::connect( $config, $force )->name( $name );
	}
}


if( !function_exists( 'dump' ) ){
	/**
	 * 浏览器友好的变量输出
	 * @param mixed   $var   变量
	 * @param boolean $echo  是否输出 默认为true 如果为false 则返回输出字符串
	 * @param string  $label 标签 默认为空
	 * @return string|null
	 */
	function dump( $var, $echo = true, $label = null ) : ?string
	{
		return Debug::dump( $var, $echo, $label );
	}
}


if( !function_exists( 'session' ) ){
	/**
	 * Session管理
	 * @param string|array $name   session名称，如果为数组表示进行session设置
	 * @param mixed        $value  session值
	 * @param string       $prefix 前缀
	 * @return mixed
	 */
	function session( $name = null, $value = '', $prefix = null )
	{
		if( is_null( $name ) ){
			return Session::get();
		} else if( '' === $value ){
			return Session::get( $name );
		} elseif( is_null( $value ) ){
			Session::delete( $name );
		} else{
			Session::set( $name, $value );
		}
	}
}

if( !function_exists( 'cookie' ) ){
	/**
	 * Cookie管理
	 * @param string|array $name   cookie名称，如果为数组表示进行cookie设置
	 * @param mixed        $value  cookie值
	 * @param mixed        $option 参数
	 * @return mixed
	 */
	function cookie( $name, $value = '', $option = null )
	{
		if( is_null( $name ) ){
			return Cookie::get();
		} else if( '' === $value ){
			return Cookie::get( $name, $option );
		} elseif( is_null( $value ) ){
			Cookie::delete( $name );
		} else{
			Cookie::set( $name, $value, $option );
		}
	}
}

if( !function_exists( 'cache' ) ){
	/**
	 * 缓存管理
	 * @param mixed $name   缓存名称，如果为数组表示进行缓存设置
	 * @param mixed $value  缓存值
	 * @param int   $expire 缓存时间
	 * @return mixed
	 */
	function cache( $name, $value = '', $expire = null )
	{
		$cache = Cache::getInstance();
		if( is_null( $name ) ){
			return $cache->clear( $value );
		} elseif( '' === $value ){
			// 获取缓存
			return 0 === strpos( $name, '?' ) ? $cache->has( substr( $name, 1 ) ) : $cache->get( $name );
		} elseif( is_null( $value ) ){
			// 删除缓存
			return $cache->delete( $name );
		} else{
			$expire = is_numeric( $expire ) ? $expire : null;
			return $cache->set( $name, $value, $expire );
		}
	}
}

if( !function_exists( 'trace' ) ){
	/**
	 * 记录日志信息
	 * @param mixed  $log   log信息 支持字符串和数组
	 * @param string $level 日志级别
	 * @return null|array
	 */
	function trace( $log = '[ezswoole]', $level = 'trace' ) : ?array
	{
		if( '[ezswoole]' === $log ){
			return Log::getLog();
		} else{
			Log::write( $log, $level );
			return null;
		}
	}
}

if( !function_exists( 'request' ) ){
	/**
	 * 获取当前Request对象实例
	 * @return Request
	 */
	function request()
	{
		return Request::getInstance();
	}
}

if( !function_exists( 'response' ) ){
	/**
	 * 创建普通 Response 对象实例
	 * @return Response
	 */
	function response( \swoole_http_response $response = null )
	{
		return Response::getInstance( $response );
	}
}

if( !function_exists( 'collection' ) ){
	/**
	 * 数组转换为数据集对象
	 * @param array $resultSet 数据集数组
	 * @return \ezswoole\model\Collection|\ezswoole\Collection
	 */
	function collection( $resultSet )
	{
		$item = current( $resultSet );
		if( $item instanceof Model ){
			return \ezswoole\model\Collection::make( $resultSet );
		} else{
			return \ezswoole\Collection::make( $resultSet );
		}
	}
}

