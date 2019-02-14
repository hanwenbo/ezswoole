<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2018/2/3
 * Time: 下午9:55
 *
 */

namespace ezswoole\validator;
/**
 * Class ValidatorRule
 * @package ezswoole\validator
 * @method ValidatorRule confirm(mixed $rule, string $msg = '') static 验证是否和某个字段的值一致
 * @method ValidatorRule different(mixed $rule, string $msg = '') static 验证是否和某个字段的值是否不同
 * @method ValidatorRule egt(mixed $rule, string $msg = '') static 验证是否大于等于某个值
 * @method ValidatorRule gt(mixed $rule, string $msg = '') static 验证是否大于某个值
 * @method ValidatorRule elt(mixed $rule, string $msg = '') static 验证是否小于等于某个值
 * @method ValidatorRule lt(mixed $rule, string $msg = '') static 验证是否小于某个值
 * @method ValidatorRule eg(mixed $rule, string $msg = '') static 验证是否等于某个值
 * @method ValidatorRule in(mixed $rule, string $msg = '') static 验证是否在范围内
 * @method ValidatorRule notIn(mixed $rule, string $msg = '') static 验证是否不在某个范围
 * @method ValidatorRule between(mixed $rule, string $msg = '') static 验证是否在某个区间
 * @method ValidatorRule notBetween(mixed $rule, string $msg = '') static 验证是否不在某个区间
 * @method ValidatorRule length(mixed $rule, string $msg = '') static 验证数据长度
 * @method ValidatorRule max(mixed $rule, string $msg = '') static 验证数据最大长度
 * @method ValidatorRule min(mixed $rule, string $msg = '') static 验证数据最小长度
 * @method ValidatorRule after(mixed $rule, string $msg = '') static 验证日期
 * @method ValidatorRule before(mixed $rule, string $msg = '') static 验证日期
 * @method ValidatorRule expire(mixed $rule, string $msg = '') static 验证有效期
 * @method ValidatorRule allowIp(mixed $rule, string $msg = '') static 验证IP许可
 * @method ValidatorRule denyIp(mixed $rule, string $msg = '') static 验证IP禁用
 * @method ValidatorRule regex(mixed $rule, string $msg = '') static 使用正则验证数据
 * @method ValidatorRule token($rule = '__token__', string $msg = '') static 验证表单令牌
 * @method ValidatorRule is(mixed $rule, string $msg = '') static 验证字段值是否为有效格式
 * @method ValidatorRule isRequire(mixed $rule, string $msg = '') static 验证字段必须
 * @method ValidatorRule isNumber(mixed $rule, string $msg = '') static 验证字段值是否为数字
 * @method ValidatorRule isArray(mixed $rule, string $msg = '') static 验证字段值是否为数组
 * @method ValidatorRule isInteger(mixed $rule, string $msg = '') static 验证字段值是否为整形
 * @method ValidatorRule isFloat(mixed $rule, string $msg = '') static 验证字段值是否为浮点数
 * @method ValidatorRule isMobile(mixed $rule, string $msg = '') static 验证字段值是否为手机
 * @method ValidatorRule isIdCard(mixed $rule, string $msg = '') static 验证字段值是否为身份证号码
 * @method ValidatorRule isChs(mixed $rule, string $msg = '') static 验证字段值是否为中文
 * @method ValidatorRule isChsDash(mixed $rule, string $msg = '') static 验证字段值是否为中文字母及下划线
 * @method ValidatorRule isChsAlpha(mixed $rule, string $msg = '') static 验证字段值是否为中文和字母
 * @method ValidatorRule isChsAlphaNum(mixed $rule, string $msg = '') static 验证字段值是否为中文字母和数字
 * @method ValidatorRule isDate(mixed $rule, string $msg = '') static 验证字段值是否为有效格式
 * @method ValidatorRule isBool(mixed $rule, string $msg = '') static 验证字段值是否为布尔值
 * @method ValidatorRule isAlpha(mixed $rule, string $msg = '') static 验证字段值是否为字母
 * @method ValidatorRule isAlphaDash(mixed $rule, string $msg = '') static 验证字段值是否为字母和下划线
 * @method ValidatorRule isAlphaNum(mixed $rule, string $msg = '') static 验证字段值是否为字母和数字
 * @method ValidatorRule isAccepted(mixed $rule, string $msg = '') static 验证字段值是否为yes, on, 或是 1
 * @method ValidatorRule isEmail(mixed $rule, string $msg = '') static 验证字段值是否为有效邮箱格式
 * @method ValidatorRule isUrl(mixed $rule, string $msg = '') static 验证字段值是否为有效URL地址
 * @method ValidatorRule activeUrl(mixed $rule, string $msg = '') static 验证是否为合格的域名或者IP
 * @method ValidatorRule ip(mixed $rule, string $msg = '') static 验证是否有效IP
 * @method ValidatorRule fileExt(mixed $rule, string $msg = '') static 验证文件后缀
 * @method ValidatorRule fileMime(mixed $rule, string $msg = '') static 验证文件类型
 * @method ValidatorRule fileSize(mixed $rule, string $msg = '') static 验证文件大小
 * @method ValidatorRule image(mixed $rule, string $msg = '') static 验证图像文件
 * @method ValidatorRule method(mixed $rule, string $msg = '') static 验证请求类型
 * @method ValidatorRule dateFormat(mixed $rule, string $msg = '') static 验证时间和日期是否符合指定格式
 * @method ValidatorRule unique(mixed $rule, string $msg = '') static 验证是否唯一
 * @method ValidatorRule behavior(mixed $rule, string $msg = '') static 使用行为类验证
 * @method ValidatorRule filter(mixed $rule, string $msg = '') static 使用filter_var方式验证
 * @method ValidatorRule requireIf(mixed $rule, string $msg = '') static 验证某个字段等于某个值的时候必须
 * @method ValidatorRule requireCallback(mixed $rule, string $msg = '') static 通过回调方法验证某个字段是否必须
 * @method ValidatorRule requireWith(mixed $rule, string $msg = '') static 验证某个字段有值的情况下必须
 * @method ValidatorRule must(mixed $rule = null, string $msg = '') static 必须验证
 */
