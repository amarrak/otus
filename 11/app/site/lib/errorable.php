<?php


interface Errorable
{
	public function getLastErrorCode();
	public function getLastErrorMessage();
}