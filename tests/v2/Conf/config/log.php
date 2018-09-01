<?php
return [
	// 日志记录方式，内置 file socket 支持扩展
	'type'                => 'Socket',
	// 日志保存目录
	'path'                => EASYSWOOLE_ROOT."/Runtime/Log/",
	// 日志记录级别
	'level'               => ['log', 'error', 'info', 'sql', 'notice', 'alert', 'debug', 'trace'],
	'file_size'           => 2097152,
	'show_included_files' => false,
];