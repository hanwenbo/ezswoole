<?php
if ($stream = fopen('file', 'r')) {
	// print all the page starting at the offset 10
	echo stream_get_contents($stream, 1000, 10);

	fclose($stream);
}



$handle = fopen('file', 'w+'); // truncate + attempt to create
rewind($handle); // position = 0
$content = stream_get_contents($handle); // file position = 0 in PHP 5.1.6, file position > 0 in PHP 5.2.17!
var_dump('为什么是空呢',$content);// 为什么返回空呢
fwrite($handle, '12345'); // file position > 0
fwrite($handle, '6789');
rewind($handle); // position = 0
fwrite($handle, 'a'); // file position > 0
fwrite($handle, 'b');
// ab3456789

fclose($handle);

echo "初始: ".memory_get_usage()."B\n";
$str = str_repeat('hello111', 1000);


echo "请输入你的名字:\n";
$stdin = fopen("php://stdin",'r');

$data = fgets($stdin);
echo "{$data}大哥,你好啊!";
echo "使用fopen时是: ".memory_get_usage()."B\n";
fclose($stdin);
echo "关闭fopen时是: ".memory_get_usage()."B\n";

echo "使用: ".memory_get_usage()."B\n";
unset($str);
echo "释放: ".memory_get_usage()."B\n";
echo "峰值: ".memory_get_peak_usage()."B\n";
