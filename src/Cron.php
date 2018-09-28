<?php

namespace ezswoole;

use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Utility\Random;

/**
 * 定时任务
 * 注意：不要在进程里重复创建
 * 任务配置列表示例：
 * [
 *    // 处理订单
 *    'deal_order' => [
 *        // 间隔多久触发事件，秒
 *        "interval_time" => 2,
 *        "script"        => "\App\Cron\Order::dealOrder",
 *    ],
 *    // 发送短信
 *    'send_msg'   => [
 *        // 间隔多久触发事件，秒
 *        "interval_time" => 2,
 *        "script"        => "\App\Cron\Message::sendMsg",
 *    ],
 * ]
 */
class Cron
{
	// 任务缓存前缀，防止和其他名字冲突
	private $taskCachePrefix;
	private static $instance;
	private $config;

	/**
	 * 初始化
	 * @access public
	 * @param array $options 参数
	 * @return \ezswoole\Cron
	 */
	public static function getInstance( $options = [] )
	{
		if( is_null( self::$instance ) || !empty( $options ) ){
			self::$instance = new static( $options );
		}
		return self::$instance;
	}

	protected function __construct( $options = [] )
	{
		$this->config = Config::get( 'cron' );
	}

	/**
	 * 运行定时任务
	 * @author 韩文博
	 */
	public function run()
	{
		if( isset( $this->config['task_list'] ) && !empty( $this->config['task_list'] ) ){
			$task_list = $this->config['task_list'];
			// 每次创建都会重新生成，以免和其他缓存命名冲突
			$this->taskCachePrefix = "cron_ezswoole_";
			// 定时执行
			ServerManager::getInstance()->getServer()->tick( $this->config['loop_time'] * 1000, function() use ( &$task_list ){
				$cache = Cache::getInstance();
				// 分别执行
				foreach( $task_list as $name => $option ){
					try{
						// 判断是否存在
						$cache_name = $this->taskCachePrefix.$name;
						// 是到期需执行 当前时间 > 最后一次时间 + 间隔时间
						$last_time     = $cache->has( $cache_name ) ? $cache->get( $cache_name ) : 0;
						$current_time  = time();
						$interval_time = ceil( $option['interval_time'] );

						if( $current_time > ($last_time + $interval_time) ){
							// 检测是否可执行
							if( \ezswoole\Cron::checkTask( $name, $option ) === true ){
								\EasySwoole\Core\Swoole\Task\TaskManager::async( function() use (  $name, $option ){
									\ezswoole\Cron::exec( $name, $option );
									return true;
								});
								// 设置最后一次执行时间
								$cache->set( $cache_name, $current_time, $interval_time );
							} else{
								// 删除该任务
								unset( $task_list[$name] );
							}
						}
					}catch(\Exception $e){
						wsdebug()->send([
							'cron-message'=>$e->getMessage()
						]);
					}
				}
			} );
		}
		return true;
	}

	/**
	 * 执行任务
	 * @datetime 2017-11-02T17:10:25+0800
	 * @author   韩文博
	 */
	static function exec( string $name, array $option )
	{
		list( $class, $function ) = explode( "::", $option['script'] );
		$class::$function();
		//		Logger::getInstance()->console( "定时任务：".$name." 返回".var_export( $result ) );
		return true;
	}

	/**
	 * 检查任务
	 * @datetime 2017-11-02T17:13:11+0800
	 * @param string $name   任务键值
	 * @param string $option 任务配置[ interval_time 扫描间隔 , script 类方法 ]
	 * @author   韩文博
	 */
	static function checkTask( string $name, array $option )
	{
		if( is_callable( explode( "::", $option['script'] ) ) !== true ){
			var_dump( "定时任务：".$name." 不存在的script：".$option['script'] );
			return false;
		} else{
			return true;
		}
	}
}
