<?php
/**
 * Socket log
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

use fashop\App;

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
		$destination = $this->config['path'].date( 'Ym' ).DS.date( 'd' ).'.log';
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
	 * @param      $message
	 * @param      $destination
	 * @param bool $apart
	 * @return bool
	 * @author 韩文博
	 */
	protected function write( $message, $destination, $apart = false )
	{
		//检测日志文件大小，超过配置大小则备份日志文件重新生成
		if( is_file( $destination ) && floor( $this->config['file_size'] ) <= filesize( $destination ) ){
			rename( $destination, dirname( $destination ).DS.time().'-'.basename( $destination ) );
			$this->writed[$destination] = false;
		}
		if( App::$debug && !$apart ){
			$this->writed[$destination] = true;
			wsdebug()->send( $message, 'log' );
		}
		return error_log( is_array( $message ) ? json_encode( $message ) : $message."\r\n", 3, $destination );
	}

}
