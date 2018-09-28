<?php
return [
	'open'=>true,
	'loop_time'=>1,// 秒
	'task_list' =>[
		// 待付款订单自动关闭
		'Test'   => [
			"interval_time" => 5,
			"script"        => "\App\Cron\Test::index",
		],

	]

];