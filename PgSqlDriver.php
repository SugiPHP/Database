<?php
/**
 * @package    SugiPHP
 * @subpackage Database
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Database;

class PgSqlDriver implements DriverInterface
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
		// if we have a PgSQL database handle (connection)
		if ($this->dbHandle) {
			return ;
		}
		// supported connection params
		$keywords = array(
			"host" => "host", 
			"port" => "port", 
			"user" => "user",
			"pass" => "password",
			"database" => "dbname"
		);
		// creating connection string
		$conn = array();
		foreach ($keywords as $key => $keyword) {
			if (!empty($this->params[$key])) {
				$conn[] = "{$keyword}={$this->params[$key]}";
			}
		}
		$conn = implode(" ", $conn);

		// before connection we want to handle errors/warnings and convert 
		// them to SugiPHP\Database\Exception
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			restore_error_handler();
			throw new Exception($errstr, "connection_error");
		});
		// establish connection
		$this->dbHandle = \pg_connect($conn);
		// restoring error_handler
		restore_error_handler();
	}

	/**
	 * {@inheritdoc}
	 */
	public function close()
	{
		if ($this->dbHandle) {
			\pg_close($this->dbHandle);
			$this->dbHandle = null;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function escape($param)
	{
		return \pg_escape_string($this->dbHandle, $param);
	}

	/**
	 * {@inheritdoc}
	 */
	public function query($sql)
	{
		// additional warning is triggered, when the query is wrong
		return @\pg_query($this->dbHandle, $sql);
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch($res)
	{
		return \pg_fetch_assoc($res);
	}

	/**
	 * {@inheritdoc}
	 */
	public function affected($res)
	{
		return \pg_affected_rows($res);
	}

	/**
	 * {@inheritdoc}
	 */
	public function lastId()
	{
		$res = \pg_fetch_row(\pg_query("SELECT lastval()"));
		return $res[0];
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function free($res)
	{
		\pg_free_result($res);
	}

	/**
	 * {@inheritdoc}
	 */
	public function error()
	{
		return \pg_last_error($this->dbHandle);
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
		if (is_object($dbHandle) and ($dbHandle instanceof \PostgreSQL)) {
			$this->dbHandle = $dbHandle;
		} else {
			throw new Exception("Handle must be PostgreSQL object", "internal_error");
		}		
	}

	/*
	 * Custom methods
	 */
	
	/**
	 * Begin transaction
	 */
	public function begin()
	{
		return \pg_query($this->dbHandle, "BEGIN TRANSACTION");
	}

	/**
	 * Commit transaction
	 */
	public function commit()
	{
		return \pg_query($this->dbHandle, "COMMIT TRANSACTION");
	}
	
	/**
	 * Rollback transaction
	 */
	public function rollback()
	{
		return \pg_query($this->dbHandle, "ROLLBACK TRANSACTION");
	}
}
