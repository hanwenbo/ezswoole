<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2017/11/21
 * Time: 上午11:03
 *
 */

namespace ezswoole;

use EasySwoole\EasySwoole\Config as AppConfig;
use EasySwoole\Http\AbstractInterface\Controller as AbstractController;
use EasySwoole\Spl\SplArray;

abstract class Controller extends AbstractController
{
	protected $app;
	protected $post;
	protected $get;
	/**
	 * @var Request
	 */
	protected $request;
	// 验证失败是否抛出异常
	protected $failException = false;
	// 是否批量验证
	protected $batchValidator = false;
	private $validator;

	protected $view;


	public function index()
	{
		return $this->send( - 1, [], "NOT FOUND" );
	}

	protected function actionNotFound( $action = null ) : void
	{
		$this->send( - 1, [], "actionNotFound" );
	}

	protected function afterAction( $actionName ) : void
	{
		// 初始化，目的清理上一次请求的static记录
		Request::getInstance()->clearInstance();
		Response::getInstance()->clearInstance();
		Log::clear();
	}

	protected function onRequest( $actionName ) : ?bool
	{
		$this->request = Request::getInstance();
		$this->get     = $this->request->get() ? new SplArray( $this->request->get() ) : null;
		$this->post    = $this->request->post() ? new SplArray( $this->request->post() ) : null;
		return null;
	}


	protected function router()
	{
		return $this->send( - 1, [], "your router not end" );
	}

	protected function send( $code = 0, $data = [], $message = null )
	{
		// todo 废除
		$this->response()->withAddedHeader( 'Access-Control-Allow-Origin', AppConfig::getInstance()->getConf( 'response.access_control_allow_origin' ) );
		$this->response()->withAddedHeader( 'Content-Type', 'application/json; charset=utf-8' );
		$this->response()->withAddedHeader( 'Access-Control-Allow-Headers', AppConfig::getInstance()->getConf( 'response.access_control_allow_headers' ) );
		$this->response()->withAddedHeader( 'Access-Control-Allow-Methods', AppConfig::getInstance()->getConf( 'response.access_control_allow_methods' ) );
		$this->response()->withStatus( 200 );
		$content = [
			"code"   => $code,
			"result" => $data,
			"msg"    => $message,
		];
		$this->response()->write( json_encode( $content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	}

	protected function getPageLimit() : array
	{
		$param = Request::getInstance()->param();
		$page  = isset( $param['page'] ) ? (int)$param['page'] : 1;
		$rows  = isset( $param['rows'] ) ? (int)$param['rows'] : 10;
		return [$page, $rows];

	}

	/**
	 * 设置验证失败后是否抛出异常
	 * @param bool $fail
	 * @return Controller
	 */
	protected function validatorFailException( $fail = true ) : Controller
	{
		$this->failException = $fail;
		return $this;
	}

	/**
	 * 验证数据
	 * @param       $data
	 * @param       $validator
	 * @param array $message
	 * @param bool  $batch
	 * @param null  $callback
	 * @return array|bool
	 */
	protected function validator( $data, $validator, array $message = [], bool $batch = false, $callback = null )
	{
		if( is_array( $validator ) ){
			$v = new Validator();
			$v->rule( $validator );
		} else{
			if( strpos( $validator, '.' ) ){
				list( $validator, $scene ) = explode( '.', $validator );
			}
			$v = new Validator( $validator );
			if( !empty( $scene ) ){
				$v->scene( $scene );
			}
		}

		// 是否批量验证
		if( $batch || $this->batchValidator ){
			$v->batch( true );
		}

		if( is_array( $message ) ){
			$v->message( $message );
		}

		if( $callback && is_callable( $callback ) ){
			call_user_func_array( $callback, [$v, &$data] );
		}
		if( !$v->check( $data ) ){
			if( $this->failException ){
				return $v->getError();
			} else{
				$this->setValidator( $v );
				return $v->getError();
			}
		} else{
			return true;
		}
	}


	protected function getValidator() : Validator
	{
		return $this->validator;
	}

	protected function setValidator( Validator $instance ) : void
	{
		$this->validator = $instance;
	}

}