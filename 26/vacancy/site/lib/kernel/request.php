<?php
namespace Kernel;

class Request
{
	public function getRequestMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	public function getRequestUri()
	{
		return $_SERVER['REQUEST_URI'];
	}

	public function getServerProtocol()
	{
		return $_SERVER['SERVER_PROTOCOL'];
	}

	public function getInput()
	{
		$inputData = file_get_contents("php://input");
		return json_decode($inputData, true);
	}

	public function getHeaders(): array
	{
		$headers = array();
		foreach ($_SERVER as $key => $value)
		{
			if (strpos($key, 'HTTP_') !== 0)
			{
				continue;
			}

			$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
			$headers[$header] = $value;
		}
		return $headers;
		//return apache_request_headers();
	}
}
