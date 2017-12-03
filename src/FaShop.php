<?php

namespace fashop;
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2017/12/3
 * Time: 下午4:21
 *
 */

class FaShop
{
	public static function register()
	{
		define( 'FASHOP_VERSION', '1.0' );
		define( 'FASHOP_START_TIME', microtime( true ) );
		define( 'FASHOP_START_MEM', memory_get_usage() );
		define( 'EXT', '.php' );
		define( 'DS', DIRECTORY_SEPARATOR );
		defined( 'FASHOP_PATH' ) or define( 'FASHOP_PATH', __DIR__.DS );
		define( 'LIB_PATH', FASHOP_PATH );
		define( 'CORE_PATH', LIB_PATH );
		define( 'TRAIT_PATH', LIB_PATH.'traits'.DS );
		defined( 'APP_PATH' ) or define( 'APP_PATH', ROOT.DS."App".DS );
		defined( 'ROOT_PATH' ) or define( 'ROOT_PATH', dirname( realpath( APP_PATH ) ).DS );
		defined( 'EXTEND_PATH' ) or define( 'EXTEND_PATH', ROOT_PATH.'Extend'.DS );
		defined( 'VENDOR_PATH' ) or define( 'VENDOR_PATH', ROOT_PATH.'vendor'.DS );
		defined( 'RUNTIME_PATH' ) or define( 'RUNTIME_PATH', ROOT_PATH.'Runtime'.DS );
		defined( 'LOG_PATH' ) or define( 'LOG_PATH', RUNTIME_PATH.'Log'.DS );
		defined( 'CACHE_PATH' ) or define( 'CACHE_PATH', RUNTIME_PATH.'Cache'.DS );
		defined( 'TEMP_PATH' ) or define( 'TEMP_PATH', RUNTIME_PATH.'Temp'.DS );
		defined( 'CONF_PATH' ) or define( 'CONF_PATH', APP_PATH ); // 配置文件目录
		defined( 'CONF_EXT' ) or define( 'CONF_EXT', EXT ); // 配置文件后缀
		defined( 'ENV_PREFIX' ) or define( 'ENV_PREFIX', 'PHP_' ); // 环境变量的配置前缀

		// 环境常量
		define( 'IS_CLI', PHP_SAPI == 'cli' ? true : false );
		define( 'IS_WIN', strpos( PHP_OS, 'WIN' ) !== false );

		// 载入Loader类
		require CORE_PATH.'Loader.php';

		// 加载环境变量配置文件
		if( is_file( ROOT_PATH.'.env' ) ){
			$env = parse_ini_file( ROOT_PATH.'.env', true );
			foreach( $env as $key => $val ){
				$name = ENV_PREFIX.strtoupper( $key );
				if( is_array( $val ) ){
					foreach( $val as $k => $v ){
						$item = $name.'_'.strtoupper( $k );
						putenv( "$item=$v" );
					}
				} else{
					putenv( "$name=$val" );
				}
			}
		}

		// 注册自动加载
		\fashop\Loader::register();

		// 注册错误和异常处理机制
		//\fashop\Error::register();

		// 加载惯例配置文件
		\fashop\Config::set( include LIB_PATH.'config/convention'.EXT );

		// 执行应用
		App::run();

	}
}