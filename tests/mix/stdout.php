<?php

$stdout = fopen("php://stdout",'w');
fwrite($stdout,"这是输出1\n");
echo "这是输出2\n";
fwrite(STDOUT,"这是输出3\n");