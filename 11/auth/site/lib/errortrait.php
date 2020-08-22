<?php

trait ErrorTrait
{
	private $lastErrorCode = 0;
	private $lastErrorMessage = "";

	public function getLastErrorCode()
	{
		return $this->lastErrorCode;
	}

	public function getLastErrorMessage()
	{
		return $this->lastErrorMessage;
	}

	protected function setError($code, $message = "")
	{
		if ($code instanceof Errorable)
		{
			$this->lastErrorCode = $code->getLastErrorCode();
			$this->lastErrorMessage = $code->getLastErrorMessage();
		}
		else
		{
			$this->lastErrorCode = $code;
			$this->lastErrorMessage = $message;
		}
	}

	protected function clearErrors()
	{
		$this->lastErrorCode = 0;
		$this->lastErrorMessage = "";
	}
}