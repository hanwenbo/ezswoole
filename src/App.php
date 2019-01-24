<?php

namespace ezswoole;

use EasySwoole\Utility\File;

/**
 * App 应用管理
 */
class App
{
	private $config;


	public function __construct()
	{
	}

	public function run()
	{
		$this->initDir();
		$this->initConfig();
		$this->initApp();
	}

	static $hooks = [];


	public function initApp()
	{
		date_default_timezone_set( $this->config['default_timezone'] );
		return Config::get();
	}

	private function initConfig()
	{
		$server_config = \EasySwoole\EasySwoole\Config::getInstance()->getConf( "." );
		foreach( $server_config as $key => $_config ){
			if( isset( $_config[$key] ) && is_array( $_config[$key] ) ){
				foreach( $_config[$key] as $k => $v ){
					Config::set( "{$key}.{$k}", $v );
				}
			} else{
				Config::set( "{$key}", $_config );
			}
		}
		if( Config::get( 'app_status' ) ){
			Config::load( CONF_PATH.Config::get( 'app_status' ) );
		}
		$this->config = Config::get();
	}

	private function initDir() : void
	{
		if( !is_dir( RUNTIME_PATH ) ){
			File::createDirectory( RUNTIME_PATH );
		}
		if( !is_dir( LOG_PATH ) ){
			File::createDirectory( LOG_PATH );
		}
		if( !is_dir( CACHE_PATH ) ){
			File::createDirectory( CACHE_PATH );
		}
		if( !is_dir( TEMP_PATH ) ){
			File::createDirectory( TEMP_PATH );
		}
		if( !is_dir( CONF_PATH ) ){
			File::createDirectory( CONF_PATH );
		}
		// 存放子配置项 如微信 定时任务等
		if( !is_dir( CONF_PATH.'config/' ) ){
			File::createDirectory( CONF_PATH.'config/' );
		}
	}
}
