<?php


class Database
	implements Errorable
{
	use ErrorTrait;

	private $host;
	private $databaseName;
	private $username;
	private $password;

	private $conn;

	public function __construct($host, $databaseName, $username, $password)
	{
		$this->host = $host;
		$this->databaseName = $databaseName;
		$this->username = $username;
		$this->password = $password;
	}

	public function getConnection()
	{
		if (!isset($this->conn))
		{
			try
			{
				$this->conn = new PDO(
					'mysql:dbname='.$this->databaseName.';host='.$this->host,
					$this->username,
					$this->password
				);
				$this->conn->exec("set names utf8");
			}
			catch (PDOException $e)
			{
				$this->setError($e->getCode(), $e->getMessage());
				return null;
			}
		}

		return $this->conn;
	}
}