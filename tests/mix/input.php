<?php
$data = file_get_contents("php://input",'r');
echo "下面是php://input\n";
var_dump($data);
echo 1;
echo "下面是 POST:\n";
var_dump($_POST);


file_put_contents("php://output","仙士可最帅");