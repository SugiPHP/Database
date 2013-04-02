<?php
/**
 * @package    SugiPHP
 * @subpackage Database
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

use SugiPHP\Database\MySqlDriver as DB;

class MySqlTest extends PHPUnit_Framework_TestCase
{
	function testCreateWithArray()
	{
		$db = new DB(array("database" => "test"));
	}

	function testCreateWithSQLite3Object()
	{
		$db = new DB(new MySQLi());
	}

	/**
	 * @expectedException SugiPHP\Database\Exception
	 */
	function testCreateWithIllegalParam()
	{
		$db = new DB("string");
	}
}
