<?php
use Kernel as core;

class Vacancy
	implements core\Errorable
{
	use core\ErrorTrait;

	private static $availableFields = ["id", "department_id", "name", "active", "published", "description", "requirements"];

	/** @var core\Database $db */
	private $db;

	public function __construct(core\Database $db)
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

		$sql = 'INSERT INTO b_vacancy('.$subSql1.') VALUES('.$subSql2.')';

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

		$sql = 'UPDATE b_vacancy SET '.$subSql.' where ID = :id';
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

		$sql = 'DELETE FROM b_vacancy where ID = :id';
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

		$sql = 'SELECT * FROM b_vacancy where ID = :id';
		$subValues[":id"] = $id;

		$conn = $this->db->getConnection();
		if ($conn === null)
		{
			$this->setError($this->db);
			return [];
		}

		$statement = $conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$statement->execute($subValues);
		return $statement->fetch(PDO::FETCH_ASSOC);
	}
}