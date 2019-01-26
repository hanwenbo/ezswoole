<?php

namespace ezswoole\db;

use EasySwoole\Mysqli\Mysqli;
use ezswoole\pool\MysqlPool;
use ezswoole\pool\MysqlObject;

class DbObject
{
	/**
	 * 先创建Mysqli的实例
	 *
	 * @var Mysqli
	 */
	private $db;
	/**
	 * 模型的路径
	 *
	 * @var string
	 */
	protected static $modelPath;
	/**
	 * 保存对象数据的数组
	 *
	 * @var array
	 */
	public $data;
	/**
	 * 要定义is对象的标志是新的或从数据库加载的
	 *
	 * @var boolean
	 */
	public $isNew = true;
	/**
	 * 一个持有的数组有*个对象，这些对象应该与main一起加载
	 * 对象与主对象在一起
	 *
	 * @var array
	 */
	private $_with = [];
	/**
	 * 分页的每个页面限制
	 *
	 * @var int
	 */
	public static $pageLimit = 20;
	/**
	 * 变量，该变量保存上一次paginate()查询的总页数
	 *
	 * @var int
	 */
	public static $totalPages = 0;
	/**
	 * 变量，该变量在分页查询期间保存返回的行数
	 * @var string
	 */
	public static $totalCount = 0;
	/**
	 * 保存插入/更新/选择错误的数组
	 *
	 * @var array
	 */
	public $errors = null;
	/**
	 * 对象的主键。'id'是默认值。
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';
	/**
	 * 对象的表名。默认情况下将使用类名
	 *
	 * @var string
	 */
	protected $dbTable;

	/**
	 * 在验证、准备和保存期间将跳过的字段的名称
	 * @var array
	 */
	protected $toSkip = [];

	/**
	 * 对象创建时预加载的数据
	 * @param null $data
	 */
	public function __construct( $data = null )
	{
		try{
			MysqlPool::invoke( function( MysqlObject $mysqlObject ){
				$this->db = $mysqlObject;
			} );
		} catch( \Throwable $throwable ){
			throw new $throwable;
		}

		if( empty ( $this->dbTable ) ){
			$this->dbTable = get_class( $this );
		}

		if( $data ){
			$this->data = $data;
		}
	}

	public function __set( string $name, $value )
	{
		if( property_exists( $this, 'hidden' ) && array_search( $name, $this->hidden ) !== false ){
			return;
		}
		$this->data[$name] = $value;
	}

	public function __get( string $name )
	{
		if( property_exists( $this, 'hidden' ) && array_search( $name, $this->hidden ) !== false ){
			return null;
		}

		if( isset ( $this->data[$name] ) && $this->data[$name] instanceof DbObject ){
			return $this->data[$name];
		}

		if( property_exists( $this, 'relations' ) && isset ( $this->relations[$name] ) ){
			$relationType = strtolower( $this->relations[$name][0] );
			$modelName    = $this->relations[$name][1];
			switch( $relationType ){
			case 'hasone':
				$key             = isset ( $this->relations[$name][2] ) ? $this->relations[$name][2] : $name;
				$obj             = new $modelName;
				$obj->returnType = $this->returnType;
				return $this->data[$name] = $obj->byId( $this->data[$key] );
			break;
			case 'hasmany':
				$key             = $this->relations[$name][2];
				$obj             = new $modelName;
				$obj->returnType = $this->returnType;
				return $this->data[$name] = $obj->where( $key, $this->data[$this->primaryKey] )->get();
			break;
			default:
			break;
			}
		}

		if( isset ( $this->data[$name] ) ){
			return $this->data[$name];
		}

		if( property_exists( $this->db, $name ) ){
			return $this->db->$name;
		}
	}

	public function __isset( string $name )
	{
		if( isset ( $this->data[$name] ) ){
			return isset ( $this->data[$name] );
		}

		if( property_exists( $this->db, $name ) ){
			return isset ( $this->db->$name );
		}
	}

	public function __unset( string $name )
	{
		unset ( $this->data[$name] );
	}

	/**
	 * 帮助函数来创建一个虚拟表类
	 *
	 * @param string tableName Table name
	 * @return DbObject
	 */
	public static function table( $tableName )
	{
		$tableName = preg_replace( "/[^-a-z0-9_]+/i", '', $tableName );
		if( !class_exists( $tableName ) ){
			eval ( "class $tableName extends DbObject {}" );
		}
		return new $tableName();
	}

	/**
	 * @return mixed insert id or false in case of failure
	 */
	public function insert()
	{
		if( !empty ( $this->timestamps ) && in_array( "createdAt", $this->timestamps ) ){
			$this->createdAt = date( "Y-m-d H:i:s" );
		}
		$sqlData = $this->prepareData();
		if( !$this->validate( $sqlData ) ){
			return false;
		}

		$id = $this->db->insert( $this->dbTable, $sqlData );
		if( !empty ( $this->primaryKey ) && empty ( $this->data[$this->primaryKey] ) ){
			$this->data[$this->primaryKey] = $id;
		}
		$this->isNew  = false;
		$this->toSkip = [];
		return $id;
	}

