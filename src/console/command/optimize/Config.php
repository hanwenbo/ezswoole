<?php
namespace ezswoole\console\command\optimize;

use ezswoole\Config as FashopConfig;
use ezswoole\console\Command;
use ezswoole\console\Input;
use ezswoole\console\input\Argument;
use ezswoole\console\Output;

class Config extends Command {
	/** @var  Output */
	protected $output;

	protected function configure() {
		$this->setName('optimize:config')
			->addArgument('module', Argument::OPTIONAL, 'Build module config cache .')
			->setDescription('Build config and common file cache.');
	}

	protected function execute(Input $input, Output $output) {
		if ($input->hasArgument('module')) {
			$module = $input->getArgument('module') . DS;
		} else {
			$module = '';
		}

		$content = '<?php ' . PHP_EOL . $this->buildCacheContent($module);

		if (!is_dir(RUNTIME_PATH . $module)) {
			@mkdir(RUNTIME_PATH . $module, 0755, true);
		}

		file_put_contents(RUNTIME_PATH . $module . 'init' . EXT, $content);

		$output->writeln('<info>Succeed!</info>');
	}

	protected function buildCacheContent($module) {
		$content = '';
		$path    = realpath(APP_PATH . $module) . DS;

        // todo 改造
		if ($module) {
			// 加载模块配置
			$config = FashopConfig::load(CONF_PATH . $module . 'config' . CONF_EXT);

			// 读取数据库配置文件
			$filename = CONF_PATH . $module . 'database' . CONF_EXT;
			FashopConfig::load($filename, 'database');

			// 加载应用状态配置
			if ($config['app_status']) {
				$config = FashopConfig::load(CONF_PATH . $module . $config['app_status'] . CONF_EXT);
			}
			// 读取扩展配置文件
			if (is_dir(CONF_PATH . $module . 'extra')) {
				$dir   = CONF_PATH . $module . 'extra';
				$files = scandir($dir);
				foreach ($files as $file) {
					if (strpos($file, CONF_EXT)) {
						$filename = $dir . DS . $file;
						FashopConfig::load($filename, pathinfo($file, PATHINFO_FILENAME));
					}
				}
			}
		}


		$content .= '\ezswoole\Config::set(' . var_export(FashopConfig::get(), true) . ');';
		return $content;
	}
}
