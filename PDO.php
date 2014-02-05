<?php
/**
 * @package    SugiPHP
 * @subpackage Database PDO
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Database;

use PDO as BasePDO;

class PDO extends BasePDO
{
	/**
	 * PDO constructor
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array  $driver_options
	 */
	public function __construct($dsn, $username = null, $password = null, array $driver_options = null)
	{
		parent::__construct($dsn, $username, $password, $driver_options);
		// Set error handling to Exception
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// Fetch return results as associative array
		$this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		// Use SugiPHP\PDOStatement statements instead of PDOStatement
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array(__NAMESPACE__."\PDOStatement", array()));
	}
}
