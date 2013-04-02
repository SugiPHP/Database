<?php
/**
 * @package    SugiPHP
 * @subpackage Database
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Database;

class SQLiteDriver implements DriverInterface
{
	/**
	 * SQLite3 handle
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
		if (is_string($database)) {
			$this->params["database"] = $database;
		} elseif (is_array($database)) {
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
		// if we have a SQLite database handle (connection)
		if ($this->dbHandle) {
			return ;
		}

		// Database parameter is mandatory
		if (empty($this->params["database"])) {
			throw new Exception("Database parameter is missing", "internal_error");
		}

		// Establish connection
		try {
			$this->dbHandle = new \SQLite3($this->params["database"]);
		} catch (\Exception $e) {
			throw new Exception($e->getMessage(), "connection_error");
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function close()
	{
		if ($this->dbHandle) {
			$this->dbHandle->close();
			$this->dbHandle = null;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function escape($param)
	{
		return $this->dbHandle->escapeString($param);
	}

	/**
	 * {@inheritdoc}
	 */
	public function query($sql)
	{
		// additional warning is triggered
		return @$this->dbHandle->query($sql);
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch($res)
	{
		return $res->fetchArray(SQLITE3_ASSOC);
	}

	/**
	 * {@inheritdoc}
	 */
	public function affected($res)
	{
		return $this->dbHandle->changes();
	}

	/**
	 * {@inheritdoc}
	 */
	public function lastId()
	{
		return $this->dbHandle->lastInsertRowID();
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function free($res)
	{
		return $res->finalize();
	}

	/**
	 * {@inheritdoc}
	 */
	public function error()
	{
		return $this->dbHandle->lastErrorMsg();
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
		if (is_object($dbHandle) and ($dbHandle instanceof \SQLite3)) {
			$this->dbHandle = $dbHandle;
		} else {
			throw new Exception("Handle must be SQLite3 object", "internal_error");
		}		
	}
}
