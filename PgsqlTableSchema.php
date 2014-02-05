<?php
/**
 * @package    SugiPHP
 * @subpackage Database PDO
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Database;

class PgsqlTableSchema
{
	public function __construct()
	{

	}

	public function extractTable($tableName, $PDOConnection)
	{
		$result = array();

		$sql = "SELECT a.attname AS name, format_type(a.atttypid, a.atttypmod) AS type,
		               a.attnotnull AS not_null/*, a.atthasdef AS has_default*/, d.adsrc AS default
		        FROM pg_attribute a
		        LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
		        WHERE a.attnum > 0 AND NOT a.attisdropped AND
		              a.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname = :table AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE nspname = 'public'))
		        ORDER BY a.attnum";
		$sth = $PDOConnection->prepare($sql);
		$sth->bindValue(":table", $tableName, PDO::PARAM_STR);
		$sth->execute();

		$columns = $sth->fetchAll();
		foreach ($columns as $col) {
			$name = $col["name"];
			$col["primary"] = false;
			$col["unique"] = false;
			unset($col["name"]);
			$result[$name] = $col;
		}

		$keys = $this->extractKeys($tableName, $PDOConnection);
		foreach ($keys as $key) {
			if ($key["type"] == "p") {
				$result[$key["key"]]["primary"] = true;
			} elseif ($key["type"] == "u") {
				$result[$key["key"]]["unique"] = true;
			} elseif ($key["type"] == "f") {
				// var_dump($key);

			} else {
				throw new \Exception("Unknown '{$key["type"]}' key");
			}
		}


		return $result;
	}

	public function extractKeys($tableName, $PDOConnection)
	{
		$sql = "SELECT contype AS type, consrc, indkey AS key FROM (
					SELECT contype, CASE WHEN contype='f' THEN
							pg_catalog.pg_get_constraintdef(oid)
						ELSE
							'CHECK (' || consrc || ')'
						END AS consrc,
						conrelid AS relid,
						NULL AS indkey
					FROM pg_catalog.pg_constraint
					WHERE contype IN ('f', 'c')
					UNION ALL
					SELECT
						CASE WHEN indisprimary THEN
								'p'
						ELSE
								'u'
						END, null,  pi.indrelid, indkey
					FROM pg_catalog.pg_class pc, pg_catalog.pg_index pi
					WHERE pc.oid=pi.indexrelid
						AND EXISTS (
							SELECT 1 FROM pg_catalog.pg_depend d JOIN pg_catalog.pg_constraint c
							ON (d.refclassid = c.tableoid AND d.refobjid = c.oid)
							WHERE d.classid = pc.tableoid AND d.objid = pc.oid AND d.deptype = 'i' AND c.contype IN ('u', 'p')
					)
				) AS sub
				WHERE relid = (SELECT oid FROM pg_catalog.pg_class WHERE relname = :table
					AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace
					WHERE nspname = 'public'))";
		$sth = $PDOConnection->prepare($sql);
		$sth->bindValue(":table", $tableName, PDO::PARAM_STR);
		$sth->execute();

		return $sth->fetchAll();
	}
}
