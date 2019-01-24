<?php
go( function(){
	$db     = new Co\MySQL();
	$server = [
		'host'     => 'mysql',
		'user'     => 'root',
		'password' => '123456',
		'database' => 'fashop',
		'port'     => 3306,
	];

	$con = $db->connect( $server );
	echo "数据库链接".($con ? "成功" : '失败')."\n" ;
	go( function(){
		go(function(){
			echo "我是内部实例数据库里的子go\n";
		});
		$db     = new Co\MySQL();
		$server = [
			'host'     => 'mysql',
			'user'     => 'root',
			'password' => '123456',
			'database' => 'fashop',
			'port'     => 3306,
		];
		$db->connect( $server );
		$result = $db->query( 'SELECT * FROM user' );
		echo "内部实例化调用：".count( $result )."\n";
	} );
	go( function() use ( $db ){
		$result = $db->query( 'SELECT * FROM user' );
		echo "从外部use 传来db 调用：".count( $result )."\n";
	} );
	go( function(){
		echo "比数据库查询先执行了"."\n";
	} );
} );
go( function(){
	co::sleep(1);

//	file_get_contents("https://www.fashop.cn");
	echo "我是倒数第二行"."\n";
} );
var_dump( '我是最后一行' );



//执行顺序
go(function() {
	go(function () {
		go(function () {
			co::sleep(1.5);
			echo "co[3] end\n";
		});
		echo "co[2] end\n";
	});
	echo "co[1] end\n";
});
// 导出测试的mysql 到本地的脚本