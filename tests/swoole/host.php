<?php
use Swoole\Coroutine as co;
go(function(){
	$array = co::getaddrinfo("localhost");
	var_dump($array);
	var_dump(AF_INET);
	$ip = co::gethostbyname("www.baidu.com");
	var_dump($ip);
});


