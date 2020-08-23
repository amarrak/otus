<?php

class User
	implements Errorable
{
	use ErrorTrait;

	private static $availableFields = ["login", "password", "firstName", "lastName", "email", "phone"];

	/** @var Database $db */
	private $db;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}

	public function add(array $data) : int
	{
		$subSql1 = "";
		$subSql2 = "";
		$subValues = [];
		foreach ($data as $key => $value)
		{
			if (!in_array($key, self::$availableFields, true))
			{
				$this->setError(1, "Invalid argument '".$key."'");
				return 0;
			}

			if ($subSql1 !== "")
			{
				$subSql1 .= ", ";
				$subSql2 .= ", ";
			}
			$subSql1 .= $key;
			$subSql2 .= ":".$key;
			$subValues[":".$key] = $value;
		}

		if ($subSql1 === "")
		{
			$this->setError(1, "Invalid arguments");
			return 0;
		}

		$sql = 'INSERT INTO b_user('.$subSql1.') VALUES('.$subSql2.')';

		$conn = $this->db->getConnection();
		if ($conn === null)
		{
			$this->setError($this->db);
			return 0;
		}

		$statement = $conn->prepare($sql);
		$statement->execute($subValues);

		return $conn->lastInsertId();
	}

	public function update($id, array $data) : int
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			$this->setError(1, "Invalid argument 'id'");
			return 0;
		}

		$subSql = "";
		$subValues = [];
		foreach ($data as $key => $value)
		{
			if (!in_array($key, self::$availableFields, true))
			{
				$this->setError(1, "Invalid argument '".$key."'");
				return 0;
			}

			if ($subSql !== "")
			{
				$subSql .= ", ";
			}
			$subSql .= $key." = :".$key;
			$subValues[":".$key] = $value;
		}

		if ($subSql === "")
		{
			$this->setError(1, "Invalid arguments");
			return 0;
		}

		$sql = 'UPDATE b_user SET '.$subSql.' where ID = :id';
		$subValues[":id"] = $id;

		$conn = $this->db->getConnection();
		if ($conn === null)
		{
			$this->setError($this->db);
			return 0;
		}

		$statement = $conn->prepare($sql);
		$statement->execute($subValues);

		return $id;
	}

	public function delete($id) : int
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			$this->setError(1, "Invalid argument 'id'");
			return 0;
		}

		$sql = 'DELETE FROM b_user where ID = :id';
		$subValues[":id"] = $id;

		$conn = $this->db->getConnection();
		if ($conn === null)
		{
			$this->setError($this->db);
			return 0;
		}

		$statement = $conn->prepare($sql);
		$statement->execute($subValues);

		return $id;
	}

	public function getById($id)
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			$this->setError(1, "Invalid argument 'id'");
			return [];
		}

		$sql = 'SELECT * FROM b_user where ID = :id';
		$subValues[":id"] = $id;

		$conn = $this->db->getConnection();
		if ($conn === null)
		{
			$this->setError($this->db);
			return [];
		}

		$statement = $conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$statement->execute($subValues);
		return $statement->fetch();
	}

	public function getByCredentials($login, $password)
	{
		if (empty($login))
		{
			$this->setError(1, "Invalid argument 'login'");
			return [];
		}

		$sql = 'SELECT * FROM b_user where login = :login and password = :password';
		$subValues[":login"] = $login;
		$subValues[":password"] = $password;

		$conn = $this->db->getConnection();
		if ($conn === null)
		{
			$this->setError($this->db);
			return [];
		}

		$statement = $conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$statement->execute($subValues);

		return $statement->fetch();
	}

}
