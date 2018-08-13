<?php

namespace ezswoole;

class Session
{
	protected $config;
	// 类成员属性定义
	static $cache_prefix = "phpsess_";
	static $cookie_key = 'PHPSESSID';
	static $sess_size = 32;
	/**
	 * 是否启动
	 * @var bool
	 */
	public $isStart = false;
	protected $sessID;
	protected $readonly; //是否为只读，只读不需要保存
	/**
	 * @var Cache
	 */
	protected $cache;
	/**
	 * 使用PHP内建的SESSION
	 * @var bool
	 */
	public $use_php_session = true;
	protected $cookie_lifetime = 86400000;
	protected $session_lifetime = 0;
	protected $cookie_domain = null;
	protected $cookie_path = '/';

	/**
	 * Session constructor.
	 * @param $config
	 * @throws \phpFastCache\Exceptions\phpFastCacheDriverCheckException
	 */
	public function __construct( array $config )
	{
		$this->config = $config;
		/**
		 * 使用PHP提供的Session
		 */
		if( isset( $config['use_php_session'] ) and $config['use_php_session'] ){
			$this->use_php_session = true;
			return;
		}
		$this->cache = Cache::getInstance();
		/**
		 * cookie过期时间
		 */
		if( isset( $config['cookie_lifetime'] ) ){
			$this->cookie_lifetime = intval( $config['cookie_lifetime'] );
		}
		if( isset( $config ) ){
			/**
			 * cookie的路径
			 */
			if( isset( $config['cookie_path'] ) ){
				$this->cookie_path = $config['cookie_path'];
			}
		}
		/**
		 * cookie域名
		 */
		if( isset( $config['cookie_domain'] ) ){
			$this->cookie_domain = $config['cookie_domain'];
		}
		/**
		 * session的过期时间
		 */
		if( isset( $config['session_lifetime'] ) ){
			$this->session_lifetime = intval( $config['session_lifetime'] );
		}
		$this->use_php_session = false;
		/**
		 * 注册钩子，请求结束后保存Session
		 */
		App::addHook( App::HOOK_AFTER_ACTION, [$this, 'save'] );
	}

	/**
	 * 启动会话
	 * @param bool $readonly
	 * @throws SessionException
	 */
	public function start( $readonly = false )
	{
		$this->isStart = true;
		if( $this->use_php_session ){
			session_start();
		} else{
			$this->readonly = $readonly;
			$sessid         = $this->sessID;
			if( empty( $sessid ) ){
				$sessid = Cookie::get( self::$cookie_key );
				if( empty( $sessid ) ){
					$sessid = \ezswoole\utils\RandomKey::randmd5( 40 );
					Response::getInstance()->getResponse()->getSwooleResponse()->cookie( self::$cookie_key, $sessid, time() + $this->cookie_lifetime, $this->cookie_path, $this->cookie_domain );
				}
			}
			$_SESSION = $this->load( $sessid );
		}
		Response::getInstance()->session = $_SESSION;
	}

	/**
	 * 设置SessionID
	 * @param $session_id
	 */
	function setId( $session_id )
	{
		$this->sessID = $session_id;
		if( $this->use_php_session ){
			session_id( $session_id );
		}
	}

	/**
	 * 获取SessionID
	 * @return string
	 */
	function getId()
	{
		if( $this->use_php_session ){
			return session_id();
		} else{
			return $this->sessID;
		}
	}

	/**
	 * 加载Session
	 * @param $sessId
	 * @return array|mixed
	 * @throws \phpFastCache\Exceptions\phpFastCacheDriverCheckException
	 * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
	 * @author 韩文博
	 */
	public function load( $sessId )
	{
		$this->sessID = $sessId;
		$data         = $this->cache->get( self::$cache_prefix.$sessId );
		//先读数据，如果没有，就初始化一个
		if( !empty( $data ) ){
			return unserialize( $data );
		} else{
			return [];
		}
	}


	static function get( string $name = null )
	{
		if( is_null( $name ) ){
			return $_SESSION;
		} else{
			return $_SESSION[$name];
		}
	}

	static function set( string $name, $value ) : void
	{
		$_SESSION[$name] = $value;
	}

	static function delete( $key )
	{
		unset( $_SESSION[$key] );
	}
	/**
	 * 保存Session
	 * @return bool
	 * @throws \phpFastCache\Exceptions\phpFastCacheSimpleCacheException
	 * @author 韩文博
	 */
	public function save()
	{
		/**
		 * 使用PHP Sesion，Readonl，未启动 这3种情况下不需要保存
		 */
		if( $this->use_php_session or !$this->isStart or $this->readonly ){
			return true;
		}
		//设置为Session关闭状态
		$this->isStart = false;
		$key           = self::$cache_prefix.$this->sessID;
		return $this->cache->set( $key, serialize( $_SESSION ), $this->session_lifetime );
	}
}

class SessionException extends \Exception
{
}
