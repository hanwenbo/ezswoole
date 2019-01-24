<?php

namespace ezswoole;

use EasySwoole\Http\Request as EasySwooleRequest;

class Request
{
	/**
	 * @var object 对象实例
	 */
	private static $instance;

	protected $method;
	/**
	 * @var string 域名（含协议和端口）
	 */
	protected $domain;

	/**
	 * @var string URL地址
	 */
	protected $url;

	/**
	 * @var string 基础URL
	 */
	protected $baseUrl;

	/**
	 * @var string 当前执行的文件
	 */
	protected $baseFile;

	/**
	 * @var string 访问的ROOT地址
	 */
	protected $root;

	/**
	 * @var string pathinfo
	 */
	protected $pathinfo;

	/**
	 * @var string pathinfo（不含后缀）
	 */
	protected $path;

	/**
	 * @var array 当前路由信息
	 */
	protected $routeInfo = [];

	/**
	 * @var array 环境变量
	 */
	protected $env;

	/**
	 * @var array 当前调度信息
	 */
	protected $dispatch = [];
	protected $module;
	protected $controller;
	protected $action;
	// 当前语言集
	protected $langset;


	protected $esRequest;

	/**
	 * @var array 请求参数
	 */
	protected $param = [];
	protected $get = [];
	protected $post = [];
	protected $route = [];
	protected $put;
	protected $session = [];
	protected $file = [];
	protected $cookie = [];
	protected $server = [];
	protected $header = [];
	protected $raw = [];

	/**
	 * @var array 资源类型
	 */
	protected $mimeType
		= [
			'xml'   => 'application/xml,text/xml,application/x-xml',
			'json'  => 'application/json,text/x-json,application/jsonrequest,text/json',
			'js'    => 'text/javascript,application/javascript,application/x-javascript',
			'css'   => 'text/css',
			'rss'   => 'application/rss+xml',
			'yaml'  => 'application/x-yaml,text/yaml',
			'atom'  => 'application/atom+xml',
			'pdf'   => 'application/pdf',
			'text'  => 'text/plain',
			'image' => 'image/png,image/jpg,image/jpeg,image/pjpeg,image/gif,image/webp,image/*',
			'csv'   => 'text/csv',
			'html'  => 'text/html,application/xhtml+xml,*/*',
		];

	protected $content;

	// 全局过滤规则
	protected $filter;
	// Hook扩展方法
	protected static $hook = [];
	// 绑定的属性
	protected $bind = [];
	// php://input
	protected $input;
	// 请求缓存
	protected $cache;
	// 缓存是否检查
	protected $isCheckCache;

	final protected function __construct( EasySwooleRequest $request )
	{
		$this->esRequest = $request;
		$this->get       = $request->getSwooleRequest()->get ? $request->getSwooleRequest()->get : [];
		$this->post      = $request->getSwooleRequest()->post ? $request->getSwooleRequest()->post : [];
		$this->file      = $request->getSwooleRequest()->files ? $request->getSwooleRequest()->files : [];
		$this->header    = $request->getSwooleRequest()->header;
		$this->cookie    = $request->getSwooleRequest()->cookie ? $request->getSwooleRequest()->cookie : [];
		$this->server    = $request->getSwooleRequest()->server;
	}

	final public static function getInstance( EasySwooleRequest $request = null )
	{
		if( $request ){
			self::$instance = new static( $request );
		}
		return self::$instance;
	}

	final public static function clearInstance() : void
	{
		self::$instance = null;
	}

	final public function getEsRequest()
	{
		return $this->esRequest;
	}

	/**
	 * 设置或获取当前包含协议的域名
	 * @access public
	 * @param string $domain 域名
	 * @return string
	 */
	public function domain( $domain = null )
	{
		if( !is_null( $domain ) ){
			$this->domain = $domain;
			return $this;
		} elseif( !$this->domain ){
			$this->domain = $this->scheme().'://'.$this->host();
		}
		return $this->domain;
	}

	/**
	 * 设置或获取当前完整URL 包括QUERY_STRING
	 * @access public
	 * @param string|true $url URL地址 true 带域名获取
	 * @return string
	 */
	public function url( $url = null )
	{
		$server = $this->esRequest->getServerParams();
		if( !is_null( $url ) && true !== $url ){
			$this->url = $url;
			return $this;
		} elseif( !$this->url ){
			if( IS_CLI ){
				$this->url = isset( $server['argv'][1] ) ? $server['argv'][1] : '';
			} elseif( isset( $server['http_x_rewrite_url'] ) ){
				$this->url = $server['http_x_rewrite_url'];
			} elseif( isset( $server['request_uri'] ) ){
				$this->url = $server['request_uri'];
			} elseif( isset( $server['orig_path_info'] ) ){
				$this->url = $server['orig_path_info'].(!empty( $server['query_string'] ) ? '?'.$server['query_string'] : '');
			} else{
				$this->url = '';
			}
		}
		return true === $url ? $this->domain().$this->url : $this->url;
	}

