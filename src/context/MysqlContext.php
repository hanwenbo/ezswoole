<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2019-02-13
 * Time: 15:44
 *
 */

namespace ezswoole\context;

use ezswoole\pool\MysqlPool;
use EasySwoole\Component\Pool\PoolManager;
use ezswoole\pool\MysqlObject;

class MysqlContext implements \EasySwoole\Component\Context\ContextItemHandlerInterface{
	const KEY = 'MYSQL';
	function onContextCreate(){
		return PoolManager::getInstance()->getPool( MysqlPool::class )->getObj( \EasySwoole\EasySwoole\Config::getInstance()->getConf( 'MYSQL.POOL_TIME_OUT' ) );
	}
	function onDestroy($context){
		if( $context instanceof MysqlObject ){
			$context->gc();
			PoolManager::getInstance()->getPool( MysqlPool::class )->recycleObj( $context );
		}
	}
}