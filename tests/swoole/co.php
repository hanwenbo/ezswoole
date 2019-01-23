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
	var_dump( $con );
	$result = $db->query( 'SELECT * FROM user' );
	var_dump( $result );
} );
// 导出测试的mysql 到本地的脚本