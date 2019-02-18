<?php

namespace ezswoole;

use EasySwoole\Core\Swoole\ServerManager;

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
			$task_list            = $this->config['task_list'];
			$cronLastExecTimeList = [];
			// 定时执行
			ServerManager::getInstance()->getServer()->tick( $this->config['loop_time'] * 1000, function() use ( &$task_list, &$cronLastExecTimeList ){
				// 分别执行
				foreach( $task_list as $name => $option ){
					try{
						// 是到期需执行 当前时间 > 最后一次时间 + 间隔时间
						$last_time     = isset( $cronLastExecTimeList[$name] ) ? $cronLastExecTimeList[$name] : 0;
						$current_time  = time();
						$interval_time = ceil( $option['interval_time'] );

						if( $current_time > ($last_time + $interval_time) ){
							// 设置最后一次执行时间 不可等任务执行完毕后再设置时间，因为会阻塞导致每秒进行一次
							$cronLastExecTimeList[$name] = $current_time;
							// 检测是否可执行
							if( \ezswoole\Cron::checkTask( $name, $option ) === true ){
								\EasySwoole\Core\Swoole\Task\TaskManager::async( function() use ( $name, $option ){
									\ezswoole\Cron::exec( $name, $option );
									return true;
								} );
							} else{
								// 删除该任务
								unset( $task_list[$name] );
							}
						}
					} catch( \Exception $e ){
						\EasySwoole\EasySwoole\Logger::getInstance()->log($e->getTraceAsString(),'error');
					}
				}
			} );
		}
		return true;
	}

	/**
	 * 执行任务
	 */
	static function exec( string $name, array $option )
	{
		list( $class, $function ) = explode( "::", $option['script'] );
		$class::$function();
		return true;
	}

	/**
	 * 检查任务
	 * @param string $name   任务键值
	 * @param string $option 任务配置[ interval_time 扫描间隔 , script 类方法 ]
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