	/**
	 * 设置或获取当前URL 不含QUERY_STRING
	 * @access public
	 * @param string $url URL地址
	 * @return string
	 */
	public function baseUrl( $url = null )
	{
		if( !is_null( $url ) && true !== $url ){
			$this->baseUrl = $url;
			return $this;
		} elseif( !$this->baseUrl ){
			$str           = $this->url();
			$this->baseUrl = strpos( $str, '?' ) ? strstr( $str, '?', true ) : $str;
		}
		return true === $url ? $this->domain().$this->baseUrl : $this->baseUrl;
	}

	/**
	 * 设置或获取当前执行的文件 SCRIPT_NAME
	 * @access public
	 * @param string $file 当前执行的文件
	 * @return string
	 */
	public function baseFile( $file = null )
	{
		if( !is_null( $file ) && true !== $file ){
			$this->baseFile = $file;
			return $this;
		} elseif( !$this->baseFile ){
			$url = '';
			if( !IS_CLI ){
				$_server     = $this->esRequest->getServerParams();
				$script_name = basename( $_server['script_filename'] );
				if( basename( $_server['script_name'] ) === $script_name ){
					$url = $_server['script_name'];
				} elseif( basename( $_server['php_self'] ) === $script_name ){
					$url = $_server['php_self'];
				} elseif( isset( $_server['orig_script_name'] ) && basename( $_server['orig_script_name'] ) === $script_name ){
					$url = $_server['orig_script_name'];
				} elseif( ($pos = strpos( $_server['php_self'], '/'.$script_name )) !== false ){
					$url = substr( $_server['script_name'], 0, $pos ).'/'.$script_name;
				} elseif( isset( $_server['document_root'] ) && strpos( $_server['script_filename'], $_server['document_root'] ) === 0 ){
					$url = str_replace( '\\', '/', str_replace( $_server['document_root'], '', $_server['script_filename'] ) );
				}
			}
			$this->baseFile = $url;
		}
		return true === $file ? $this->domain().$this->baseFile : $this->baseFile;
	}

	/**
	 * 设置或获取URL访问根地址
	 * @access public
	 * @param string $url URL地址
	 * @return string
	 */
	public function root( $url = null )
	{
		if( !is_null( $url ) && true !== $url ){
			$this->root = $url;
			return $this;
		} elseif( !$this->root ){
			$file = $this->baseFile();
			if( $file && 0 !== strpos( $this->url(), $file ) ){
				$file = str_replace( '\\', '/', dirname( $file ) );
			}
			$this->root = rtrim( $file, '/' );
		}
		return true === $url ? $this->domain().$this->root : $this->root;
	}

	/**
	 * todo 未测试
	 * 获取当前请求URL的pathinfo信息（含URL后缀）
	 * @access public
	 * @return string
	 */
	public function pathinfo()
	{
		if( is_null( $this->pathinfo ) ){
			$_server = $this->esRequest->getServerParams();
			if( IS_CLI ){
				// cli模式下 index.php module/controller/action/params/...
				$_server['path_info'] = isset( $_server['argv'][1] ) ? $_server['argv'][1] : '';
			}


			$this->pathinfo = empty( $_server['path_info'] ) ? '/' : ltrim( $_server['path_info'], '/' );
		}
		return $this->pathinfo;
	}

	/**
	 * 获取当前请求URL的pathinfo信息(不含URL后缀)
	 * @access public
	 * @return string
	 */
	public function path()
	{
		return $this->esRequest->getUri()->getPath();
	}

	/**
	 * 当前URL的访问后缀
	 * @access public
	 * @return string
	 */
	public function ext()
	{
		return pathinfo( $this->pathinfo(), PATHINFO_EXTENSION );
	}

	/**
	 * 获取当前请求的时间
	 * @access public
	 * @param bool $float 是否使用浮点类型
	 * @return integer|float
	 */
	public function time( $float = false )
	{
		$_server = $this->esRequest->getServerParams();
		return $float ? $_server['request_time_float'] : $_server['request_time'];
	}

	/**
	 * 当前请求的资源类型
	 * @access public
	 * @return false|string
	 */
	public function type()
	{

		$accept = $this->server( 'http_accept' );
		if( empty( $accept ) ){
			return false;
		}

		foreach( $this->mimeType as $key => $val ){
			$array = explode( ',', $val );
			foreach( $array as $k => $v ){
				if( stristr( $accept, $v ) ){
					return $key;
				}
			}
		}
		return false;
	}

