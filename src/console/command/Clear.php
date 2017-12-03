<?php

namespace fashop\console\command;

use fashop\console\Command;
use fashop\console\Input;
use fashop\console\input\Option;
use fashop\console\Output;

class Clear extends Command {
	protected function configure() {
		// 指令配置
		$this
			->setName('clear')
			->addOption('path', 'd', Option::VALUE_OPTIONAL, 'path to clear', null)
			->setDescription('Clear runtime file');
	}

	protected function execute(Input $input, Output $output) {
		$path = $input->getOption('path') ?: RUNTIME_PATH;

		if (is_dir($path)) {
			$this->clearPath($path);
		}

		$output->writeln("<info>Clear Successed</info>");
	}

	protected function clearPath($path) {
		$path  = realpath($path) . DS;
		$files = scandir($path);
		if ($files) {
			foreach ($files as $file) {
				if ('.' != $file && '..' != $file && is_dir($path . $file)) {
					$this->clearPath($path . $file);
				} elseif ('.gitignore' != $file && is_file($path . $file)) {
					unlink($path . $file);
				}
			}
		}
	}
}
