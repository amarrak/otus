<?php


class Response
{
	/** @var Request */
	private $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function render($status, $data = [])
	{
		if (mb_stristr(PHP_SAPI, "cgi") !== false)
		{
			header("Status: " . $status);
		}
		else
		{
			header($this->request->getServerProtocol() . " " . $status);
		}

		if (!empty($data))
		{
			if (is_array($data))
				echo json_encode($data);
			else
				echo $data;
		}
	}

}