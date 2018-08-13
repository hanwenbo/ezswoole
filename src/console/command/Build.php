<?php

namespace ezswoole\console\command;

use ezswoole\console\Command;
use ezswoole\console\Input;
use ezswoole\console\input\Option;
use ezswoole\console\Output;

class Build extends Command {

	/**
	 * {@inheritdoc}
	 */
	protected function configure() {
		$this->setName('build')
			->setDefinition([
				new Option('config', null, Option::VALUE_OPTIONAL, "build.php path"),
				new Option('module', null, Option::VALUE_OPTIONAL, "module name"),
			])
			->setDescription('Build Application Dirs');
	}

	protected function execute(Input $input, Output $output) {
		if ($input->hasOption('module')) {
			\ezswoole\Build::module($input->getOption('module'));
			$output->writeln("Successed");
			return;
		}

		if ($input->hasOption('config')) {
			$build = include $input->getOption('config');
		} else {
			$build = include APP_PATH . 'build.php';
		}
		if (empty($build)) {
			$output->writeln("Build Config Is Empty");
			return;
		}
		\ezswoole\Build::run($build);
		$output->writeln("Successed");

	}
}
