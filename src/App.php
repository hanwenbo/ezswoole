<?php

namespace fashop;


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

	public function initBase()
	{
		// 注册核心类到容器
		Container::getInstance()->bind( [
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


}