class ValidatorRule
{
	// 验证字段的名称
	protected $title;
	// 当前验证规则
	protected $rule = [];
	// 验证提示信息
	protected $message = [];

	/**
	 * 添加验证因子

	 * @param  string $name 验证名称
	 * @param  mixed  $rule 验证规则
	 * @param  string $msg  提示信息
	 * @return $this
	 */
	protected function addItem( $name, $rule = null, $msg = '' )
	{
		if( $rule || 0 === $rule ){
			$this->rule[$name] = $rule;
		} else{
			$this->rule[] = $name;
		}
		$this->message[] = $msg;
		return $this;
	}

	/**
	 * 获取验证规则
	 * @access public
	 * @return array
	 */
	public function getRule()
	{
		return $this->rule;
	}

	/**
	 * 获取验证字段名称
	 * @access public
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * 获取验证提示
	 * @access public
	 * @return array
	 */
	public function getMsg()
	{
		return $this->message;
	}

	/**
	 * 设置验证字段名称
	 * @access public
	 * @return $this
	 */
	public function title( $title )
	{
		$this->title = $title;
		return $this;
	}

	public function __call( $method, $args )
	{
		if( 'is' == strtolower( substr( $method, 0, 2 ) ) ){
			$method = substr( $method, 2 );
		}
		array_unshift( $args, lcfirst( $method ) );
		return call_user_func_array( [$this, 'addItem'], $args );
	}

	public static function __callStatic( $method, $args )
	{
		$rule = new static();
		if( 'is' == strtolower( substr( $method, 0, 2 ) ) ){
			$method = substr( $method, 2 );
		}
		array_unshift( $args, lcfirst( $method ) );
		return call_user_func_array( [$rule, 'addItem'], $args );
	}
}