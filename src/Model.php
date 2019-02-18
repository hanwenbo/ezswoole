<?php

namespace ezswoole;

use EasySwoole\Mysqli\TpORM;
use EasySwoole\EasySwoole\Config;
//use EasySwoole\Component\Pool\PoolManager;
//use ezswoole\pool\MysqlPool;
use ezswoole\pool\MysqlObject;
use ezswoole\context\MysqlContext;

/**
 * Class Model
 * @package ezswoole
 * @method mixed|static where($whereProps, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND')
 * @method mixed|static group(string $groupByField)
 * @method mixed|static order(string $orderByField, string $orderByDirection = "DESC", $customFieldsOrRegExp = null)
 * @method mixed|static field($field)
 *
 */
class Model extends TpORM
{
	protected $prefix;
	protected $modelPath = '\\App\\Model';
	protected $fields = [];
	protected $limit;
	protected $throwable;
	protected $createTime = false;
	protected $createTimeName = 'create_time';
	protected $softDelete = false;
	protected $softDeleteTimeName = 'delete_time';

	/**
	 * @param null $data
	 */
	public function __construct( $data = null )
	{
		$this->prefix = Config::getInstance()->getConf( 'MYSQL.prefix' );
		$db = \EasySwoole\Component\Context\ContextManager::getInstance()->get(MysqlContext::KEY);
		if( $db instanceof MysqlObject ){
			parent::__construct( $data );
			$this->setDb( $db );
		} else{
			return null;
		}
	}

	//	public function __destruct()
	//	{
	//		$db = $this->getDb();
	//		if( $db instanceof MysqlObject ){
	//			$db->gc();
	//			PoolManager::getInstance()->getPool( MysqlPool::class )->recycleObj( $db );
	//			$this->setDb( null );
	//		}
	//	}

	/**
	 * 批量添加
	 * @param array $datas
	 * @param bool $autoConvertData 自动转换model所需要的类型
	 * @return bool|mixed
	 */
	public function addMulti( array $datas = [], bool $convertData = false)
	{
		try{
			if( !empty( $datas ) ){
				if($convertData === true ){
					foreach($datas as $k => $d){
						$datas[$k] = $this->convertData($d);
					}
				}
				if( !is_array( $datas[0] ) ){
					return false;
				}
				$fields    = array_keys( $datas[0] );
				$db        = $this->getDb();
				$tableName = $this->getDbTable();
				$values    = [];
				foreach( $datas as $data ){
					$value = [];
					foreach( $data as $key => $val ){
						if( is_string( $val ) ){
							$val = '"'.addslashes( $val ).'"';
						} elseif( is_bool( $val ) ){
							$val = $val ? '1' : '0';
						} elseif( is_null( $val ) ){
							$val = 'null';
						}
						if( is_scalar( $val ) ){
							$value[] = $val;
						}
					}
					$values[] = '('.implode( ',', $value ).')';
				}
				$sql = 'INSERT INTO '.$tableName.' ('.implode( ',', $fields ).') VALUES '.implode( ',', $values );
				return $db->rawQuery( $sql );
			} else{
				return false;
			}
		}catch(\Exception $e){
			var_dump($e->getTraceAsString());
			return false;
		}

	}

