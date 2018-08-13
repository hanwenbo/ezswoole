<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2018/1/24
 * Time: 下午4:58
 *
 */

namespace fashop;

use EasySwoole\Core\Utility\Curl\Response;
use EasySwoole\Core\Utility\Curl\Request;
use EasySwoole\Core\Utility\Curl\Field;

class Curl
{
	public function __construct()
	{

	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array  $params
	 * @author 韩文博
	 */
	public function request( string $method, string $url, array $params = null ) : Response
	{
		$request = new Request( $url );
		switch( $method ){
		case 'GET' :
			if( $params && isset( $params['query'] ) ){
				foreach( $params['query'] as $key => $value ){
					$request->addGet( new Field( $key, $value ) );
				}
			}
		break;
		case 'POST' :
			if( $params && isset( $params['form_params'] ) ){
				foreach( $params['form_params'] as $key => $value ){
					$request->addPost( new Field( $key, $value ) );
				}
			} elseif( $params && isset( $params['body'] ) ){
				if( !isset( $params['header']['Content-Type'] ) ){
					$params['header']['Content-Type'] = 'application/json; charset=utf-8';
				}
				$request->setUserOpt( [CURLOPT_POSTFIELDS => $params['body']] );
			}
		break;
		default:
			throw new \InvalidArgumentException( "method eroor" );
		break;
		}

		if( isset( $params['header'] ) && !empty( $params['header'] ) && is_array( $params['header'] ) ){
			foreach( $params['header'] as $key => $value ){
				$string   = "{$key}:$value";
				$header[] = $string;
			}
			$request->setUserOpt( [CURLOPT_HTTPHEADER => $header] );
		}

		if( isset( $params['user_opt'] ) ){
			foreach( $params['user_opt'] as $key => $value ){
				$request->setUserOpt( [$key => $value] );
			}
		}
		return $request->exec();
	}

	public function praseHeaderLine( string $header_string )
	{
		$header_rows = explode( PHP_EOL, $header_string );
		foreach( $header_rows as $key => $value ){
			$header_rows[$key] = trim( $header_rows[$key] );
		}
		$i = 0;
		foreach( (array)$header_rows as $hr ){
			$colonpos      = strpos( $hr, ':' );
			$key           = $colonpos !== false ? substr( $hr, 0, $colonpos ) : (int)$i ++;
			$headers[$key] = $colonpos !== false ? trim( substr( $hr, $colonpos + 1 ) ) : $hr;
		}
		$j = 0;
		foreach( (array)$headers as $key => $val ){
			$vals = explode( ';', $val );
			if( count( $vals ) >= 2 ){
				unset( $headers[$key] );
				foreach( $vals as $vk => $vv ){
					$equalpos             = strpos( $vv, '=' );
					$vkey                 = $equalpos !== false ? trim( substr( $vv, 0, $equalpos ) ) : (int)$j ++;
					$headers[$key][$vkey] = $equalpos !== false ? trim( substr( $vv, $equalpos + 1 ) ) : $vv;
				}
			}
		}
		return $headers;
	}
}