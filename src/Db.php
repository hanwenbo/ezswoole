<?php

namespace ezswoole;

use ezswoole\pool\MysqlPool;
use ezswoole\pool\MysqlObject;
use EasySwoole\Mysqli\TpDb;
use EasySwoole\Component\Pool\PoolManager;
use EasySwoole\EasySwoole\Config;

/**
 * Class Model
 * @package ezswoole
 * @method TpDb where($whereProps, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND') : TpDb
 * @method TpDb field($field) : TpDb
 */
class Db extends TpDb
{
	protected $prefix;
	protected $fields = [];
	protected $limit;
	protected $throwable;

	public function setThrowable( $t )
	{
		$this->throwable = $t;
	}

	/**
	 * Model constructor.
	 * @throws \Exception
	 */
	public function __construct()
	{
		$this->prefix = Config::getInstance()->getConf( 'MYSQL.prefix' );
		$db           = PoolManager::getInstance()->getPool( MysqlPool::class )->getObj( Config::getInstance()->getConf( 'MYSQL.POOL_TIME_OUT' ) );
		if( $db instanceof MysqlObject ){
			$this->setDb( $db );
		} else{
			throw new \Exception( 'mysql pool is empty' );
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
			return parent::delete();
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