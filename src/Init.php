<?php

namespace ezswoole;
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
class Init
{
	public static function register()
	{
		define( 'EZSWOOLE_START_TIME', microtime( true ) );
		define( 'EZSWOOLE_START_MEM', memory_get_usage() );
		define( 'EXT', '.php' );
		define( 'DS', DIRECTORY_SEPARATOR );
		defined( 'EZSWOOLE_PATH' ) or define( 'EZSWOOLE_PATH', __DIR__.DS );
		define( 'LIB_PATH', EZSWOOLE_PATH );
		defined( 'APP_PATH' ) or define( 'APP_PATH', __DIR__.DS."..".DS."App".DS );
		defined( 'ROOT_PATH' ) or define( 'ROOT_PATH', dirname( realpath( APP_PATH ) ).DS );
		defined( 'RUNTIME_PATH' ) or define( 'RUNTIME_PATH', ROOT_PATH.'Runtime'.DS );
		defined( 'LOG_PATH' ) or define( 'LOG_PATH', RUNTIME_PATH.'Log'.DS );
		defined( 'CACHE_PATH' ) or define( 'CACHE_PATH', RUNTIME_PATH.'Cache'.DS );
		defined( 'TEMP_PATH' ) or define( 'TEMP_PATH', RUNTIME_PATH.'Temp'.DS );
		defined( 'CONF_PATH' ) or define( 'CONF_PATH', ROOT_PATH.'Conf'.DS ); // 配置文件目录

		// 加载惯例配置文件
		\ezswoole\Config::set( include LIB_PATH.'config/detault'.EXT );
		// 执行应用
		$app = new App();
		$app->run();
	}

}