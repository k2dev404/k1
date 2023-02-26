<?php

namespace K1\System;

class Component
{
	private string $dir;
	private ?Application $app;
	private string $default = 'default';

	function __construct(string $dir)
	{
		$this->dir = $dir;
		$this->app = Application::getInstance();
	}

	public function controller($result = null)
	{
		$path = $this->dir . 'controller.php';

		$this->include($path, $result);
	}

	public function template(string $template = '', $result = null)
	{
		if (!$template) {
			$template = $this->default;
		}

		$path = $this->dir . '/templates/' . $template . '.php';

		$this->include($path, $result);
	}

	private function include(string $path, $result = null)
	{
		if (!file_exists($path)) {
			throw new \RuntimeException('Файл не найден "' . $path . '"');
		}

		$app = Application::getInstance();

		include($path);
	}
}