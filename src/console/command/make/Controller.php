<?php

namespace ezswoole\console\command\make;

use ezswoole\Config;
use ezswoole\console\command\Make;
use ezswoole\console\input\Option;

class Controller extends Make {

	protected $type = "Controller";

	protected function configure() {
		parent::configure();
		$this->setName('make:controller')
			->addOption('plain', null, Option::VALUE_NONE, 'Generate an empty controller class.')
			->setDescription('Create a new resource controller class');
	}

	protected function getStub() {
		if ($this->input->getOption('plain')) {
			return __DIR__ . '/stubs/controller.plain.stub';
		}

		return __DIR__ . '/stubs/controller.stub';
	}

	protected function getClassName($name) {
		return parent::getClassName($name) . (Config::get('controller_suffix') ? ucfirst(Config::get('url_controller_layer')) : '');
	}

	protected function getNamespace($appNamespace, $module) {
		return parent::getNamespace($appNamespace, $module) . '\controller';
	}

}
