<?php

namespace fashop\console\command;

use fashop\console\Command;
use fashop\console\Input;
use fashop\console\input\Option;
use fashop\console\Output;

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
			\fashop\Build::module($input->getOption('module'));
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
		\fashop\Build::run($build);
		$output->writeln("Successed");

	}
}