	/**
	 * 设置资源类型
	 * @access public
	 * @param string|array $type 资源类型名
	 * @param string       $val  资源类型
	 * @return void
	 */
	public function mimeType( $type, $val = '' )
	{
		if( is_array( $type ) ){
			$this->mimeType = array_merge( $this->mimeType, $type );
		} else{
			$this->mimeType[$type] = $val;
		}
	}

	/**
	 * 当前的请求类型
	 * @access public
	 * @param bool $method true 获取原始请求类型
	 * @return string
	 */
	public function method( $method = false )
	{

		if( true === $method ){
			// 获取原始请求类型
			return IS_CLI ? 'GET' : (isset( $this->server['request_method'] ) ?? $this->server['request_method']);
		} elseif( !$this->method ){
			if( isset( $this->post[Config::get( 'var_method' )] ) ){
				$this->method = strtoupper( $this->post[Config::get( 'var_method' )] );
				$this->{$this->method}( $this->post );
			} elseif( isset( $_server['http_x_method_override'] ) ){
				$this->method = strtoupper( $this->server['http_x_method_override'] );
			} else{
				$this->method = strtoupper( isset( $this->server['request_method'] ) ? $this->server['request_method'] : null );
			}
		}
		return $this->method;
	}

	/**
	 * 是否为GET请求
	 * @access public
	 * @return bool
	 */
	public function isGet()
	{
		return $this->method() == 'GET';
	}

	/**
	 * 是否为POST请求
	 * @access public
	 * @return bool
	 */
	public function isPost()
	{
		return $this->method() == 'POST';
	}

	/**
	 * 是否为PUT请求
	 * @access public
	 * @return bool
	 */
	public function isPut()
	{
		return $this->method() == 'PUT';
	}

	/**
	 * 是否为DELTE请求
	 * @access public
	 * @return bool
	 */
	public function isDelete()
	{
		return $this->method() == 'DELETE';
	}

	/**
	 * 是否为HEAD请求
	 * @access public
	 * @return bool
	 */
	public function isHead()
	{
		return $this->method() == 'HEAD';
	}

	/**
	 * 是否为PATCH请求
	 * @access public
	 * @return bool
	 */
	public function isPatch()
	{
		return $this->method() == 'PATCH';
	}

	/**
	 * 是否为OPTIONS请求
	 * @access public
	 * @return bool
	 */
	public function isOptions()
	{
		return $this->method() == 'OPTIONS';
	}

	/**
	 * 是否为cli
	 * @access public
	 * @return bool
	 */
	public function isCli()
	{
		return PHP_SAPI == 'cli';
	}

	/**
	 * 是否为cgi
	 * @access public
	 * @return bool
	 */
	public function isCgi()
	{
		return strpos( PHP_SAPI, 'cgi' ) === 0;
	}

