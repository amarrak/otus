<?php

spl_autoload_register(static function ($class) {
	if (preg_match('/[^a-z0-9_.]/i', $class))
	{
		return;
	}

	include strtolower($class).'.php';
});

require __DIR__ . '/../vendor/autoload.php';