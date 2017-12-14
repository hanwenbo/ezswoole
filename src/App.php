<?php

namespace fashop;

use fashop\exception\HttpResponseException;
/**
 * App 应用管理
 */
class App
{
	/**
	 * @var bool 是否初始化过
	 */
	protected static $init = false;

	/**
	 * @var string 当前模块路径
	 */
	public static $modulePath;

	/**
	 * @var bool 应用调试模式
	 */
	public static $debug = true;

	/**
	 * @var string 应用类库命名空间
	 */
	public static $namespace = 'app';

	/**
	 * @var bool 应用类库后缀
	 */
	public static $suffix = false;

	/**
	 * @var bool 应用路由检测
	 */
	protected static $routeCheck;

	/**
	 * @var bool 严格路由检测
	 */
	protected static $routeMust;

	protected static $dispatch;
	protected static $file = [];

	/**
	 * 执行应用程序
	 * @access public
	 * @param Request $request Request对象
	 * @return Response
	 * @throws Exception
	 */
	public static function run( Request $request = null )
	{
		try{
			$config = self::initCommon();
			// 默认语言
			Lang::range( $config['default_lang'] );
			if( $config['lang_switch_on'] ){
				// 开启多语言机制 检测当前语言
				Lang::detect();
			}

		} catch( HttpResponseException $exception ){
			// $data = $exception->getResponse();
		}
		// 清空类的实例化
		Loader::clearInstance();
	}

	/**
	 * 初始化应用
	 */
	public static function initCommon()
	{
		if( empty( self::$init ) ){
			self::$namespace = 'App';
			// 初始化应用
			$config       = self::init();
			self::$suffix = $config['class_suffix'];

			// 应用调试模式
			self::$debug = Env::get( 'app_debug', Config::get( 'app_debug' ) );
			if( !self::$debug ){
				ini_set( 'display_errors', 'Off' );
			}

			if( !empty( $config['root_namespace'] ) ){
				Loader::addNamespace( $config['root_namespace'] );
			}

			// 加载额外文件
			if( !empty( $config['extra_file_list'] ) ){
				foreach( $config['extra_file_list'] as $file ){
					$file = strpos( $file, '.' ) ? $file : APP_PATH.$file.EXT;
					if( is_file( $file ) && !isset( self::$file[$file] ) ){
						include $file;
						self::$file[$file] = true;
					}
				}
			}
			// 设置系统时区
			date_default_timezone_set( $config['default_timezone'] );
			self::$init = true;
		}
		return Config::get();
	}

	/**
	 * 初始化应用或模块
	 * @access public
	 * @param string $module 模块名
	 * @return array
	 */
	private static function init()
	{
		$server_config = \Conf\Config::getInstance()->getConf( "*" );
		foreach( $server_config as $key => $_config ){
			if( Config::has( $key ) ){
				if( isset( $_config[$key] ) && is_array( $_config[$key] ) ){
					foreach( $_config[$key] as $k => $v ){
						Config::set( "{$key}.{$k}", $v );
					}
				} else{
					Config::set( "{$key}", $_config );
				}
			}
		}
//		Error::register();
		// 加载应用状态配置
		if( Config::get( 'app_status' ) ){
			Config::load( CONF_PATH.Config::get( 'app_status' ).CONF_EXT );
		}
		return Config::get();
	}


}
