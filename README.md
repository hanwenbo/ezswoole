# ezswoole
目前属于开发阶段，composer 请引入本库的github地址，composer还没有打tag
在EasySwoole的基础上开发，增加了大量接口开发时需要的简便类和方法。

# 安装
```sh
composer require hanwenbo/ezswoole
```

# TODO
- 文档
- 测试脚本完善
- 增加数据库迁移工具 方便数据库的迭代和测试

## 我的本地环境
docker run -it -p 9527:9501 -v /Volumes/dev/www/ezswoole-v3:/var/www/project --privileged=true ezkuangren/swoole4 /bin/bash