	/**
	 * 可选的更新数据应用于对象
	 * @param array $data
	 */
	public function update( $data = null )
	{
		if( empty ( $this->dbFields ) ){
			return false;
		}

		if( empty ( $this->data[$this->primaryKey] ) ){
			return false;
		}

		if( $data ){
			foreach( $data as $k => $v ){
				if( in_array( $k, $this->toSkip ) ){
					continue;
				}
				$this->$k = $v;
			}
		}

		if( !empty ( $this->timestamps ) && in_array( "updatedAt", $this->timestamps ) ){
			$this->updatedAt = date( "Y-m-d H:i:s" );
		}

		$sqlData = $this->prepareData();
		if( !$this->validate( $sqlData ) ){
			return false;
		}

		$this->db->where( $this->primaryKey, $this->data[$this->primaryKey] );
		$res          = $this->db->update( $this->dbTable, $sqlData );
		$this->toSkip = [];
		return $res;
	}

	/**
	 * 保存或更新对象
	 *
	 * @return mixed insert 失败时id或false
	 */
	public function save( $data = null )
	{
		if( $this->isNew ){
			return $this->insert();
		}
		return $this->update( $data );
	}

	/**
	 * 删除的方法。只在定义了对象primaryKey时才有效
	 *
	 * @return boolean 表示成功。0或1。
	 */
	public function delete()
	{
		if( empty ( $this->data[$this->primaryKey] ) )
			return false;

		$this->db->where( $this->primaryKey, $this->data[$this->primaryKey] );
		$res          = $this->db->delete( $this->dbTable );
		$this->toSkip = [];
		return $res;
	}

	/**
	 * 链接的方法，该方法将一个或多个字段附加到跳过
	 * @param mixed|array|false $field 字段名;数组的名字;空跳如果为假
	 * @return $this
	 */
	public function skip( $field )
	{
		if( is_array( $field ) ){
			foreach( $field as $f ){
				$this->toSkip[] = $f;
			}
		} else if( $field === false ){
			$this->toSkip = [];
		} else{
			$this->toSkip[] = $field;
		}
		return $this;
	}

	/**
	 * 通过主键获取对象
	 * todo $prefix
	 *
	 * @access public
	 * @param  string      $id     主键
	 * @param array|string $fields 要获取的字段的数组或昏迷分隔列表
	 *
	 * @return DbObject|array
	 */
	private function byId( $id, $fields = null )
	{
		$this->db->where( Mysqli::$prefix.$this->dbTable.'.'.$this->primaryKey, $id );
		return $this->getOne( $fields );
	}

	/**
	 * 函数的作用是:获取一个对象。大部分会和where()在一起
	 *
	 * @access public
	 * @param array|string $fields 要获取的字段的数组或昏迷分隔列表
	 *
	 * @return DbObject
	 */
	protected function getOne( $fields = null )
	{
		$this->processHasOneWith();
		$results = $this->db->ArrayBuilder()->getOne( $this->dbTable, $fields );
		if( $this->db->count == 0 )
			return null;

		$this->processArrays( $results );
		$this->data = $results;
		$this->processAllWith( $results );
		if( $this->returnType == 'Json' )
			return json_encode( $results );
		if( $this->returnType == 'Array' )
			return $results;

		$item        = new static ( $results );
		$item->isNew = false;

		return $item;
	}

	/**
	 * Fetch all objects
	 *
	 * @access public
	 * @param integer|array $limit  Array to define SQL limit in format Array ($count, $offset)
	 *                              or only $count
	 * @param array|string  $fields Array or coma separated list of fields to fetch
	 *
	 * @return array Array of DbObjects
	 */
	protected function get( $limit = null, $fields = null )
	{
		$objects = [];
		$this->processHasOneWith();
		$results = $this->db->get( $this->dbTable, $limit, $fields );
		if( $this->db->count == 0 )
			return null;

		foreach( $results as $k => &$r ){
			$this->processArrays( $r );
			$this->data = $r;
			$this->processAllWith( $r, false );
			if( $this->returnType == 'Object' ){
				$item        = new static ( $r );
				$item->isNew = false;
				$objects[$k] = $item;
			}
		}
		$this->_with = [];

		return $results;
	}

	/**
	 * Function to set witch hasOne or hasMany objects should be loaded togeather with a main object
	 *
	 * @access public
	 * @param string $objectName Object Name
	 *
	 * @return DbObject
	 */
	private function with( $objectName )
	{
		if( !property_exists( $this, 'relations' ) || !isset ( $this->relations[$objectName] ) )
			die ( "No relation with name $objectName found" );

		$this->_with[$objectName] = $this->relations[$objectName];

		return $this;
	}

