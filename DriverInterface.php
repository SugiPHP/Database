<?php
/**
 * @package    SugiPHP
 * @subpackage Database
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Database;

interface DriverInterface
{
	/**
	 * Establishes a database connection.
	 * 
	 * @return void
	 * @throws \SugiPHP\Database\Exception
	 */
	public function open();

	/**
	 * Closes database connection.
	 */
	public function close();

	/**
	 * Escapes a string for use as a query parameter.
	 * 
	 * @param string $param
	 * @return string
	 */
	public function escape($param);

	/**
	 * Executes a query.
	 * 
	 * @param string $sql SQL statement
	 * @return mixed - FALSE on query failure
	 */
	public function query($sql);

	/**
	 * Fetches a row.
	 * 
	 * @param resource handle $res
	 * @return array
	 */
	public function fetch($res);
	
	/**
	 * Returns the number of rows that were changed by the most recent SQL 
	 * statement (INSERT, UPDATE, REPLACE, DELETE)
	 * 
	 * @return integer
	 */
	public function affected($res);
	
	/**
	 * Returns the auto generated id used in the last query.
	 * 
	 * @return mixed
	 */
	public function lastId();
	
	/**
	 * Frees the memory associated with a result.
	 * 
	 * @param A result set identifier returned by query()
	 */
	public function free($res);
	
	/**
	 * Returns last error.
	 * 
	 * @return string
	 */
	public function error();

	/**
	 * Return a database handle.
	 * 
	 * @return object|null
	 */
	public function getHandle();

	/**
	 * Sets a Database handle
	 * 
	 * @param mixed $dbHandle
	 * @throws \SugiPHP\Database\Exception if the dbHandle is illegal
	 */
	public function setHandle($dbHandle);
}
