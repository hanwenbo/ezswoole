<?php
$stdin = fopen("php://fd/0",'r');
$data = fgets($stdin);
echo "这是STDIN输入的:{$data}\n";
file_put_contents("php://fd/2","这是STDERR\n");
file_put_contents("php://fd/1","这是STDOUT\n");