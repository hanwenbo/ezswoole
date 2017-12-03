<?php

namespace fashop\console\command\make;

use fashop\console\command\Make;

class Model extends Make {
	protected $type = "Model";

	protected function configure() {
		parent::configure();
		$this->setName('make:model')
			->setDescription('Create a new model class');
	}

	protected function getStub() {
		return __DIR__ . '/stubs/model.stub';
	}

	protected function getNamespace($appNamespace, $module) {
		return parent::getNamespace($appNamespace, $module) . '\model';
	}
}
