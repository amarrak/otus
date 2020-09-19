<?php
use Kernel;

class Billing
	implements Kernel\Errorable
{
	use Kernel\ErrorTrait;

	private static $availableFields = ["user_id", "balance"];

	/** @var Kernel\Database $db */
	private $db;

	public function __construct(Kernel\Database $db)
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

		$sql = 'INSERT INTO b_balance('.$subSql1.') VALUES('.$subSql2.')';

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

		$sql = 'UPDATE b_balance SET '.$subSql.' where ID = :id';
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

		$sql = 'DELETE FROM b_balance where ID = :id';
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

		$sql = 'SELECT * FROM b_balance where ID = :id';
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

	public function getByUserId($userId)
	{
		$userId = (int)$userId;
		if ($userId <= 0)
		{
			$this->setError(1, "Invalid argument 'userId'");
			return [];
		}

		$sql = 'SELECT * FROM b_balance where user_id = :userid';
		$subValues[":userid"] = $userId;

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

	public function applyTransaction($userId, $sum) : bool
	{
		$userId = (int)$userId;
		if ($userId <= 0)
		{
			$this->setError(1, "Invalid argument 'userId'");
			return 0;
		}

		$sum = (double)$sum;

		$sql = 'UPDATE b_balance SET balance = balance + (:sum) where user_id = :user_id';
		$subValues[":sum"] = $sum;
		$subValues[":user_id"] = $userId;

		$conn = $this->db->getConnection();
		if ($conn === null)
		{
			$this->setError($this->db);
			return 0;
		}

		$statement = $conn->prepare($sql);
		$statement->execute($subValues);

		return true;
	}

}