<?php

namespace fashop\console\command\optimize;

use fashop\console\Command;
use fashop\console\Input;
use fashop\console\Output;

class Route extends Command {
	/** @var  Output */
	protected $output;

	protected function configure() {
		$this->setName('optimize:route')
			->setDescription('Build route cache.');
	}

	protected function execute(Input $input, Output $output) {
		file_put_contents(RUNTIME_PATH . 'route.php', $this->buildRouteCache());
		$output->writeln('<info>Succeed!</info>');
	}

	protected function buildRouteCache() {
		$files = \fashop\Config::get('route_config_file');
		foreach ($files as $file) {
			if (is_file(CONF_PATH . $file . CONF_EXT)) {
				$config = include CONF_PATH . $file . CONF_EXT;
				if (is_array($config)) {
					\fashop\Route::import($config);
				}
			}
		}
		$rules = \fashop\Route::rules(true);
		array_walk_recursive($rules, [$this, 'buildClosure']);
		$content = '<?php ' . PHP_EOL . 'return ';
		$content .= var_export($rules, true) . ';';
		$content = str_replace(['\'[__start__', '__end__]\''], '', stripcslashes($content));
		return $content;
	}

	protected function buildClosure(&$value) {
		if ($value instanceof \Closure) {
			$reflection = new \ReflectionFunction($value);
			$startLine  = $reflection->getStartLine();
			$endLine    = $reflection->getEndLine();
			$file       = $reflection->getFileName();
			$item       = file($file);
			$content    = '';
			for ($i = $startLine - 1; $i <= $endLine - 1; $i++) {
				$content .= $item[$i];
			}
			$start = strpos($content, 'function');
			$end   = strrpos($content, '}');
			$value = '[__start__' . substr($content, $start, $end - $start + 1) . '__end__]';
		}
	}
}
