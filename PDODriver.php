<?php
/**
 * @package    SugiPHP
 * @subpackage Database PDO
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Database;

class PDODriver implements DriverInterface
{
	/**
	 * PgSQL connection handle.
	 *
	 * @var object
	 */
	protected $dbHandle;

	/**
	 * Database connection parameters.
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Constructor.
	 *
	 * @param mixed $database
	 */
	public function __construct($database)
	{
		if (is_array($database)) {
			$this->params = $database;
		} else {
			$this->setHandle($database);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function open()
	{
		// if we have a PDO database handle (connection)
		if ($this->dbHandle) {
			return ;
		}

		// establish connection
		$this->dbHandle = new PDO($this->params["dsn"], $this->params["user"], $this->params["pass"]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function close()
	{
		if ($this->dbHandle) {
			$this->dbHandle = null;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function escape($param)
	{
		return trim($this->dbHandle->quote($param), "'");
	}

	/**
	 * {@inheritdoc}
	 */
	public function query($sql)
	{
		return $this->dbHandle->query($sql);
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch($res)
	{
		return $res->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * {@inheritdoc}
	 */
	public function affected($res)
	{
		return $res->rowCount();
	}

	/**
	 * {@inheritdoc}
	 */
	public function lastId()
	{
		return $this->dbHandle->lastInsertId();
	}

	/**
	 * {@inheritdoc}
	 */
	public function free($res)
	{
		//
	}

	/**
	 * {@inheritdoc}
	 */
	public function error()
	{
		return $this->dbHandle->errorInfo();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHandle()
	{
		return $this->dbHandle;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHandle($dbHandle)
	{
		if (is_object($dbHandle) and ($dbHandle instanceof \PDO)) {
			$this->dbHandle = $dbHandle;
		} else {
			throw new Exception("Handle must be PDO object", "internal_error");
		}
	}


	/*
	 * Custom methods
	 */
	// public function __call($name, $arguments)
	// {
	// 	return call_user_func_array(array($this->dbHandle, $name), $arguments);
	// }

	/**
	 * Begin transaction.
	 *
	 * @return bool
	 */
	public function beginTransaction()
	{
		return $this->dbHandle->beginTransaction();
	}

	/**
	 * Commit transaction.
	 *
	 * @return bool
	 */
	public function commit()
	{
		return $this->dbHandle->commit();
	}

	/**
	 * Rollback transaction.
	 *
	 * @return bool
	 */
	public function rollBack()
	{
		return $this->dbHandle->rollBack();
	}

	/**
	 * Checks if inside a transaction.
	 *
	 * @return bool
	 */
	public function inTransaction()
	{
		return $this->dbHandle->inTransaction();
	}

	/**
	 * Execute an SQL statement and return the number of affected rows.
	 *
	 * @param  string $statement The SQL statement to prepare and execute. Data inside the query should be properly escaped.
	 * @return integer
	 */
	public function exec($statement)
	{
		return $this->dbHandle->exec($statement);
	}

	/**
	 * Prepares a statement for execution and returns a statement object.
	 *
	 * @param  string $statement This must be a valid SQL statement for the target database server.
	 * @return PDOStatement
	 */
	public function prepare($statement)
	{
		return $this->dbHandle->prepare($statement);
	}
}
