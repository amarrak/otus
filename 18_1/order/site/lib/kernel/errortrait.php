<?php
namespace Kernel;

trait ErrorTrait
{
	private $lastErrorCode = 0;
	private $lastErrorMessage = "";

	public function getLastErrorCode(): int
	{
		return $this->lastErrorCode;
	}

	public function getLastErrorMessage(): string
	{
		return $this->lastErrorMessage;
	}

	protected function setError($code, $message = ""): void
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

	protected function clearErrors(): void
	{
		$this->lastErrorCode = 0;
		$this->lastErrorMessage = "";
	}
}