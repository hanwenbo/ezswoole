<?php
namespace ezswoole;
use EasySwoole\Utility\File;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
class Core
{
	private $config;

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

		// 执行应用
		$app = new self();
		$app->run();
	}

	public function run()
	{
		$this->initDir();
		$this->initConfig();
	}

	private function initConfig()
	{

	}

	private function initDir() : void
	{
		if( !is_dir( RUNTIME_PATH ) ){
			File::createDirectory( RUNTIME_PATH );
		}
		if( !is_dir( LOG_PATH ) ){
			File::createDirectory( LOG_PATH );
		}
		if( !is_dir( CACHE_PATH ) ){
			File::createDirectory( CACHE_PATH );
		}
		if( !is_dir( TEMP_PATH ) ){
			File::createDirectory( TEMP_PATH );
		}
		if( !is_dir( CONF_PATH ) ){
			File::createDirectory( CONF_PATH );
		}
		// 存放子配置项 如微信 定时任务等
		if( !is_dir( CONF_PATH.'config/' ) ){
			File::createDirectory( CONF_PATH.'config/' );
		}
	}
	static function afterAction( Request $request, Response $response ) : void
	{
		\ezswoole\Request::clearGlobalVariables();
//		self::callHook( self::HOOK_AFTER_ACTION );
//		self::$hooks = [];
	}
	static function onRequest( Request $request, Response $response ) : void
	{
		\ezswoole\Request::clearGlobalVariables();
		\ezswoole\Request::getInstance( $request );
		\ezswoole\Response::getInstance( $response );
		\ezswoole\Request::setGlobalVariables( $request );
	}
}


