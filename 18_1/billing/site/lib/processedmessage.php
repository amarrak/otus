<?php
use Kernel;

class ProcessedMessage
	implements Kernel\Errorable
{
	use Kernel\ErrorTrait;

	private static $availableFields = ["message_id"];

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

		$sql = 'INSERT INTO b_processed_message('.$subSql1.') VALUES('.$subSql2.')';

		$conn = $this->db->getConnection();
		if ($conn === null)
		{
			$this->setError($this->db);
			return 0;
		}

		$statement = $conn->prepare($sql);
		if (!$statement->execute($subValues))
		{
			$this->setError(11, "Already processed");
			return 0;
		}

		return $conn->lastInsertId();
	}

	public function delete($id) : int
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			$this->setError(1, "Invalid argument 'id'");
			return 0;
		}

		$sql = 'DELETE FROM b_processed_message where ID = :id';
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

	public function isProcessed($message_id)
	{
		if (empty($message_id))
		{
			$this->setError(1, "Invalid argument 'message_id'");
			return [];
		}

		$sql = 'SELECT * FROM b_processed_message where message_id = :message_id';
		$subValues[":message_id"] = $message_id;

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