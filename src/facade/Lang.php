<?php
/**
 *
 * Copyright  FaShop
 * License    http://www.fashop.cn
 * link       http://www.fashop.cn
 * Created by FaShop.
 * User: hanwenbo
 * Date: 2018/2/27
 * Time: 下午3:08
 *
 */

namespace fashop\facade;
use fashop\Facade;
/**
 * @see \fashop\Lang
 * @mixin \fashop\Lang
 * @method mixed range($range = '') static 设定当前的语言
 * @method mixed set(mixed $name, string $value = null, string $range = '') static 设置语言定义
 * @method array load(mixed $file, string $range = '') static 加载语言定义
 * @method mixed get(string $name = null, array $vars = [], string $range = '') static 获取语言定义
 * @method mixed has(string $name, string $range = '') static 获取语言定义
 * @method string detect() static 自动侦测设置获取语言选择
 * @method void saveToCookie(string $lang = null) static 设置当前语言到Cookie
 * @method void setLangDetectVar(string $var) static 设置语言自动侦测的变量
 * @method void setLangCookieVar(string $var) static 设置语言的cookie保存变量
 * @method void setAllowLangList(array $list) static 设置允许的语言列表
 */
class Lang extends Facade
{
}