	/**
	 * 获取当前请求的参数
	 * @access public
	 * @param string|array $name    变量名
	 * @param mixed        $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function param( $name = '', $default = null, $filter = '' )
	{
		if( empty( $this->param ) ){
			$method = $this->method( true );
			// 自动获取请求变量
			switch( $method ){
			case 'POST':
				$vars = $this->post( false );
			break;
			case 'PUT':
			case 'DELETE':
			case 'PATCH':
				$vars = $this->put( false );
			break;
			default:
				$vars = [];
			}
			// 当前请求参数和URL地址中的参数合并
			$this->param = array_merge( $this->get( false ), $vars, $this->route( false ) );
		}
		if( true === $name ){
			// 获取包含文件上传信息的数组
			$file = $this->file();
			$data = is_array( $file ) ? array_merge( $this->param, $file ) : $this->param;
			return $this->input( $data, '', $default, $filter );
		}
		return $this->input( $this->param, $name, $default, $filter );
	}

	/**
	 * 设置获取路由参数
	 * @access public
	 * @param string|array $name    变量名
	 * @param mixed        $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function route( $name = '', $default = null, $filter = '' )
	{
		if( is_array( $name ) ){
			$this->param = [];
			return $this->route = array_merge( $this->route, $name );
		}
		return $this->input( $this->route, $name, $default, $filter );
	}

	/**
	 * 设置获取GET参数
	 * @access public
	 * @param string|array $name    变量名
	 * @param mixed        $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function get( $name = '', $default = null, $filter = '' )
	{
		if( is_array( $name ) ){
			$this->param = [];
			return $this->get = array_merge( $this->get, $name );
		}
		return $this->input( $this->get, $name, $default, $filter );
	}

	/**
	 * 设置获取POST参数
	 * @access public
	 * @param string       $name    变量名
	 * @param mixed        $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function post( $name = '', $default = null, $filter = '' )
	{

		if( empty( $this->post ) ){
			$_post = $this->esRequest->getParsedBody();
			if( empty( $_post ) && false !== strpos( $this->contentType(), 'application/json' ) ){
				$this->post = $this->raw();
			} else{
				$this->post = $_post;
			}
		}
		if( is_array( $name ) ){
			$this->param = [];
			return $this->post = array_merge( $this->post, $name );
		}
		return $this->input( $this->post, $name, $default, $filter );
	}

	/**
	 * 设置获取PUT参数
	 * @access public
	 * @param string|array $name    变量名
	 * @param mixed        $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function put( $name = '', $default = null, $filter = '' )
	{
		if( is_null( $this->put ) ){
			$content = $this->getEsRequest()->getSwooleRequest()->rawContent();
			if( false !== strpos( $this->contentType(), 'application/json' ) ){
				$this->put = (array)json_decode( $content, true );
			} else{
				parse_str( $content, $this->put );
			}
		}
		if( is_array( $name ) ){
			$this->param = [];
			return $this->put = is_null( $this->put ) ? $name : array_merge( $this->put, $name );
		}

		return $this->input( $this->put, $name, $default, $filter );
	}

	/**
	 * 设置获取DELETE参数
	 * @access public
	 * @param string|array $name    变量名
	 * @param mixed        $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function delete( $name = '', $default = null, $filter = '' )
	{
		return $this->put( $name, $default, $filter );
	}

	/**
	 * 设置获取PATCH参数
	 * @access public
	 * @param string|array $name    变量名
	 * @param mixed        $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function patch( $name = '', $default = null, $filter = '' )
	{
		return $this->put( $name, $default, $filter );
	}

	/**
	 * 获取session数据
	 * @access public
	 * @param string|array $name    数据名称
	 * @param string       $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function session( $name = '', $default = null, $filter = '' )
	{
		if( empty( $this->session ) ){
			$this->session = Session::get();
		}
		if( is_array( $name ) ){
			return $this->session = array_merge( $this->session, $name );
		}
		return $this->input( $this->session, $name, $default, $filter );
	}

	/**
	 * 获取cookie参数
	 * @access public
	 * @param string|array $name    数据名称
	 * @param string       $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function cookie( $name = '', $default = null, $filter = '' )
	{
		if( empty( $this->cookie ) ){
			$this->cookie = Cookie::get();
		}
		if( is_array( $name ) ){
			return $this->cookie = array_merge( $this->cookie, $name );
		} elseif( !empty( $name ) ){
			$data = Cookie::has( $name ) ? Cookie::get( $name ) : $default;
		} else{
			$data = $this->cookie;
		}

		// 解析过滤器
		$filter = $this->getFilter( $filter, $default );

		if( is_array( $data ) ){
			array_walk_recursive( $data, [$this, 'filterValue'], $filter );
			reset( $data );
		} else{
			$this->filterValue( $data, $name, $filter );
		}
		return $data;
	}

	/**
	 * 获取server参数
	 * @access public
	 * @param string|array $name    数据名称
	 * @param string       $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function server( $name = '', $default = null, $filter = '' )
	{
		if( is_array( $name ) ){
			return $this->server = array_merge( $this->server, $name );
		}
		return $this->input( $this->server, false === $name ? false : strtoupper( $name ), $default, $filter );
	}

	/**
	 * 获取上传的文件信息
	 * @access public
	 * @param string|array $name 名称
	 * @return null|array|\ezswoole\File
	 */
	public function file( $name = '' )
	{
		if( is_array( $name ) ){
			return $this->file = array_merge( $this->file, $name );
		}
		$files = $this->file;
		if( !empty( $files ) ){
			if( $name == '' ){
				return $files;
			} else{
				return $files[$name];
			}
		}
		return null;
	}

	/**
	 * 获取环境变量
	 * @param string|array $name    数据名称
	 * @param string       $default 默认值
	 * @param string|array $filter  过滤方法
	 * @return mixed
	 */
	public function env( $name = '', $default = null, $filter = '' )
	{
		if( is_array( $name ) ){
			return $this->env = array_merge( $this->env, $name );
		}
		return $this->input( $this->env, false === $name ? false : strtoupper( $name ), $default, $filter );
	}

	/**
	 * 设置或者获取当前的Header
	 * @access public
	 * @param string|array $name    header名称
	 * @param string       $default 默认值
	 * @return mixed
	 */
	public function header( $name = '', $default = null )
	{
		if( is_array( $name ) ){
			return $this->header = array_merge( $this->header, $name );
		}
		if( '' === $name ){
			return $this->header;
		}
		$name = str_replace( '_', '-', strtolower( $name ) );
		return isset( $this->header[$name] ) ? $this->header[$name] : $default;
	}

