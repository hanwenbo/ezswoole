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
use EasySwoole\Http\Request as EasySwooleRequest;
use EasySwoole\Http\Response as EasySwooleResponse;
use ezswoole\exception\ValidateException;
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
	protected $batchValidate = false;
	private $validate;

	protected $view;

	public function __construct( string $actionName, EasySwooleRequest $request, EasySwooleResponse $response )
	{
		parent::__construct( $actionName, $request, $response );
	}


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

	protected function getPageLimit() : string
	{
		$param = Request::getInstance()->param();
		$page  = isset( $param['page'] ) ? $param['page'] : 1;
		$rows  = isset( $param['rows'] ) ? $param['rows'] : 10;
		return $page.','.$rows;
	}

	/**
	 * 设置验证失败后是否抛出异常
	 * @param bool $fail
	 * @return Controller
	 */
	protected function validateFailException( $fail = true ) : Controller
	{
		$this->failException = $fail;
		return $this;
	}

	/**
	 * 验证数据

	 * @param mixed $data     数据
	 * @param mixed $validate 验证器名或者验证规则数组
	 * @param array $message  提示信息
	 * @param bool  $batch    是否批量验证
	 * @param mixed $callback 回调方法（闭包）
	 * @return array|string|true
	 * @throws ValidateException
	 */
	protected function validate( $data, $validate, array $message = [], bool $batch = false, $callback = null )
	{
		if( is_array( $validate ) ){
			$v = new Validate();
			$v->rule( $validate );
		} else{
			if( strpos( $validate, '.' ) ){
				list( $validate, $scene ) = explode( '.', $validate );
			}
			$v = new Validate( $validate );
			if( !empty( $scene ) ){
				$v->scene( $scene );
			}
		}

		// 是否批量验证
		if( $batch || $this->batchValidate ){
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
				throw new ValidateException( $v->getError() );
			} else{
				$this->setValidate( $v );
				return $v->getError();
			}
		} else{
			return true;
		}
	}


	protected function getValidate() : Validate
	{
		return $this->validate;
	}

	protected function setValidate( Validate $instance ) : void
	{
		$this->validate = $instance;
	}

}