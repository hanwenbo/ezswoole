<?php

namespace App\Cron;

class Test
{
	/**
	 * 消息推送
	 * @author 韩文博
	 */
	static function index()
	{
		wsdebug()->send(['date'=>date('Y-m-d H:i:s')],'debug');
	}
}