	public function raw( $raw = null )
	{
		if( is_null( $raw ) ){
			$content = $this->esRequest->getBody()->__toString();
			$raw     = (array)json_decode( $content, true );
			return (array)$raw;
		} else{
			return (array)$this->raw = $raw;
		}
	}

	/**
	 * 获取变量 支持过滤和默认值
	 * @param array        $data    数据源
	 * @param string|false $name    字段名
	 * @param mixed        $default 默认值
	 * @param string|array $filter  过滤函数
	 * @return mixed
	 */
	public function input( $data = [], $name = '', $default = null, $filter = '' )
	{
		if( false === $name ){
			// 获取原始数据
			return $data;
		}
		$name = (string)$name;
		if( '' != $name ){
			// 解析name
			if( strpos( $name, '/' ) ){
				list( $name, $type ) = explode( '/', $name );
			} else{
				$type = 's';
			}
			// 按.拆分成多维数组进行判断
			foreach( explode( '.', $name ) as $val ){
				if( isset( $data[$val] ) ){
					$data = $data[$val];
				} else{
					// 无输入数据，返回默认值
					return $default;
				}
			}
			if( is_object( $data ) ){
				return $data;
			}
		}

		// 解析过滤器
		$filter = $this->getFilter( $filter, $default );

		if( is_array( $data ) ){
			array_walk_recursive( $data, [$this, 'filterValue'], $filter );
			reset( $data );
		} else{
			$this->filterValue( $data, $name, $filter );
		}

		if( isset( $type ) && $data !== $default ){
			// 强制类型转换
			$this->typeCast( $data, $type );
		}
		return $data;
	}

	/**
	 * 设置或获取当前的过滤规则
	 * @param mixed $filter 过滤规则
	 * @return mixed
	 */
	public function filter( $filter = null )
	{
		if( is_null( $filter ) ){
			return $this->filter;
		} else{
			$this->filter = $filter;
		}
	}

	protected function getFilter( $filter, $default )
	{
		if( is_null( $filter ) ){
			$filter = [];
		} else{
			$filter = $filter ?: $this->filter;
			if( is_string( $filter ) && false === strpos( $filter, '/' ) ){
				$filter = explode( ',', $filter );
			} else{
				$filter = (array)$filter;
			}
		}

		$filter[] = $default;
		return $filter;
	}

	/**
	 * 递归过滤给定的值
	 * @param mixed $value   键值
	 * @param mixed $key     键名
	 * @param array $filters 过滤方法+默认值
	 * @return mixed
	 */
	private function filterValue( &$value, $key, $filters )
	{
		$default = array_pop( $filters );
		foreach( $filters as $filter ){
			if( is_callable( $filter ) ){
				// 调用函数或者方法过滤
				$value = call_user_func( $filter, $value );
			} elseif( is_scalar( $value ) ){
				if( false !== strpos( $filter, '/' ) ){
					// 正则过滤
					if( !preg_match( $filter, $value ) ){
						// 匹配不成功返回默认值
						$value = $default;
						break;
					}
				} elseif( !empty( $filter ) ){
					// filter函数不存在时, 则使用filter_var进行过滤
					// filter为非整形值时, 调用filter_id取得过滤id
					$value = filter_var( $value, is_int( $filter ) ? $filter : filter_id( $filter ) );
					if( false === $value ){
						$value = $default;
						break;
					}
				}
			}
		}
		$this->filterExp( $value );
	}

