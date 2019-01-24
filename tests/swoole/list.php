<?php
go(function(){
	go(function(){

	});
	go(function(){

	});
	$coros = co::listCoroutines();
	var_dump(count($coros));

});
go(function(){
	echo 1;
});
