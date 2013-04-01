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
	public function open(); // or connect

	public function close(); // or disconnect

	public function escape($param);

	public function query($sql);

	public function fetch($res);
}