	/**
	 * 过滤表单中的表达式
	 * @param string $value
	 * @return void
	 */
	public function filterExp( &$value )
	{
		// 过滤查询特殊字符
		if( is_string( $value ) && preg_match( '/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT LIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value ) ){
			$value .= ' ';
		}
	}

	/**
	 * 强制类型转换
	 * @param string $data
	 * @param string $type
	 * @return mixed
	 */
	private function typeCast( &$data, $type )
	{
		switch( strtolower( $type ) ){
			// 数组
		case 'a':
			$data = (array)$data;
		break;
			// 数字
		case 'd':
			$data = (int)$data;
		break;
			// 浮点
		case 'f':
			$data = (float)$data;
		break;
			// 布尔
		case 'b':
			$data = (boolean)$data;
		break;
			// 字符串
		case 's':
		default:
			if( is_scalar( $data ) ){
				$data = (string)$data;
			} else{
				throw new \InvalidArgumentException( 'variable type error：'.gettype( $data ) );
			}
		}
	}

	/**
	 * 是否存在某个请求参数
	 * @access public
	 * @param string $name       变量名
	 * @param string $type       变量类型
	 * @param bool   $checkEmpty 是否检测空值
	 * @return mixed
	 */
	public function has( $name, $type = 'param', $checkEmpty = false )
	{
		if( empty( $this->$type ) ){
			$param = $this->$type();
		} else{
			$param = $this->$type;
		}
		// 按.拆分成多维数组进行判断
		foreach( explode( '.', $name ) as $val ){
			if( isset( $param[$val] ) ){
				$param = $param[$val];
			} else{
				return false;
			}
		}
		return ($checkEmpty && '' === $param) ? false : true;
	}

	/**
	 * 获取指定的参数
	 * @access public
	 * @param string|array $name 变量名
	 * @param string       $type 变量类型
	 * @return mixed
	 */
	public function only( $name, $type = 'param' )
	{
		$param = $this->$type();
		if( is_string( $name ) ){
			$name = explode( ',', $name );
		}
		$item = [];
		foreach( $name as $key ){
			if( isset( $param[$key] ) ){
				$item[$key] = $param[$key];
			}
		}
		return $item;
	}

	/**
	 * 排除指定参数获取
	 * @access public
	 * @param string|array $name 变量名
	 * @param string       $type 变量类型
	 * @return mixed
	 */
	public function except( $name, $type = 'param' )
	{
		$param = $this->$type();
		if( is_string( $name ) ){
			$name = explode( ',', $name );
		}
		foreach( $name as $key ){
			if( isset( $param[$key] ) ){
				unset( $param[$key] );
			}
		}
		return $param;
	}

	/**
	 * 当前是否ssl
	 * @access public
	 * @return bool
	 */
	public function isSsl()
	{
		$server = $this->server;
		$header = $this->header;
		if( isset( $header['https'] ) && ('1' == $header['https'] || 'on' == strtolower( $header['https'] )) ){
			return true;
		} elseif( isset( $server['request_scheme'] ) && 'https' == $server['request_scheme'] ){
			return true;
		} elseif( isset( $server['server_port'] ) && ('443' == $server['server_port']) ){
			return true;
		} elseif( isset( $server['http_x_forwarded_proto'] ) && 'https' == $server['http_x_forwarded_proto'] ){
			return true;
		}
		return false;
	}

	/**
	 * 当前是否Ajax请求
	 * @access public
	 * @param bool $ajax true 获取原始ajax请求
	 * @return bool
	 */
	public function isAjax( $ajax = false )
	{
		$value  = $this->server( 'http_x_requested_with', '', 'strtolower' );
		$result = ('xmlhttprequest' == $value) ? true : false;
		if( true === $ajax ){
			return $result;
		} else{
			return $this->param( Config::get( 'var_ajax' ) ) ? true : $result;
		}
	}

	/**
	 * 当前是否Pjax请求
	 * @access public
	 * @param bool $pjax true 获取原始pjax请求
	 * @return bool
	 */
	public function isPjax( $pjax = false )
	{
		$result = !is_null( $this->server( 'http_x_pjax' ) ) ? true : false;
		if( true === $pjax ){
			return $result;
		} else{
			return $this->param( Config::get( 'var_pjax' ) ) ? true : $result;
		}
	}

	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param boolean $adv  是否进行高级模式获取（有可能被伪装）
	 * @return mixed
	 */
	public function ip( $type = 0, $adv = false )
	{
		$_server = $this->server;
		$type    = $type ? 1 : 0;
		static $ip = null;
		if( null !== $ip ){
			return $ip[$type];
		}

		if( $adv ){
			if( isset( $_server['http_x_forwarded_for'] ) ){
				$arr = explode( ',', $_server['http_x_forwarded_for'] );
				$pos = array_search( 'unknown', $arr );
				if( false !== $pos ){
					unset( $arr[$pos] );
				}
				$ip = trim( current( $arr ) );
			} elseif( isset( $_server['http_client_ip'] ) ){
				$ip = $_server['http_client_ip'];
			} elseif( isset( $_server['remote_addr'] ) ){
				$ip = $_server['remote_addr'];
			}
		} elseif( isset( $_server['remote_addr'] ) ){
			$ip = $_server['remote_addr'];
		}
		// IP地址合法验证
		$long = sprintf( "%u", ip2long( $ip ) );
		$ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];
		return $ip[$type];
	}

