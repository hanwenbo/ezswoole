<?php
ob_start();//开启buffer缓冲区  php-cli下默认关闭buffer,由于web访问测试较麻烦,该段代码只为了查看以及测试缓冲区的作用,在web模式下,默认开启,无需手动开启,可自行配置
for($i=0;$i<1000;$i++){
	echo $i;
	sleep(1);
	if($i%10==0){
		//当i为10的倍数时,将直接结束并输出缓冲区的数据,然后再次开启缓冲区
		ob_end_flush();
		ob_start();
	}
}