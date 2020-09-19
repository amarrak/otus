<?php

spl_autoload_register(static function ($className) {

	$className = ltrim($className, "\\");
	$className = strtr($className, "QWERTYUIOPLKJHGFDSAZXCVBNM", "qwertyuioplkjhgfdsazxcvbnm");

	if (preg_match("#[^\\\\/a-zA-Z0-9_]#", $className))
	{
		return;
	}

	$className = str_replace("\\", "/", $className);

	include $className.'.php';
});

require_once __DIR__ . '/../vendor/autoload.php';
