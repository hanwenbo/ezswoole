## FaShop示例

## 定义一个总模型

该模型不是必须，只是为了定义辅助注释和以便日后继承父类方法进行处理

文件目录：App\Model\Model.php

```php
<?php

namespace App\Model;

/**
 * Class Model
 * @package App\Model
 * @method mixed|static init() 实例化
 */
class Model extends \ezswoole\Model
{
    // 默认返回的数据为array，而不是Object
	protected $returnType = 'Array';
}
```

## 模型定义
定义一个Page模型类：
文件目录：App\Model\Page.php

继承了Model.php是为了后期方便批量修改操作

```php
<?php

namespace App\Model;
class Page extends Model
{
    // 是否采用软删除 默认字段为delete_time
	protected $softDelete = true; 
    // 是否采用自动创建create_time 默认字段为create_time
	protected $createTime = true;
    // 自动出入转化json的字段
	protected $jsonFields = ['body'];
	//
	public function addPage( array $data )
	{
		return $this->add( $data );
	}

	public function editPage( $condition = [], $data = [] )
	{
		$data['update_time'] = time();
		return $this->where( $condition )->edit( $data );
	}

	public function getPageInfo( $condition = [], $field = '*' )
	{
		$info = $this->where( $condition )->field( $field )->find();
		return $info;
	}

	public function getPageList( $condition = [], $field = '*', $order = 'id desc', $page = [1, 10] )
	{
		$list = $this->where( $condition )->order( $order )->field( $field )->page( $page )->select();
		return $list;
	}
}
```
可以设置属性：
```php
namespace App\Model;
class Page extends Model
{
    // 是否采用软删除 默认字段为delete_time
	protected $softDelete = true; 
    // 是否采用自动创建create_time 默认字段为create_time
	protected $createTime = true;
    // 自动出入转化json的字段
	protected $jsonFields = ['body'];
	// 查询要隐藏的字段
	protected $hiddenFields = ['password','pay_password','salt'];
	// 默认自增主键，默认是id，如果是id 可以不设置
	protected $primaryKey = 'id';
	
```
模型会自动对应数据表，模型类的命名规则是除去表前缀的数据表名称，采用驼峰法命名，并且首字母大写，例如：

| 模型名        | 约定对应数据表（假设数据库的前缀定义是 fashop_） |
| ------------- | ------------------------------------------------ |
| User          | fashop_user                                      |
| GoodsCategory | fashop_goods_category                            |

如果你的规则和上面的系统约定不符合，那么需要设置Model类的数据表名称属性，以确保能够找到对应的数据表。


## 查询
```php
// 实例化GoodsModel
// 等于 $goodsModel = \App\Model\Goods::init();
$goodsModel = new \App\Model\Goods;

// 查询id大于1的列表
$list = $goodsModel->where(['id'=>['>',1]])->field('title,id')->withTotalCount()->select();

// 获得总条数 ，利用的是 mysql的 SQL_CALC_FOUND_ROWS
$total_number = $goodsModel->getTotalCount()
```
- where等链式查询条件，在`$goodsModel`实例化后会一直存在，除非是新实例化一个对象，所以在重复利用where的时候要注意，和thinkphp不一样



## JOIN

- 不在需要`alias`或者`__GOODS__`这样的操作，默认就给了别名，格式为goods_sku

```php
public function getGoodsImageMoreList( $condition = [], $field = '*', $order = 'id desc', $page = [1, 20])
{
    $data = $this->join( 'goods', 'goods_image.goods_id = goods.id', 'LEFT' )->where( $condition )->order( $order )->field( $field )->page( $page )->select();
    return $data;
}
```
## 删除

删除模型数据，可以在实例化后调用`del`方法。

```php
User::init()->where(['id'=>1])->del();
```

## 软删除

```php
// 是否采用软删除 默认字段为delete_time
protected $softDelete = true; 
// 默认为delete_time 可以不定义
protected $softDeleteName = 'delete_time';
// 会把delete_time值改为当前操作的时间戳
User::init()->where(['id'=>1])->del();
```



## 获取单个数据

```php
User::init()->where(['id'=>1])->find();
```

## 添加

```php
User::init()->add(['nickname'=>'韩文博','sex'=>1]);
```

## 修改

```php
User::init()->where(['id'=>1])->edit(['nickname'=>'韩文博你好','sex'=>1]);
// 或 id 必须为自增主键
User::init()->edit(['id'=>1,'nickname'=>'韩文博你好','sex'=>1]);
```

## 聚合

在模型中也可以调用数据库的聚合方法进行查询，例如：

| 方法  | 说明                                     |
| ----- | ---------------------------------------- |
| count | 统计数量，参数是要统计的字段名（可选）   |
| max   | 获取最大值，参数是要统计的字段名（必须） |
| min   | 获取最小值，参数是要统计的字段名（必须） |
| avg   | 获取平均值，参数是要统计的字段名（必须） |
| sum   | 获取总分，参数是要统计的字段名（必须）   |

虽然支持静态调用类似`::where`、`::max`，但不建议使用，为了规范，我们统一走实例化；

- 方便调用，我们提供了一个实例化的静态方法 `init`

```php
User::init()->count();
User::init()->where('status','>',0)->count();
User::init()->where('status',1)->avg('score');
User::init()->max('score');
```



动态调用：

```php
$user = new User;
$user->count();
$user->where('status','>',0)->count();
$user->where('status',1)->avg('score');
$user->max('score');
```

## 查询语句获取

```php
$user = new User;
$user->fetchSql()->where(['id'=>1])->field(['name','id'])->select();
```

## 原生查询

```php
$user = new User;
$user->rawQuery("select distinct name from students");
```

## 统计

```php
$user = new User;
// 所有
$result = $user->where(['id'=>['>',1]])->count();
// 去重复
$result = $user->where(['id'=>['>',1]])->count('DISTINCT user_id');
```

## 关联

底层支持，但是为测试，不建议使用，因为实际项目中用不到

## 聚合模型

底层支持，但是为测试，不建议使用，因为实际项目中用不到