	/**
	 * 批量修改
	 * @param array $multipleData
	 * @return bool
	 */
	public function editMulti( array $multipleData = [] )
	{
		try{
			if( !empty( $multipleData ) ){
				$db           = $this->getDb();
				$pk           = $this->getPrimaryKey();
				$tableName    = $this->getDbTable();
				$updateColumn = array_keys( $multipleData[0] );
				unset( $updateColumn[0] );
				$sql = "UPDATE ".$tableName." SET ";
				$pks = array_column( $multipleData, $pk );
				// 解决
				foreach( $updateColumn as $uColumn ){
					$sql .= $uColumn." = CASE ";
					foreach( $multipleData as $data ){
						$sql .= "WHEN `".$pk."` = ".$data[$pk]." THEN '".$data[$uColumn]."' ";
					}
					$sql .= "ELSE ".$uColumn." END, ";
				}

				$joinStr = join(",",$pks);
				$inStr = "'".str_replace(",","','",$joinStr)."'";

				$sql = rtrim( $sql, ", " )." WHERE `".$pk."` IN (".$inStr.")";
				return $db->rawQuery( $sql ) ?? false;
			} else{
				return false;
			}
		}catch(\Exception $e){
			var_dump($e->getTraceAsString());
			return false;
		}

	}
	/**
	 * @param null $data
	 * @return bool|int
	 */
	protected function add( $data = null )
	{
		try{
			if( $this->createTime === true ){
				$data[$this->createTimeName] = time();
			}
			return parent::insert( $data );
		} catch( \EasySwoole\Mysqli\Exceptions\ConnectFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \EasySwoole\Mysqli\Exceptions\PrepareQueryFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \Throwable $t ){
			$this->throwable = $t;
			return false;
		}
	}

	/**
	 * @param null $data
	 * @return bool|mixed
	 */
	protected function edit( $data = null )
	{
		try{
			return $this->update( $data );
		} catch( \EasySwoole\Mysqli\Exceptions\ConnectFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \EasySwoole\Mysqli\Exceptions\PrepareQueryFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \Throwable $t ){
			$this->throwable = $t;
			return false;
		}
	}

	/**
	 * @return bool|null
	 */
	protected function del()
	{
		try{
			if( $this->softDelete === true ){
				$data[$this->softDeleteTimeName] = time();
				return $this->update( $data );
			} else{
				return parent::delete();
			}
		} catch( \EasySwoole\Mysqli\Exceptions\ConnectFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \EasySwoole\Mysqli\Exceptions\PrepareQueryFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \Throwable $t ){
			$this->throwable = $t;
			return false;
		}
	}

	/**
	 * @return array|bool|false|null
	 */
	protected function select()
	{
		try{
			return parent::select();
		} catch( \EasySwoole\Mysqli\Exceptions\ConnectFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \EasySwoole\Mysqli\Exceptions\PrepareQueryFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \Throwable $t ){
			$this->throwable = $t;
			return false;
		}
	}

	/**
	 * @param string $name
	 * @return array|bool
	 */
	protected function column( string $name )
	{
		try{
			return parent::column( $name );
		} catch( \EasySwoole\Mysqli\Exceptions\ConnectFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \EasySwoole\Mysqli\Exceptions\PrepareQueryFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \Throwable $t ){
			$this->throwable = $t;
			return false;
		}
	}

	/**
	 * @param string $name
	 * @return array|bool|null
	 */
	protected function value( string $name )
	{
		try{
			return parent::value( $name );
		} catch( \EasySwoole\Mysqli\Exceptions\ConnectFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \EasySwoole\Mysqli\Exceptions\PrepareQueryFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \Throwable $t ){
			$this->throwable = $t;
			return false;
		}
	}

	/**
	 * @param string $column
	 * @return array|bool|int|null
	 */
	protected function count( string $column = '*')
	{
		try{
			return parent::count($column);
		} catch( \EasySwoole\Mysqli\Exceptions\ConnectFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \EasySwoole\Mysqli\Exceptions\PrepareQueryFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \Throwable $t ){
			$this->throwable = $t;
			return false;
		}
	}

	/**
	 * @return array|bool
	 */
	protected function find( $id = null )
	{
		try{
			if( $id ){
				return $this->byId( $id );
			} else{
				return parent::find();
			}
		} catch( \EasySwoole\Mysqli\Exceptions\ConnectFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \EasySwoole\Mysqli\Exceptions\PrepareQueryFail $e ){
			$this->throwable = $e;
			return false;
		} catch( \Throwable $t ){
			$this->throwable = $t;
			return false;
		}
	}

	/**
	 * @return Model
	 */
	static function init()
	{
		return new static();
	}
}