	/**
	 * 检测是否使用手机访问
	 * @access public
	 * @return bool
	 */
	public function isMobile()
	{
		$server = $this->server;

		if( isset( $server['http_via'] ) && stristr( $server['http_via'], "wap" ) ){
			return true;
		} elseif( isset( $server['http_accept'] ) && strpos( strtoupper( $server['http_accept'] ), "vnd.wap.wml" ) ){
			return true;
		} elseif( isset( $server['http_x_wap_profile'] ) || isset( $server['http_profile'] ) ){
			return true;
		} elseif( isset( $server['http_user_agent'] ) && preg_match( '/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $server['http_user_agent'] ) ){
			return true;
		} else{
			return false;
		}
	}

	public function scheme() : string
	{
		return $this->isSsl() ? 'https' : 'http';
	}

	public function query() : string
	{
		return $this->esRequest->getUri()->getQuery();
	}

	public function host() : string
	{
		return $this->esRequest->getUri()->getHost();
	}

	public function port() : int
	{
		return $this->esRequest->getUri()->getPort();
	}

	public function protocol() : int
	{
		return $this->server( 'server_protocol' );
	}

	/**
	 * 当前请求 REMOTE_PORT
	 * @access public
	 * @return integer
	 */
	public function remotePort()
	{
		return $this->server( 'remote_port' );
	}

	/**
	 * 当前请求 HTTP_CONTENT_TYPE
	 * @access public
	 * @return string
	 */
	public function contentType()
	{
		if( isset( $this->header['content-type'] ) ){
			$contentType = $this->header['content-type'];
			if( !empty( $contentType ) ){
				if( strpos( $contentType, ';' ) ){
					list( $type ) = explode( ';', $contentType );
				} else{
					$type = $contentType;
				}
				return trim( $type );
			}
		} else{
			return '';
		}

	}

	/**
	 * 获取当前请求的路由信息
	 * @access public
	 * @param array $route 路由名称
	 * @return array
	 */
	public function routeInfo( $route = [] )
	{
		if( !empty( $route ) ){
			$this->routeInfo = $route;
		} else{
			return $this->routeInfo;
		}
	}

	/**
	 * 设置或者获取当前请求的调度信息
	 * @access public
	 * @param array $dispatch 调度信息
	 * @return array
	 */
	public function dispatch( $dispatch = null )
	{
		if( !is_null( $dispatch ) ){
			$this->dispatch = $dispatch;
		}
		return $this->dispatch;
	}

	/**
	 * 设置或者获取当前的模块名
	 * @access public
	 * @param string $module 模块名
	 * @return string|Request
	 */
	public function module( $module = null )
	{
		if( !is_null( $module ) ){
			$this->module = $module;
			return $this;
		} else{
			$path     = $this->path();
			$path_arr = explode( "/", strtolower( $path ) );
			return $path_arr[0];
		}
	}

	/**
	 * 设置或者获取当前的控制器名
	 * @access public
	 * @param string $controller 控制器名
	 * @return string|Request
	 */
	public function controller( $controller = null )
	{
		if( !is_null( $controller ) ){
			$this->controller = $controller;
			return $this;
		} else{
			$path     = $this->path();
			$path_arr = explode( "/", strtolower( $path ) );
			return $path_arr[2] ?: '';
		}
	}

	/**
	 * 设置或者获取当前的操作名
	 * @access public
	 * @param string $action 操作名
	 * @return string|Request
	 */
	public function action( $action = null )
	{

		if( !is_null( $action ) ){
			$this->action = $action;
			return $this;
		} else{
			$path     = $this->path();
			$path_arr = explode( "/", strtolower( $path ) );
			return $path_arr[3] ?: '';
		}
	}

	/**
	 * 设置或者获取当前的语言
	 * @access public
	 * @param string $lang 语言名
	 * @return string|Request
	 */
	public function langset( $lang = null )
	{
		if( !is_null( $lang ) ){
			$this->langset = $lang;
			return $this;
		} else{
			return $this->langset ?: '';
		}
	}

	public function getContent() : ?string
	{
		$content = $this->esRequest->getSwooleRequest()->rawContent();
		if( is_null( $content ) ){
			return $content;
		} else{
			return null;
		}
	}

	/**
	 * 获取当前请求的php://input
	 * @access public
	 * @return string
	 */
	public function getInput() : ?string
	{
		return $this->getEsRequest()->getSwooleRequest()->rawContent();
	}

	/**
	 * 生成请求令牌
	 * @access public
	 * @param string $name 令牌名称
	 * @param mixed  $type 令牌生成方法
	 * @return string
	 */
	public function token( $name = '__token__', $type = 'md5' )
	{
		$_server = $this->server;

		$type  = is_callable( $type ) ? $type : 'md5';
		$token = call_user_func( $type, $_server['request_time_float'] );
		if( $this->isAjax() ){
			header( $name.': '.$token );
		}
		Session::set( $name, $token );
		return $token;
	}

	/**
	 * 设置当前地址的请求缓存
	 * @access public
	 * @param string $key    缓存标识，支持变量规则 ，例如 item/:name/:id
	 * @param mixed  $expire 缓存有效期
	 * @param array  $except 缓存排除
	 * @param string $tag    缓存标签
	 * @return void
	 */
	public function cache( $key, $expire = null, $except = [], $tag = null )
	{
		$_server = $this->server;

		if( !is_array( $except ) ){
			$tag    = $except;
			$except = [];
		}

		if( false !== $key && $this->isGet() && !$this->isCheckCache ){
			// 标记请求缓存检查
			$this->isCheckCache = true;
			if( false === $expire ){
				// 关闭当前缓存
				return;
			}
			if( $key instanceof \Closure ){
				$key = call_user_func_array( $key, [$this] );
			} elseif( true === $key ){
				foreach( $except as $rule ){
					if( 0 === stripos( $this->url(), $rule ) ){
						return;
					}
				}
				// 自动缓存功能
				$key = '__URL__';
			} elseif( strpos( $key, '|' ) ){
				list( $key, $fun ) = explode( '|', $key );
			}
			// 特殊规则替换
			if( false !== strpos( $key, '__' ) ){
				$key = str_replace( ['__MODULE__', '__CONTROLLER__', '__ACTION__', '__URL__', ''], [
					$this->module,
					$this->controller,
					$this->action,
					md5( $this->url( true ) ),
				], $key );
			}

			if( false !== strpos( $key, ':' ) ){
				$param = $this->param();
				foreach( $param as $item => $val ){
					if( is_string( $val ) && false !== strpos( $key, ':'.$item ) ){
						$key = str_replace( ':'.$item, $val, $key );
					}
				}
			} elseif( strpos( $key, ']' ) ){
				if( '['.$this->ext().']' == $key ){
					// 缓存某个后缀的请求
					$key = md5( $this->url() );
				} else{
					return;
				}
			}
			if( isset( $fun ) ){
				$key = $fun( $key );
			}

			if( strtotime( $this->server( 'http_if_modified_since' ) ) + $expire > $_server['request_time'] ){
				// 读取缓存
				$response = Response::create()->code( 304 );
				throw new \ezswoole\exception\HttpResponseException( $response );
			} elseif( Cache::getInstance()->has( $key ) ){
				list( $content, $header ) = Cache::getInstance()->get( $key );
				$response = Response::create( $content )->header( $header );
				throw new \ezswoole\exception\HttpResponseException( $response );
			} else{
				$this->cache = [$key, $expire, $tag];
			}
		}
	}

	/**
	 * 读取请求缓存设置
	 * @access public
	 * @return array
	 */
	public function getCache()
	{
		return $this->cache;
	}

	static function clearGlobalVariables()
	{
		$_GET    = null;
		$_POST   = null;
		$_COOKIE = null;
		$_FILES  = null;
		$_SERVER = null;
	}

	static function setGlobalVariables( EasySwooleRequest $request )
	{
		$_GET    = isset( $request->getSwooleRequest()->get ) ? $request->getSwooleRequest()->get : [];
		$_POST   = isset( $request->getSwooleRequest()->post ) ? $request->getSwooleRequest()->post : [];
		$_COOKIE = isset( $request->getSwooleRequest()->cookie ) ? $request->getSwooleRequest()->cookie : [];
		$_FILES  = isset( $request->getSwooleRequest()->files ) ? $request->getSwooleRequest()->files : [];
		$server  = $request->getSwooleRequest()->server;
		$_SERVER = [];
		if( isset( $server ) ){
			foreach( $server as $key => $value ){
				$_SERVER[strtoupper( $key )] = $value;
			}
		}
	}

	/**
	 * 设置当前请求绑定的对象实例
	 * @access public
	 * @param string|array $name 绑定的对象标识
	 * @param mixed        $obj  绑定的对象实例
	 * @return mixed
	 */
	public function bind( $name, $obj = null )
	{
		if( is_array( $name ) ){
			$this->bind = array_merge( $this->bind, $name );
		} else{
			$this->bind[$name] = $obj;
		}
	}

	public function __set( $name, $value )
	{
		$this->bind[$name] = $value;
	}

	public function __get( $name )
	{
		return isset( $this->bind[$name] ) ? $this->bind[$name] : null;
	}

	public function __isset( $name )
	{
		return isset( $this->bind[$name] );
	}

	/**
	 * @param $method
	 * @param $args
	 * @return mixed
	 * @throws Exception
	 */
	public function __call( $method, $args )
	{
		if( array_key_exists( $method, self::$hook ) ){
			array_unshift( $args, $this );
			return call_user_func_array( self::$hook[$method], $args );
		} else{
			throw new Exception( 'method not exists:'.__CLASS__.'->'.$method );
		}
	}

	/**
	 * Hook 方法注入
	 * @access public
	 * @param string|array $method   方法名
	 * @param mixed        $callback callable
	 * @return void
	 */
	public static function hook( $method, $callback = null )
	{
		if( is_array( $method ) ){
			self::$hook = array_merge( self::$hook, $method );
		} else{
			self::$hook[$method] = $callback;
		}
	}
}
