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
}