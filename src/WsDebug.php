<?php
/**
 * wsdebug
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

namespace ezswoole;

use EasySwoole\Core\Swoole\ServerManager;


class WsDebug
{

	/**
	 * @param        $message
	 * @param string $type
	 * @return bool
	 */
	public function send( $message, string $type = 'info' )
	{
		$server = ServerManager::getInstance()->getServer();
		if( !empty( $server->connections ) ){
			foreach( $server->connections as $fd ){
				$info = $server->connection_info( $fd );
				if( isset( $info['websocket_status'] ) && $info['websocket_status'] === 3 ){
					$message      = $this->object2array( $message );
					$json_message = json_encode( [
						'type'    => $type,
						'time'    => date( "Y-m-d H:i:s" ),
						'content' => $message,
					] );
					$server->push( $fd, $json_message );
				}
			}
			return true;
		} else{
			return false;
		}
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

}
