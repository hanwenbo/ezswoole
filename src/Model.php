<?php

namespace ezswoole;

use ezswoole\dbobject\DbObject;
use ezswoole\pool\MysqlPool;
use ezswoole\pool\MysqlObject;
use EasySwoole\Spl\SplString;

abstract class Model extends DbObject
{
	protected $prefix = 'ez_';
	protected $modelPath = '\\App\\Model';
	protected $fields = [];
	protected $limit;

	/**
	 * @throws \EasySwoole\Component\Pool\Exception\PoolEmpty
	 * @throws \EasySwoole\Component\Pool\Exception\PoolException
	 * @throws \Throwable
	 */
	public function initialize() : void
	{
		$db = MysqlPool::invoke( function( MysqlObject $mysqlObject ){
			return $mysqlObject;
		} );
		$this->setDb( $db );
	}

	public function __construct( $data = null )
	{
		if( empty( $this->dbTable ) ){
			$split         = explode( "\\", get_class( $this ) );
			$end           = end( $split );
			$splString     = new SplString( $end );
			$name          = $splString->snake( '_' )->__toString();
			$this->dbTable = $this->prefix.$name." AS {$name}";
		}
		parent::__construct( $data );
	}

	/**
	 * @param string $objectNames
	 * @param string $joinStr
	 * @param string $joinType
	 * @return Model
	 * @throws \EasySwoole\Mysqli\Exceptions\JoinFail
	 */
	protected function join( $objectNames, string $joinStr, string $joinType = 'LEFT' ) : Model
	{
		if( is_array( $objectNames ) ){
			foreach( $objectNames as $join ){
				$this->getDb()->join( ...$join );
			}
		} else{
			$this->getDb()->join( ...$objectNames );
		}

		return $this;
	}

	protected function find() : array
	{
		$list = parent::get( 1, $this->fields );
		return isset( $list[0] ) ? $list[0] : [];
	}

	protected function field( $field ) : Model
	{
		$this->fields = $field;
		return $this;
	}

	protected function limit( $limit ) : Model
	{
		$this->limit = $limit;
		return $this;
	}

	protected function page( string $page ) : Model
	{
		$split = explode( ",", $page );
		$page  = $split[0] - 1;
		$rows  = $split[1];
		return $this->limit( "{$page},{$rows}" );
	}

	protected function select() : array
	{
		return parent::get( $this->limit, $this->fields );
	}

	protected function where( $whereProps, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND' ) : Model
	{
		if( is_array( $whereProps ) ){
			foreach( $whereProps as $whereProp ){
				$this->getDb()->where( ...$whereProp );
			}
		} else{
			$this->getDb()->where( $whereProps, $whereValue, $operator, $cond );
		}
		return $this;
	}

}