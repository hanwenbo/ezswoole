<?php

namespace ezswoole;

use EasySwoole\Mysqli\TpORM;
use EasySwoole\EasySwoole\Config;
use ezswoole\pool\MysqlObject;
use ezswoole\context\MysqlContext;

/**
 * Class Model
 * @package ezswoole
 * @method mixed|static where($whereProps, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND')
 * @method mixed|static group(string $groupByField)
 * @method mixed|static order(string $orderByField, string $orderByDirection = "DESC", $customFieldsOrRegExp = null)
 * @method mixed|static field($field)
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
	protected $updateTime = false;
	protected $updateTimeName = 'update_time';
	protected $softDelete = false;
	protected $softDeleteTimeName = 'delete_time';
	// 同时也查询出来删除过的
	protected $_withDelete = false;
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
			$this->throwable = $e;
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
					$sql .= "`{$uColumn}` = CASE ";
					foreach( $multipleData as $data ){
						$val = $data[$pk];
						// 判断是不是字符串
						if( is_string( $val ) ){
							$val = '"'.addslashes( $val ).'"';
						}  elseif( is_null( $val ) ){
							$val = 'NULL';
						}

						$_val = $data[$uColumn];
						if( is_string( $val ) ){
							$_val = '"'.addslashes( $_val ).'"';
						}  elseif( is_null( $_val ) ){
							$_val = 'NULL';
						}

						$sql .= "WHEN `".$pk."` = {$val} THEN {$_val} ";
					}
					$sql .= "ELSE `".$uColumn."` END, ";
				}

				$joinStr = join(",",$pks);
				$inStr = "'".str_replace(",","','",$joinStr)."'";

				$sql = rtrim( $sql, ", " )." WHERE `".$pk."` IN (".$inStr.")";
				return $db->rawQuery( $sql ) ?? false;
			} else{
				return false;
			}
		}catch(\Exception $e){
			$this->throwable = $e;
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
			if( $this->updateTime === true ){
				$data[$this->updateTimeName] = time();
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
		$this->selectPreHandle();
		try{
			if( $this->updateTime === true ){
				$data[$this->updateTime] = time();
			}
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
		$this->selectPreHandle();
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
		$this->selectPreHandle();
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
		$this->selectPreHandle();
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
		$this->selectPreHandle();
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
		$this->selectPreHandle();
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
		$this->selectPreHandle();
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
	private function selectPreHandle(){
		$this->softDeleteSelectPreHandle();
	}
	/**
	 * 软删除查询的提前处理
	 */
	private function softDeleteSelectPreHandle(){
		// 当查询的时候  过滤掉 delete_time !==0
		if($this->softDelete === true && $this->_withDelete === false){
			// TODO 优化获得别名name 而不是getTableName
			if(strstr($this->getDbTable(),' AS ')){
				$fieldName = "{$this->getTableName()}.{$this->softDeleteTimeName}";
			}else{
				$fieldName = $this->softDeleteTimeName;
			}
			parent::where($fieldName,0);
		}
	}
	protected function withDelete(){
		$this->_withDelete = true;
		return $this;
	}
	/**
	 * @return Model
	 */
	static function init()
	{
		return new static();
	}
}