<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2018/1/3
 * Time: 下午11:01
 *
 */

namespace ezswoole;

use EasySwoole\Http\Response as EasySwooleResponse;
use EasySwoole\Component\Singleton;

class Response
{
	use Singleton;

	private $response;

	final public function __construct( EasySwooleResponse $response )
	{
		$this->response = $response;
	}

	final public function getResponse()
	{
		return $this->response;
	}

	public static function clearInstance() : void
	{
		self::$instance = null;
	}

}