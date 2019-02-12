<?php

namespace ezswoole;

use ezswoole\pool\MysqlPool;
use ezswoole\pool\MysqlObject;
use EasySwoole\Mysqli\TpORM;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Config;

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
		$db           = PoolManager::getInstance()->getPool( MysqlPool::class )->getObj( Config::getInstance()->getConf( 'MYSQL.POOL_TIME_OUT' ) );
		if( $db instanceof MysqlObject ){
			parent::__construct( $data );
			$this->setDb( $db );
		} else{
			// todo log
			return null;
			//			throw new \Exception( 'mysql pool is empty' );
		}
	}

	public function __destruct()
	{
		$db = $this->getDb();
		if( $db instanceof MysqlObject ){
			$db->gc();
			PoolManager::getInstance()->getPool( MysqlPool::class )->recycleObj( $db );
			$this->setDb( null );
		}
	}

	/**
	 * 批量添加
	 * @param array $datas
	 * @return bool|mixed
	 */
	public function addMulti( array $datas = [] )
	{
		try{
			if( !empty( $datas ) ){
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
				foreach( $updateColumn as $uColumn ){
					$sql .= $uColumn." = CASE ";
					foreach( $multipleData as $data ){
						$sql .= "WHEN ".$pk." = ".$data[$pk]." THEN '".$data[$uColumn]."' ";
					}
					$sql .= "ELSE ".$uColumn." END, ";
				}
				$sql = rtrim( $sql, ", " )." WHERE ".$pk." IN (".implode( ",", $pks ).")";
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
	public function del()
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
	public function select()
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
	public function column( string $name )
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
	public function value( string $name )
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
	 * @return array|bool|int|null
	 */
	public function count()
	{
		try{
			return parent::count();
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
	protected function model()
	{
		return new static();
	}
}