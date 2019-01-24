<?php

return [
	// +----------------------------------------------------------------------
	// | 应用设置
	// +----------------------------------------------------------------------
	// 默认Host地址
	'app_host'             => '',
	// 应用调试模式
	'app_debug'            => true,
	// 应用Trace
	'app_trace'            => false,
	// 默认时区
	'default_timezone'     => 'PRC',
	// 是否开启多语言
	'lang_switch_on'       => false,
	// 默认全局过滤方法 用逗号分隔多个
	'default_filter'       => '',
	// 默认语言
	'default_lang'         => 'zh-cn',


	// +----------------------------------------------------------------------
	// | URL设置
	// +----------------------------------------------------------------------

	// PATHINFO变量名 用于兼容模式
	'var_pathinfo'         => 's',
	// 兼容PATH_INFO获取
	'pathinfo_fetch'       => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
	// pathinfo分隔符
	'pathinfo_depr'        => '/',
	// HTTPS代理标识
	'https_agent_name'     => '',
	// URL伪静态后缀
	'url_html_suffix'      => 'html',
	// URL普通方式参数 用于自动生成
	'url_common_param'     => false,
	// URL参数方式 0 按名称成对解析 1 按顺序解析
	'url_param_type'       => 0,
	// 是否开启路由
	'url_route_on'         => true,
	// 路由配置文件（支持配置多个）
	'route_config_file'    => ['route'],
	// 路由使用完整匹配
	'route_complete_match' => false,
	// 是否强制使用路由
	'url_route_must'       => false,
	// 域名部署
	'url_domain_deploy'    => false,
	// 域名根，如ezswoolephp.cn
	'url_domain_root'      => '',
	// 是否自动转换URL中的控制器和操作名
	'url_convert'          => true,
	// 默认的访问控制器层
	'url_controller_layer' => 'controller',
	// 表单请求类型伪装变量
	'var_method'           => '_method',
	// 表单ajax伪装变量
	'var_ajax'             => '_ajax',
	// 表单pjax伪装变量
	'var_pjax'             => '_pjax',
	// 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
	'request_cache'        => false,
	// 请求缓存有效期
	'request_cache_expire' => null,
	// 全局请求缓存排除规则
	'request_cache_except' => [],

	// +----------------------------------------------------------------------
	// | 异常及错误设置
	// +----------------------------------------------------------------------
	// 异常页面的模板文件
	'exception_tmpl'       => FASHOP_PATH.'tpl'.DS.'exception.tpl',

	// 错误显示信息,非调试模式有效
	'error_message'        => '页面错误！请稍后再试～',
	// 显示错误信息
	'show_error_msg'       => false,
	// 异常处理handle类 留空使用 \ezswoole\exception\Handle
	'exception_handle'     => '',
	// 是否记录trace信息到日志
	'record_trace'         => false,

	// +----------------------------------------------------------------------
	// | 日志设置
	// +----------------------------------------------------------------------

	'log' => [
		// 日志记录方式，内置 file socket 支持扩展
		'type'                => 'File',
		// 日志保存目录
		'path'                => LOG_PATH,
		// 日志记录级别
		'level'               => [],
		//单个日志文件的大小限制，超过后会自动记录到第二个文件
		'file_size'           => 2097152,
		// 显示加载文件
		'show_included_files' => false,
	],

	// +----------------------------------------------------------------------
	// | 缓存设置
	// +----------------------------------------------------------------------

	'cache' => [
		// 驱动方式
		'type'      => 'files',
		// 缓存保存目录
		'path'      => CACHE_PATH,
		// 缓存文件后缀
		'extension' => 'txt',
	],

	// +----------------------------------------------------------------------
	// | 会话设置
	// +----------------------------------------------------------------------

	'session' => [
		'id'             => '',
		// SESSION_ID的提交变量,解决flash上传跨域
		'var_session_id' => '',
		// SESSION 前缀
		'prefix'         => 'ezswoole',
		// 驱动方式 支持redis memcache memcached
		'type'           => '',
		// 是否自动开启 SESSION
		'auto_start'     => true,
		'httponly'       => true,
		'secure'         => false,
	],

	// +----------------------------------------------------------------------
	// | Cookie设置
	// +----------------------------------------------------------------------
	'cookie'  => [
		// cookie 名称前缀
		'prefix'    => '',
		// cookie 保存时间
		'expire'    => 0,
		// cookie 保存路径
		'path'      => '/',
		// cookie 有效域名
		'domain'    => '',
		//  cookie 启用安全传输
		'secure'    => false,
		// httponly设置
		'httponly'  => '',
		// 是否使用 setcookie
		'setcookie' => true,
	],

	// +----------------------------------------------------------------------
	// | 数据库设置
	// +----------------------------------------------------------------------

	'database' => [
		// 数据库类型
		'type'            => 'mysql',
		// 数据库连接DSN配置
		'dsn'             => '',
		// 服务器地址
		'hostname'        => '127.0.0.1',
		// 数据库名
		'database'        => '',
		// 数据库用户名
		'username'        => 'root',
		// 数据库密码
		'password'        => '',
		// 数据库连接端口
		'hostport'        => '',
		// 数据库连接参数
		'params'          => [],
		// 数据库编码默认采用utf8
		'charset'         => 'utf8',
		// 数据库表前缀
		'prefix'          => '',
		// 数据库调试模式
		'debug'           => false,
		// 是否严格检查字段是否存在
		'fields_strict'   => true,
		// 自动写入时间戳字段
		'auto_timestamp'  => false,
		// 时间字段取出后的默认时间格式
		'datetime_format' => 'Y-m-d H:i:s',
		// 是否需要进行SQL性能分析
		'sql_explain'     => false,
	],

	// 定时任务
	'corn'     => [
		// 单位为秒
		'loop_time' => 1,
	],

];
