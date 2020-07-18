<?php
if (strpos($_SERVER["REQUEST_URI"], "/health/") === 0)
{
	echo '{"status": "OK"}';
	die();
}

if (strpos($_SERVER["REQUEST_URI"], "/phpinfo/") === 0)
{
	phpinfo();
	die();
}

echo "OK from PHP";
