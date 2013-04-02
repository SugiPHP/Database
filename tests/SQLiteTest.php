<?php
/**
 * @package    SugiPHP
 * @subpackage Database
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

use SugiPHP\Database\SQLite as DB;

class SQLiteTest extends PHPUnit_Framework_TestCase
{
	function testCreateWithDBname()
	{
		$db = new DB(":memory:");
	}

	function testCreateWithArray()
	{
		$db = new DB(array("database" => ":memory:"));
	}

	function testCreateWithSQLite3Object()
	{
		$db = new DB(new SQLite3(":memory:"));
	}

	/**
	 * @expectedException SugiPHP\Database\Exception
	 */
	function testCreateWithIllegalParam()
	{
		$db = new DB(3);
	}

	function testOpenReturnsSQLite3()
	{

	}
}
