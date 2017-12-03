<?php
/**
 * josn log
 *
 *
 *
 *
 * @copyright  Copyright (c) 2016-2017 MoJiKeJi Inc. (http://www.fashop.cn)
 * @license    http://www.fashop.cn
 * @link       http://www.fashop.cn
 * @since      File available since Release v1.1
 * @author     hanwenbo <9476400@qq.com>
 */

namespace fashop\log\driver;

use Conf\Config;
use fashop\App;
use Core\Swoole\Server;

/**
 * 本地化调试输出到文件
 */
class Socket
{
	protected $config
		= [
			'time_format' => ' c ',
			'file_size'   => 2097152,
			'path'        => LOG_PATH,
			'apart_level' => [],
		];

	protected $writed = [];

	// 实例化并传入参数
	public function __construct( $config = [] )
	{
		if( is_array( $config ) ){
			$this->config = array_merge( $this->config, $config );
		}
	}

	/**
	 * 日志写入接口
	 * @access public
	 * @param array $log 日志信息
	 * @return bool
	 */
	public function save( array $log = [] )
	{
		$request = request();
		$header  = \Core\Http\Request::getInstance()->getSwooleRequest()->header;
		$get     = $request->get();
		$post    = $request->post();
		if( strlen( json_encode( $post ) ) > 20000 ){
			$post = "长度大于20000太长，有可能是图片或附件或长文本，不记录";
		}
		$ip = $_SERVER['REMOTE_ADDR'];

		$cli         = IS_CLI ? '_cli' : '';
		$destination = $this->config['path'].date( 'Ym' ).DS.date( 'd' ).$cli.'.log';

		$path = dirname( $destination );
		!is_dir( $path ) && mkdir( $path, 0755, true );

		$info           = [];
		$info['header'] = $header;
		if($get){
			$info['get']    = $get;
		}
		if($post){
			$info['post']   = $post;
		}
		$info['ip']     = $ip;
		foreach( $log as $type => $val ){
			if( in_array( $type, $this->config['level'] ) ){
				$info[$type] = $val;
			}
			if( $type == 'trace' || $type == 'debug' ){
				$this->send( json_encode( [
					'time' => date( "Y-m-d H:i:s" ),
					'type' => $type,
					'info' => $val,
				] ) );
			}
		}
		if( $info ){
			return $this->write( $info, $destination );
		}
	}

	/**
	 * @param string $message - 发送的消息
	 * @return bool
	 */
	protected function send( $message = '' )
	{
		$server = Server::getInstance()->getServer();
		if( !empty( $server->connections ) ){
			foreach( $server->connections as $fd ){
				$info = $server->connection_info( $fd );
				if( isset( $info['websocket_status'] ) && $info['websocket_status'] === 3 ){
					$server->push( $fd, $message );
				}
			}
			return true;
		} else{
			return false;
		}
	}

	protected function write( $message, $destination, $apart = false )
	{
		$json_data = [];

		//检测日志文件大小，超过配置大小则备份日志文件重新生成
		if( is_file( $destination ) && floor( $this->config['file_size'] ) <= filesize( $destination ) ){
			rename( $destination, dirname( $destination ).DS.time().'-'.basename( $destination ) );
			$this->writed[$destination] = false;
		}

		//		if( empty( $this->writed[$destination] ) ){
		if( App::$debug && !$apart ){
			$request = request();
			$host    = $request->host();
			// 获取基本信息
			if( isset( $host ) ){
				$current_uri = $request->scheme()."://".$host.$request->url();
			} else{
				$current_uri = "cmd:".implode( ' ', $_SERVER['argv'] );
			}

			$runtime    = round( microtime( true ) - FASHOP_START_TIME, 10 );
			$reqs       = $runtime > 0 ? number_format( 1 / $runtime, 2 ) : '∞';
			$time_str   = ' [运行时间：'.number_format( $runtime, 6 ).'s][吞吐率：'.$reqs.'req/s]';
			$memory_use = number_format( (memory_get_usage() - FASHOP_START_MEM) / 1024, 2 );
			$memory_str = ' [内存消耗：'.$memory_use.'kb]';
			$file_load  = ' [文件加载：'.count( get_included_files() ).']';

			$now    = date( "Y-m-d H:i:s" );
			$server = isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';
			$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
			$method = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
			$uri    = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
			if( Config::get( 'log.show_included_files' ) === true ){
				$json_data['load_files'] = get_included_files();
			}

			$json_data['current_uri'] = $current_uri.$uri;
			$json_data['now']         = $now;
			//			$json_data['time_str']    = $time_str;
			//			$json_data['memory_str']  = $memory_str;
			//			$json_data['file_load']   = $file_load;
			//			$json_data['server'] = $server;
			//			$json_data['remote'] = $remote;
			$json_data['method'] = $method;
			$json_data           = array_merge( $json_data, $message );
			if( !empty( $json_data['api_return'] ) ){
				$json_data['api_return'] = $json_data['api_return'][0];
			}
			$this->writed[$destination] = true;
			//		}
			$message = json_encode( $json_data );
			$this->send( $message );
		}

		return error_log( $message."\r\n", 3, $destination );
	}

}
