<?php
/**
 * @package    SugiPHP
 * @subpackage Database
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Database;

class MySqlDriver implements DriverInterface
{

	/**
	 * MySQLi connection handle.
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
	 * Default value is true;
	 * This can be manually set to false with mysqli_autocommit(false)
	 * or can temporary deactivate using begin() and restored with commit() or 
	 * rollback() functions
	 */
	private $autocommit = true;

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
		// if we have a MySQL database handle (connection)
		if ($this->dbHandle) {
			return ;
		}

		$params = $this->params;

		/*
		 * When one of those are not given the MySQLi's default will be used
		 */
		$user = (isset($params['user'])) ? $params['user'] : null;
		$pass = (isset($params['pass'])) ? $params['pass'] : null;
		$host = (isset($params['host'])) ? $params['host'] : null;
		$database = (isset($params['database'])) ? $params['database'] : null;

		// Establish connection
		if (!$this->dbHandle = @mysqli_connect($host, $user, $pass, $database)) {
			throw new Exception(mysqli_connect_error(), "connection_error");
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
		return mysqli_real_escape_string($this->dbHandle, $param);
	}

	/**
	 * {@inheritdoc}
	 */
	public function query($sql)
	{
		return @mysqli_query($this->dbHandle, $sql, MYSQLI_STORE_RESULT);
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch($res)
	{
		return mysqli_fetch_assoc($res);
	}

	/**
	 * {@inheritdoc}
	 */
	public function affected($res)
	{
		return mysqli_affected_rows($this->dbHandle);
	}

	/**
	 * {@inheritdoc}
	 */
	public function lastId()
	{
		return mysqli_insert_id($this->dbHandle);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function free($res)
	{
		mysqli_free_result($res);
	}

	/**
	 * {@inheritdoc}
	 */
	public function error()
	{
		return mysqli_error($this->dbHandle);
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
		if (is_object($dbHandle) and ($dbHandle instanceof \MySQLi)) {
			$this->dbHandle = $dbHandle;
		} else {
			throw new Exception("Handle must be MySQLi object", "internal_error");
		}		
	}


	/*
	 * Other functions that are not part of the DriverInterface
	 */
	
	/**
	 * Begin transaction
	 */
	public function begin()
	{
		if (!$this->autocommit) {
			return $this->mysqli_autocommit(false);
		} else {
			return true;
		}
	}

	/**
	 * Commit transaction
	 */
	public function commit()
	{
		$r = mysqli_commit($this->dbHandle);
		if (!$this->autocommit) {
			$this->mysqli_autocommit(true);
		}
		return $r;
	}
	
	/**
	 * Rollback transaction
	 */
	public function rollback()
	{
		$r = mysqli_rollback($this->dbHandle);
		if (!$this->autocommit) {
			$this->mysqli_autocommit(true);
		}
		return $r;
	}

	/**
	 * Turns on or off auto-commiting database modifications
	 * To get current auto-commit mode: SELECT @@autocommit
	 * @param boolean - Whether to turn on auto-commit or not. 
	 * @return boolean
	 */
	public function mysqli_autocommit($mode)
	{
		if (mysqli_autocommit($this->dbHandle, $mode)) {
			$this->autocommit = $mode;
			return true;		
		}

		return false;
	}

}
