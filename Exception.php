<?php
/**
 * @package    SugiPHP
 * @subpackage Database
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Database;

/**
 * Exception for SugiPHP\Database.
 */
class Exception extends \Exception
{
	public $error_type;

	public function __construct($message, $error_type)
	{
		parent::__construct($message);
		$this->error_type = $error_type;
	}

	public function getType()
	{
		return $this->error_type;
	}
}
