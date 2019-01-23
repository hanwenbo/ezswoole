<?php

$stderr = fopen("php://stderr",'w');
fwrite($stderr,"这是输出1\n");
echo "这是输出2\n";
fwrite(STDERR,"这是输出3\n");
$a=$b;