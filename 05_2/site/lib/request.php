<?php


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
}