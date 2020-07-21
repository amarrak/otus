<?php
if ((strpos($_SERVER["REQUEST_URI"], "/health/") === 0) || ($_SERVER["REQUEST_URI"] === "/health"))
{
	echo '{"status": "OK"}';
	die();
}

if ((strpos($_SERVER["REQUEST_URI"], "/phpinfo/") === 0) || ($_SERVER["REQUEST_URI"] === "/phpinfo"))
{
	phpinfo();
	die();
}

echo "OK from PHP";
