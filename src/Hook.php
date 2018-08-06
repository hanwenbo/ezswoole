<?php

namespace fashop;

use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Utility\Random;


class Hook
{
	protected $hooks = array();
	protected $router_function;
	const HOOK_INIT = 1; //初始化
	const HOOK_ROUTE = 2; //URL路由
	const HOOK_CLEAN = 3; //清理
	const HOOK_BEFORE_ACTION = 4;
	const HOOK_AFTER_ACTION = 5;
}
