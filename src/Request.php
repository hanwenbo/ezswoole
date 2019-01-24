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
	 * @var array 当前调度信息
	 */
	protected $dispatch = [];
	protected $module;
	protected $controller;
	protected $action;
	// 当前语言集
	protected $langset;

	/**
	 * @var array 请求参数
	 */
	protected $param = [];
	protected $get = [];
	protected $post = [];
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

	private $EasySwooleRequest;

	final protected function __construct( EasySwooleRequest $request )
	{
		$this->EasySwooleRequest = $request;
		$this->get               = $request->getSwooleRequest()->get ? $request->getSwooleRequest()->get : [];
		$this->post              = $request->getSwooleRequest()->post ? $request->getSwooleRequest()->post : [];
		$this->file              = $request->getSwooleRequest()->files ? $request->getSwooleRequest()->files : [];
		$this->header            = $request->getSwooleRequest()->header;
		$this->cookie            = $request->getSwooleRequest()->cookie ? $request->getSwooleRequest()->cookie : [];
		$this->server            = $request->getSwooleRequest()->server;
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

	final public function getEasySwooleRequest()
	{
		return $this->EasySwooleRequest;
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
		$server = $this->getEasySwooleRequest()->getServerParams();
		if( !is_null( $url ) && true !== $url ){
			$this->url = $url;
			return $this;
		} elseif( !$this->url ){
			if( isset( $server['http_x_rewrite_url'] ) ){
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
	 * 获取当前请求URL的pathinfo信息（含URL后缀）
	 * @access public
	 * @return string
	 */
	public function pathinfo()
	{
		if( is_null( $this->pathinfo ) ){
			$_server        = $this->getEasySwooleRequest()->getSwooleRequest()->server;
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
		return $this->getEasySwooleRequest()->getUri()->getPath();
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
		$_server = $this->getEasySwooleRequest()->getServerParams();
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
	 * TODO 测试
	 * 当前的请求类型
	 * @access public
	 * @param bool $method true 获取原始请求类型
	 * @return string
	 */
	public function method( $method = false ) : string
	{
		if( true === $method ){
			return (isset( $this->server['request_method'] ) ?? $this->server['request_method']);
		} elseif( !$this->method ){
			if( isset( $_server['http_x_method_override'] ) ){
				$this->method = strtoupper( $this->server['http_x_method_override'] );
			} else{
				$this->method = strtoupper( isset( $this->server['request_method'] ) ? $this->server['request_method'] : null );
			}
		}
		return $this->method;
	}

	public function isGet() : bool
	{
		return $this->method() == 'GET';
	}

	public function isPost() : bool
	{
		return $this->method() == 'POST';
	}

	public function isPut() : bool
	{
		return $this->method() == 'PUT';
	}

	public function isDelete() : bool
	{
		return $this->method() == 'DELETE';
	}

	public function isHead() : bool
	{
		return $this->method() == 'HEAD';
	}

	public function isPatch() : bool
	{
		return $this->method() == 'PATCH';
	}

	public function isOptions() : bool
	{
		return $this->method() == 'OPTIONS';
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
			$this->param = array_merge( $this->get( false ), $vars );
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
			$_post = $this->getEasySwooleRequest()->getParsedBody();
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
			$content = $this->getEasySwooleRequest()->getSwooleRequest()->rawContent();
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

	public function header( string $name = '', $default = null )
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

	public function raw() : ?string
	{
		return $this->getEasySwooleRequest()->getSwooleRequest()->rawcontent();
	}

	/**
	 * todo
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
		if( empty( $this->type ) ){
			$param = $this->$type();
		} else{
			$param = $this->type;
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
		return $this->getEasySwooleRequest()->getUri()->getQuery();
	}

	public function host() : string
	{
		return $this->getEasySwooleRequest()->getUri()->getHost();
	}

	public function port() : int
	{
		return $this->getEasySwooleRequest()->getUri()->getPort();
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
		$content = $this->getEasySwooleRequest()->getSwooleRequest()->rawContent();
		if( is_null( $content ) ){
			return $content;
		} else{
			return null;
		}
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
}