	/**
	 * Function to join object with another object.
	 *
	 * @access public
	 * @param string $objectName Object Name
	 * @param string $key        Key for a join from primary object
	 * @param string $joinType   SQL join type: LEFT, RIGHT,  INNER, OUTER
	 * @param string $primaryKey SQL join On Second primaryKey
	 *
	 * @return DbObject
	 */
	private function join( $objectName, $key = null, $joinType = 'LEFT', $primaryKey = null )
	{
		$joinObj = new $objectName;
		if( !$key )
			$key = $objectName."id";

		if( !$primaryKey )
			$primaryKey = Mysqli::$prefix.$joinObj->dbTable.".".$joinObj->primaryKey;

		if( !strchr( $key, '.' ) )
			$joinStr = Mysqli::$prefix.$this->dbTable.".{$key} = ".$primaryKey; else
			$joinStr = Mysqli::$prefix."{$key} = ".$primaryKey;

		$this->db->join( $joinObj->dbTable, $joinStr, $joinType );
		return $this;
	}

	/**
	 * todo for test
	 * Function to get a total records count
	 *
	 * @return int
	 */
	protected function count()
	{
		$res = $this->db->getValue( $this->dbTable, "count(*)" );
		if( !$res ){
			return 0;
		} else{
			return $res;
		}
	}

	/**
	 * 对get()封装的分页
	 * todo
	 * @access public
	 * @param int          $page   页码
	 * @param array|string $fields 要获取的字段的数组或昏迷分隔列表
	 * @return array
	 */
	private function paginate( $page, $fields = null )
	{
		$this->db->pageLimit = self::$pageLimit;
		$this->processHasOneWith();
		$res              = $this->db->paginate( $this->dbTable, $page, $fields );
		self::$totalPages = $this->db->totalPages;
		self::$totalCount = $this->db->totalCount;
		if( $this->db->count == 0 )
			return null;

		foreach( $res as $k => &$r ){
			$this->processArrays( $r );
			$this->data = $r;
			$this->processAllWith( $r, false );
		}
		$this->_with = [];
		return $res;
	}

	/**
	 * 捕获对未定义方法的调用。
	 * 提供对类的私有函数和本机公共mysqlidb函数的神奇访问
	 *
	 * @param string $method
	 * @param mixed  $arg
	 *
	 * @return mixed
	 */
	public function __call( $method, $arg )
	{
		if( method_exists( $this, $method ) ){
			return call_user_func_array( [$this, $method], $arg );
		}

		call_user_func_array( [$this->db, $method], $arg );
		return $this;
	}

	/**
	 * 捕获对未定义静态方法的调用。
	 *
	 * 透明地创建DbObject类来提供平滑的API，如name::get() name::orderBy()->get()
	 *
	 * @param string $method
	 * @param mixed  $arg
	 *
	 * @return mixed
	 */
	public static function __callStatic( $method, $arg )
	{
		$obj    = new static;
		$result = call_user_func_array( [$obj, $method], $arg );
		if( method_exists( $obj, $method ) ){
			return $result;
		}
		return $obj;
	}

	/**
	 * 将对象数据转换为关联数组
	 *
	 * @return array Converted data
	 */
	public function toArray() : array
	{
		$data = $this->data;
		$this->processAllWith( $data );
		foreach( $data as &$d ){
			if( $d instanceof DbObject ){
				$d = $d->data;
			}
		}
		return $data;
	}

	/**
	 * 将对象数据转换为JSON字符串。
	 *
	 * @return string Converted data
	 */
	public function toJson() : string
	{
		return json_encode( $this->toArray() );
	}

	/**
	 * 将对象数据转换为JSON字符串。
	 *
	 * @return string Converted data
	 */
	public function __toString() : string
	{
		return $this->toJson();
	}

	/**
	 * 如果需要，函数查询有很多关系，还可以转换hasOne对象名
	 *
	 * @param array $data
	 */
	private function processAllWith( array &$data, $shouldReset = true ) : void
	{
		if( count( $this->_with ) == 0 ){
			return;
		}

		foreach( $this->_with as $name => $opts ){
			$relationType = strtolower( $opts[0] );
			$modelName    = $opts[1];
			if( $relationType == 'hasone' ){
				$obj        = new $modelName;
				$table      = $obj->dbTable;
				$primaryKey = $obj->primaryKey;

				if( !isset ( $data[$table] ) ){
					$data[$name] = $this->$name;
					continue;
				}
				if( $data[$table][$primaryKey] === null ){
					$data[$name] = null;
				} else{
					if( $this->returnType == 'Object' ){
						$item             = new $modelName ( $data[$table] );
						$item->returnType = $this->returnType;
						$item->isNew      = false;
						$data[$name]      = $item;
					} else{
						$data[$name] = $data[$table];
					}
				}
				unset ( $data[$table] );
			} else
				$data[$name] = $this->$name;
		}
		if( $shouldReset ){
			$this->_with = [];
		}
	}

