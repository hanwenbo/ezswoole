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

use EasySwoole\EasySwooleEvent;
use fashop\App;
use EasySwoole\Core\Swoole\ServerManager;
/**
 * 本地化调试输出到文件
 */
class Socket
{
	protected $config
		= [
			'time_format'         => ' c ',
			'file_size'           => 2097152,
			'path'                => LOG_PATH,
			'apart_level'         => [],
			'show_included_files' => false,
			'show_http_message'   => false,
			'send_type'           => ['log', 'error', 'info', 'sql', 'notice', 'alert', 'debug'],
			// 限制允许读取日志的client_id
			'allow_client_ids'    => [],
			// 允许访问的ip
			'allow_client_ips'    => [],
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
		$cli         = IS_CLI ? '_cli' : '';
		$destination = $this->config['path'].date( 'Ym' ).DS.date( 'd' ).$cli.'.log';
		$path        = dirname( $destination );
		!is_dir( $path ) && mkdir( $path, 0755, true );
		$info = [];
		foreach( $log as $type => $content ){
			$info['type']    = $type;
			$info['time']    = date( "Y-m-d H:i:s" );
			$info['content'] = $content;
			$this->write( $info, $destination );
		}
	}

	/**
	 * @param string $message - 发送的消息
	 * @return bool
	 */
	protected function send( $message = '' )
	{
		$server =  ServerManager::getInstance()->getServer();
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
		//检测日志文件大小，超过配置大小则备份日志文件重新生成
		if( is_file( $destination ) && floor( $this->config['file_size'] ) <= filesize( $destination ) ){
			rename( $destination, dirname( $destination ).DS.time().'-'.basename( $destination ) );
			$this->writed[$destination] = false;
		}

		if( App::$debug && !$apart ){
			$this->writed[$destination] = true;
			$message                    = $this->object2array( $message );
			$json_message               = json_encode( $message );
			if( in_array( $message['type'], $this->config['send_type'] ) ){

				$this->send( $json_message );
			}
		}
		return error_log( $json_message."\r\n", 3, $destination );
	}

	/**
	 * Convert encoding.
	 *
	 * @param array  $array
	 * @param string $to_encoding
	 * @param string $from_encoding
	 *
	 * @return array
	 */
	private function encoding( $array, $to_encoding = 'UTF-8', $from_encoding = 'GBK' )
	{
		$encoded = [];
		foreach( $array as $key => $value ){
			if( is_array( $value ) ){
				$encoded[$key] = $this->encoding( $value, $to_encoding, $from_encoding );
			} elseif( is_bool( $value ) ){
				$encoded[$key] = $value;
			} elseif( is_string( $value ) && mb_detect_encoding( $value, 'UTF-8', true ) ){
				$encoded[$key] = $value;
			} else{
				$encoded[$key] = mb_convert_encoding( $value, $to_encoding, $from_encoding );
			}
		}
		return $encoded;
	}

	private function object2array( $object )
	{
		if( is_object( $object ) ){
			$object = (array)$object;
		}
		if( is_array( $object ) ){
			foreach( $object as $key => $value ){
				$object[$key] = $this->object2array( $value );
			}
		}
		return $object;
	}
}
