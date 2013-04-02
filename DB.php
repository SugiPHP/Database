<?php
/**
 * @package    SugiPHP
 * @subpackage Database
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Database;

class DB
{
	/**
	 * Database driver instance.
	 * 
	 * @var object DriverInterface
	 */
	protected $driver;


	/**
	 * Hooks
	 * @var array of events
	 */
	protected $hooks = array();

	/**
	 * Constructor.
	 * 
	 * @param DriverInterface $driver
	 */
	public function __construct(DriverInterface $driver)
	{
		$this->driver = $driver;
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Calling methods that are only part of the database driver
	 */
	public function __call($method, $args)
	{
		if (method_exists($this->driver, $method)) {
			return call_user_func_array(array($this->driver, $method), $args);
		}
		throw new Exception("Method $method does not exist", "internal_error");		
	}

	/**
	 * Opens connection to the database
	 */
	public function open()
	{
		if (!$this->driver->getHandle()) {
			$this->triggerAction("pre", "open");
			$this->driver->open();
			$this->triggerAction("post", "open");
		}
	}

	/**
	 * Closes connection to the database
	 */
	public function close()
	{
		if ($this->driver->getHandle) {
			$this->triggerAction("pre", "close");
			$this->driver->close();
			$this->triggerAction("post", "close");
		}
	}

	/**
	 * Escape method
	 * 
	 * @param  string $item
	 * @return string
	 */
	public function escape($item)
	{
		// For delayed opens
		$this->open();

		return $this->driver->escape($item);
	}

	/**
	 * Executes query.
	 * Query could be any valid SQL statement.
	 * 
	 * @param  string $sql
	 * @throws SugiPHP\Database\Exception If the query fails
	 * @return mixed
	 */
	public function query($sql)
	{
		// For delayed opens
		$this->open();

		$this->triggerAction("pre", "query", $sql);
		if ($res = $this->driver->query($sql)) {
			$this->triggerAction("post", "query", $sql);
			return $res;
		}
			
		throw new Exception($this->driver->error(), "sql_error");
	}

	/**
	 * Fetches one row
	 * 
	 * @param  handle $res result returned from query()
	 * @return array
	 * @throws SugiPHP\Database\Exception
	 */
	public function fetch($res)
	{
		try {
			$res = $this->driver->fetch($res);
		} catch (\Exception $e) {
			throw new Exception($e->getMessage(), "resource_error");
		}

		return $res;
	}

	/**
	 * Fetches all rows
	 * 
	 * @param  handle $res result returned from query()
	 * @return array
	 */
	public function fetchAll($res)
	{
		$return = array();
		while ($row = $this->fetch($res)) {
			$return[] = $row;
		}

		return $return;
	}

	/**
	 * Fetches all rows
	 * 
	 * @param  string $sql SQL statement
	 * @return array
	 */
	public function all($sql)
	{
		return $this->fetchAll($this->query($sql));
	}

	/**
	 * Fetches single row
	 * 
	 * @param  string $sql SQL statement
	 * @return array|null
	 */
	public function single($sql)
	{
		if ($res = $this->query($sql)) {
			return $this->fetch($res);
		}

		return null;
	}

	/**
	 * Returns first field of the first row
	 * 
	 * @param  string $sql - SQL statment
	 * @return string|null
	 */
	public function singleField($sql)
	{
		if ($row = $this->single($sql)) {
			return array_shift($row);
		}

		return null;
	}

	/**
	 * Returns rows affected by the query
	 * 
	 * @param handle $res handle returned by query()
	 * @return integer
	 */
	public function affected($res = null)
	{
		return $this->driver->affected($res);
	}

	/**
	 * Returns last ID returned after successful INSERT statement
	 * 
	 * @return mixed
	 */
	public function lastId()
	{
		return $this->driver->lastId();
	}

	/**
	 * Frees result
	 * 
	 * @param handle $res handle returned by query()
	 * @throws SugiPHP\Database\Exception
	 */
	public function free($res)
	{
		if (!$res) {
			throw new Exception("Could not free invalid resource.", "resource_error");
		}
		$this->driver->free($res);
	}

	/**
	 * Hook a callback function/method to some hookable events.
	 * Hooks could be 'pre_' and 'post_'.
	 *
	 * <code>
	 * 	// to hook an event before executing a query
	 *  Database::hook('pre_query', array($object, 'method_name'));
	 *  // to hook an event after executing a query
	 *  Database::hook('post_query', 'function_name')
	 * </code>
	 * 
	 * @param string $event - pre or post method name
	 * @param mixed $callback - callable function or method name
	 */
	public function hook($event, $callback)
	{
		if (is_array($callback)) $inx = get_class($callback[0])."::".$callback[1];
		elseif (gettype($callback) == "object") $inx = uniqid();
		else $inx = $callback;
				
		$this->hooks[$event][$inx] = $callback;
	}

	
	/**
	 * Unhook.
	 * If callback is not given all callbacks are unhooked from this event.
	 * If event is not given all callbacks are unhooked.
	 * 
	 * <code>
	 * 	Database::unhook('pre_query', array($this, 'before_query')); // This will unhook method $this->before_query before query
	 * 	Database::unhook('post_query'); // This will unhook all callbacks which are executed after query
	 *  Database::unhook(); // This will unhook all callbacks
	 *  Database::unhook(false, 'test'); // This will unhook callback function test from any (pre and post) events
	 * </code>
	 * 
	 * @param string $event
	 * @param mixed $callback - callback function to unhook.
	 */
	public function unhook($event = null, $callback = null)
	{
		if (is_array($callback)) $inx = get_class($callback[0])."::".$callback[1];
		else $inx = $callback;
						
		if (is_null($event) AND is_null($callback)) {
			$this->hooks = array();
		}
		elseif (is_null($callback)) {
			$this->hooks[$event] = array();
		}
		elseif (is_null($event)) {
			foreach ($this->hooks as $key => $value) {
				unset($this->hooks[$key][$inx]);
			}
		}
		else {
			unset($this->hooks[$event][$inx]);
		}
	}

	/**
	 * Escapes each element in the array
	 * 
	 * @param array
	 * @return array
	 */
	public function escapeAll(array $values)
	{
		$return = array();
		foreach ($values as $key => $value) {
			if (is_null($value)) $value = "null";
			elseif (is_numeric($value)) ;
			elseif (is_bool($value)) $value = $value ? "TRUE" : "FALSE";
			else $value = "'" . $this->escape($value) . "'";
			$return[$key] = $value;
		}
		return $return;
	}

	/**
	 * Escapes all parameters and binds them in the SQL
	 * 
	 * @param  string $sql
	 * @param  array $params Associative array, where the key is replaced in the SQL with the value
	 * @param  boolean $nullMissing - set NULL when the param is missing
	 * @return string
	 */
	public function bindParams($sql, array $params, $nullMissing = true)
	{
		$params = $this->escapeAll($params);
		if (preg_match_all("#:([a-zA-Z0-9_]+)#", $sql, $matches)) {
			foreach ($matches[1] as $match) {
				if (isset($params[$match])) {
					$sql = str_replace(":$match", $params[$match], $sql);
				} elseif ($nullMissing) {
					$sql = str_replace(":$match", "null", $sql);
				}				
			}
		}
		return $sql;
	}

	protected function triggerAction($type, $action, $data = null)
	{
		$hook = "{$type}_{$action}";
		// check for hooks
		if (!empty($this->hooks[$hook])) {
			foreach ($this->hooks[$hook] as $callback) {
				$callback($action, $data);
			}
		}
	}

}