	/*
	 * 函数构建对于get/getOne方法有一个连接
	 */
	private function processHasOneWith() : void
	{
		if( count( $this->_with ) == 0 ){
			return;
		}
		foreach( $this->_with as $name => $opts ){
			$relationType = strtolower( $opts[0] );
			$modelName    = $opts[1];
			$key          = null;
			if( isset ( $opts[2] ) ){
				$key = $opts[2];
			}
			if( $relationType == 'hasone' ){
				$this->db->setQueryOption( "MYSQLI_NESTJOIN" );
				$this->join( $modelName, $key );
			}
		}
	}

	/**
	 * @param array $data
	 */
	private function processArrays( array &$data ) : void
	{
		if( isset ( $this->jsonFields ) && is_array( $this->jsonFields ) ){
			foreach( $this->jsonFields as $key ){
				$data[$key] = json_decode( $data[$key] );
			}
		}

		if( isset ( $this->arrayFields ) && is_array( $this->arrayFields ) ){
			foreach( $this->arrayFields as $key ){
				$data[$key] = explode( "|", $data[$key] );
			}
		}
	}

	/**
	 * @param array $data
	 */
	private function validate( $data )
	{
		if( !$this->dbFields ){
			return true;
		}

		foreach( $this->dbFields as $key => $desc ){
			if( in_array( $key, $this->toSkip ) ){
				continue;
			}

			$type     = null;
			$required = false;
			if( isset ( $data[$key] ) ){
				$value = $data[$key];
			} else{
				$value = null;
			}

			if( is_array( $value ) ){
				continue;
			}

			if( isset ( $desc[0] ) ){
				$type = $desc[0];
			}
			if( isset ( $desc[1] ) && ($desc[1] == 'required') ){
				$required = true;
			}

			if( $required && strlen( $value ) == 0 ){
				$this->errors[] = [$this->dbTable.".".$key => "is required"];
				continue;
			}
			if( $value == null ){
				continue;
			}

			switch( $type ){
			case "text":
				$regexp = null;
			break;
			case "int":
				$regexp = "/^[0-9]*$/";
			break;
			case "double":
				$regexp = "/^[0-9\.]*$/";
			break;
			case "bool":
				$regexp = '/^(yes|no|0|1|true|false)$/i';
			break;
			case "datetime":
				$regexp = "/^[0-9a-zA-Z -:]*$/";
			break;
			default:
				$regexp = $type;
			break;
			}
			if( !$regexp ){
				continue;
			}

			if( !preg_match( $regexp, $value ) ){
				$this->errors[] = [$this->dbTable.".".$key => "$type validation failed"];
				continue;
			}
		}
		return !count( $this->errors ) > 0;
	}

	private function prepareData()
	{
		$this->errors = [];
		$sqlData      = [];
		if( count( $this->data ) == 0 ){
			return [];
		}

		if( method_exists( $this, "preLoad" ) ){
			$this->preLoad( $this->data );
		}

		if( !$this->dbFields )
			return $this->data;

		foreach( $this->data as $key => &$value ){
			if( in_array( $key, $this->toSkip ) ){
				continue;
			}

			if( $value instanceof DbObject && $value->isNew == true ){
				$id = $value->save();
				if( $id ){
					$value = $id;
				} else{
					$this->errors = array_merge( $this->errors, $value->errors );
				}
			}

			if( !in_array( $key, array_keys( $this->dbFields ) ) ){
				continue;
			}

			if( !is_array( $value ) ){
				$sqlData[$key] = $value;
				continue;
			}

			if( isset ( $this->jsonFields ) && in_array( $key, $this->jsonFields ) ){
				$sqlData[$key] = json_encode( $value );
			} else if( isset ( $this->arrayFields ) && in_array( $key, $this->arrayFields ) ){
				$sqlData[$key] = implode( "|", $value );
			} else{
				$sqlData[$key] = $value;
			}
		}
		return $sqlData;
	}

	private static function DbObjectAutoload( string $classname )
	{
		$filename = static::$modelPath.$classname.".php";
		if( file_exists( $filename ) ){
			include_once($filename);
		}
	}

	/*
	 * 允许模型从指定的路径自动加载
	 *
	 * Calling autoload() without path will set path to DbObjectPath/models/ directory
	 *
	 * @param string $path
	 */
	public static function autoload( $path = null )
	{
		if( $path ){
			static::$modelPath = $path."/";
		} else{
			static::$modelPath = __DIR__."/models/";
		}
		spl_autoload_register( "DbObject::DbObjectAutoload" );
	}
}