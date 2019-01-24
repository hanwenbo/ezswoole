<?php
go(function(){
	$ret = co::exec('php -v');
	var_dump($ret);
});