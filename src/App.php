<?php

namespace ezswoole;

use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

/**
 * App 应用管理
 */
class App
{
	private $config;
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
	 * 容器对象实例
	 * @var Container
	 */
	protected $container;

	public function __construct()
	{
		$this->container = Container::getInstance();
	}

	public function run()
	{
		$this->initBase();
		$this->initConfig();
		$this->initApp();
		Loader::clearInstance();
	}

	static $hooks = [];
	const HOOK_INIT          = 1; //初始化
	const HOOK_ROUTE         = 2; //URL路由
	const HOOK_CLEAN         = 3; //清理
	const HOOK_BEFORE_ACTION = 4;
	const HOOK_AFTER_ACTION  = 5;

	public function initBase()
	{
		// 注册核心类到容器
		$this->container->bind( [
			'app'      => App::class,
			'build'    => Build::class,
			'cache'    => Cache::class,
			'config'   => Config::class,
			'debug'    => Debug::class,
			'env'      => Env::class,
			'lang'     => Lang::class,
			'log'      => Log::class,
			'request'  => Request::class,
			'response' => Response::class,
			'session'  => Session::class,
			'validate' => Validate::class,
		] );
		// 注册核心类的静态代理
		Facade::bind( [
			facade\Lang::class => Lang::class,
		] );
		// 注册类库别名
		Loader::addClassAlias( [
			'Db'     => Db::class,
			'Facade' => Facade::class,
			'Lang'   => facade\Lang::class,
		] );

	}

	public function initApp()
	{
		self::$namespace = 'App';

		self::$debug = Env::get( 'app_debug', Config::get( 'app_debug' ) );
		if( !self::$debug ){
			ini_set( 'display_errors', 'Off' );
		}

		$providerName = "\\".self::$namespace."\\Provider";
		$provider     = new $providerName();

		$this->container->bind( $provider->get() );

		if( !empty( $this->config['root_namespace'] ) ){
			Loader::addNamespace( $this->config['root_namespace'] );
		}

		if( !empty( $this->config['extra_file_list'] ) ){
			foreach( $this->config['extra_file_list'] as $file ){
				$file = (strpos( $file, '.' ) !== false) ? $file : APP_PATH.$file.EXT;
				if( is_file( $file ) && !isset( self::$file[$file] ) ){
					include $file;
					self::$file[$file] = true;
				}
			}
		}
		date_default_timezone_set( $this->config['default_timezone'] );
		self::$init = true;
		return Config::get();
	}

	private function initConfig()
	{
		$server_config = \EasySwoole\Config::getInstance()->getConf( "." );
		foreach( $server_config as $key => $_config ){
			if( isset( $_config[$key] ) && is_array( $_config[$key] ) ){
				foreach( $_config[$key] as $k => $v ){
					Config::set( "{$key}.{$k}", $v );
				}
			} else{
				Config::set( "{$key}", $_config );
			}
		}
		if( Config::get( 'app_status' ) ){
			Config::load( CONF_PATH.Config::get( 'app_status' ).CONF_EXT );
		}
		$this->config = Config::get();
	}

	static function addHook( $type, $func )
	{
		self::$hooks[$type][] = $func;
	}

	/**
	 * 执行Hook函数列表
	 * @param $type
	 * @param $subtype
	 */
	static function callHook( $type, $subtype = false )
	{
		if( $subtype and isset( self::$hooks[$type][$subtype] ) ){
			foreach( self::$hooks[$type][$subtype] as $f ){
				if( !is_callable( $f ) ){
					trigger_error( "hook function[$f] is not callable." );
					continue;
				}
				$f();
			}
		} elseif( isset( self::$hooks[$type] ) ){
			foreach( self::$hooks[$type] as $f ){
				//has subtype
				if( is_array( $f ) and !is_callable( $f ) ){
					foreach( $f as $subtype => $ff ){
						if( !is_callable( $ff ) ){
							trigger_error( "hook function[$ff] is not callable." );
							continue;
						}
						$ff();
					}
				} else{
					if( !is_callable( $f ) ){
						trigger_error( "hook function[$f] is not callable." );
						continue;
					}
					$f();
				}
			}
		}
	}

	static function afterAction( Request $request, Response $response ) : void
	{
		\ezswoole\Request::clearGlobalVariables();
		self::callHook( self::HOOK_AFTER_ACTION );
		self::$hooks = [];
	}

	static function onRequest( Request $request, Response $response ) : void
	{
		\ezswoole\Request::clearGlobalVariables();
		\ezswoole\Request::getInstance( $request );
		\ezswoole\Response::getInstance( $response );
		\ezswoole\Request::setGlobalVariables( $request );
	}
}
