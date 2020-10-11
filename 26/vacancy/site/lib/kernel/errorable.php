<?php
namespace Kernel;

interface Errorable
{
	public function getLastErrorCode();
	public function getLastErrorMessage();
}