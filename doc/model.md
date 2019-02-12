## FaShop示例

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

## 注意

- 实例化过的方法操作会一直保留，和thinkphp不一样，一定要注意

  - 示例：TP操作如下：

    > 总条数：`$pageModel->where($condition)->count();`
    > 列表：`$pageModel->where($condition)->getPageList();`

  - ezswoole操作如下：

    > 列表：`$pageModel->where($condition)->getPageList();`
    > 总条数：`$pageModel->where($condition)->count();`

  - 不同之处：Tp是每次搜索完之后自动的清理了where、order、field条件，但是ezswoole不会清理，一直保留。由于getPageList里用了where，所以在count的时候不需要再加where条件了

```php
<?php

namespace App\HttpController\Server;

class Page extends Server
{
	/**
	 * 页面列表
	 * @method GET
	 */
	public function list()
	{
        $pageModel = new \App\Model\Page;
        $total     = $pageModel->count();
        $list      = $pageModel->getPageList( [], '*', 'id desc', $this->getPageLimit() );
	}
}
```

### 错误示范

```php
public function info()
{
    $pageModel = new \App\Model\Page;
    $total     = $pageModel->where(['id'=>1])->find();
    $list      = $pageModel->getPageList( [], '*', 'id desc', $this->getPageLimit() );
}
```

- 错误原因：
  - 由于$pageModel是一个对象，Model类具有保留where、field等的功能，这时再去操作getPageList，那么该方法where里已经自带了id = 1。
- 正确的做法应该是实例化两个对象。
- 思想：每次操作都是对一个数据对象进行操作，Model不是一个查询数据的工具类(由于多数用户带有thinkphp的思想，这儿要强调一下)