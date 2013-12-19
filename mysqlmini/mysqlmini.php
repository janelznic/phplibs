<?php
/**
 * @name mysqlmini
 * @version 1.0.6
 * @description Framework pro práci s MySQL databází
 * @depends phpmini (>= 1.0)
 * @branch unstable
 **/
class MySQL
{
	/**
	 * Aktualizuje data v řádku na základě předaných informací
	 * @param {string} tabName Jméno tabulky
	 * @param {string|array} colName Název sloupce nebo pole sloupců, který/é budeme hledat
	 * @param {string|boolean} valueBy Hodnota ve sloupci, kterou budeme hledat; pokud hledáme více hodnot, tak false
	 * @param {array} data Nová data pro uložení
	 **/
	function updateRow($tabName, $colName, $valueBy, $data) {
		$tabName = FW::mres($tabName);

		$settings = "";
		$i=0;
		$colsCount = count($data)-1;
		foreach ($data as $key => $value) {
			$settings .= sprintf("%s = '%s'", FW::mres($key), FW::mres($value));
			if ($i != $colsCount) {
				$settings .= ", ";
			}
			$i++;
		}

		if (is_array($colName)) {
			$where = "";
			$j=0;
			$whereCount = count($colName)-1;
			foreach ($colName as $key => $value) {
				$where .= sprintf("%s like '%s'", FW::mres($key), FW::mres($value));
				if ($j != $whereCount) {
					$where .= " and ";
				}
				$j++;
			}
		} else {
			$colName = FW::mres($colName);
			$valueBy = FW::mres($valueBy);
			$where = sprintf("%s like %s", $colName, $valueBy);
		}

		$request = mysql_query(
			$sql = sprintf(
				"update %s_%s set %s where %s",
				Config::get("mysqlPrefix"), $tabName, $settings, $where
			)
		);

		//Dbg::log($sql);

		if ($request) {
			return true;
		} else {
			Dbg::log("Error: Cannot update this row");
			return false;
		}
	}

	/**
	 * Načte řádek z DB
	 * @param {string} tabName Jméno tabulky
	 * @param {string} colName Název sloupce
	 * @param {string} value Hodnota ve sloupci, kterou budeme hledat
	 * @param {array} order Řazení sloupců (dle posloupnosti), může být false - nepovinné
	 **/
	public static function selectRow($tabName, $colName, $value, $order = false) {
		if (!MySQL::orderByControl($order)) return false;

		# Řazení podle sloupců
		$sort = MySQL::makeSorting($order);

		$tabName = FW::mres($tabName);
		$colName = FW::mres($colName);
		$value = FW::mres($value);
		list($count) = mysql_fetch_row(mysql_query(
			sprintf(
				"select count(%s) from %s_%s where %s like '%s'",
				$colName, Config::get("mysqlPrefix"), $tabName, $colName, $value
			)
		));

		if ($count) {
			$request = mysql_query(
				$sql = sprintf(
					"select * from %s_%s where %s like '%s'%s",
					Config::get("mysqlPrefix"), $tabName, $colName, $value, $sort
				)
			);

			//Dbg::log($sql);

			if ($request) {
				$colsData = mysql_fetch_array($request);

				$i = 0;
				foreach ($colsData as $key => $value) {
					if ($key == $i) {
						$i++;
					} else {
						$output[$key] = $value;
					}
				}
				return $output;
			} else {
				Dbg::log("Error: Cannot select this row");
				return false;
			}
		}
	}

	/**
	 * Vrátí počet záznamů odpovídajcích dané hodnotě
	 * @param {string} tabName Jméno tabulky
	 * @param {string|array} colName Název sloupce nebo sloupců, podle kterých budeme hledat
	 * @param {string|boolean} value Hodnota ve sloupci, kterou budeme hledat; v případě více hodnot bude false
	 **/
	public static function countRows($tabName, $colName, $value) {
		$tabName = FW::mres($tabName);

		if (is_array($colName)) {
			$where = "";
			$j=0;
			$whereCount = count($colName)-1;
			foreach ($colName as $key => $value) {
				if (!$j) $countCol = $key;
				$where .= sprintf("%s like '%s'", FW::mres($key), FW::mres($value));
				if ($j != $whereCount) {
					$where .= " and ";
				}
				$j++;
			}
		} else {
			$colName = FW::mres($colName);
			$value = FW::mres($value);
			$where = sprintf("%s like %s", $colName, $value);
		}

		list($count) = mysql_fetch_row($query = mysql_query(
			$sql = sprintf(
				"select count(%s) from %s_%s where %s",
				(is_array($colName) ? $countCol : $colName), Config::get("mysqlPrefix"), $tabName, $where
			)
		));

		Dbg::log($sql);

		if ($query) {
			return $count;
		} else {
			Dbg::log("Error: Cannot select rows count");
			return false;
		}
	}

	/**
	 * Smaže řádek z DB
	 * @param {string} tabName Jméno tabulky
	 * @param {string} colName Název sloupce, podle kterého budeme mazat
	 * @param {string} value Hodnota ve sloupci, kterou budeme hledat
	 **/
	public static function deleteRow($tabName, $colName, $value) {
		$tabName = FW::mres($tabName);
		$colName = FW::mres($colName);
		$value = FW::mres($value);

		$request = mysql_query(
			$sql = sprintf(
				"delete from %s_%s where %s like '%s'",
				Config::get("mysqlPrefix"),
				$tabName,
				$colName,
				$value
			)
		);

		//Dbg::log($sql);

		if ($request) {
			return true;
		} else {
			Dbg::log("Error: Cannot delete row");
			return false;
		}
	}

	/**
	 * Vloží záznam do vybrané tabulky předáním pole hodnot
	 * @param {string} tabName Jméno tabulky
	 * @param {array} colsData Pole, kde klíč i hodnota mohou být string
	 **/
	public static function insertRow($tabName, $colsData) {
		if (!is_array($colsData)) {
			Dbg::log("Error: Argument colsData must be an array");
			return false;
		}

		$tabName = FW::mres($tabName);

		$colNames = "";
		$values = "";
		$i = 0;
		$colsCount = count($colsData)-1;
		foreach ($colsData as $key => $value) {
			$colNames .= FW::mres($key);
			$values .= sprintf("'%s'", FW::mres($value));
			if ($i != $colsCount) {
				$colNames .= ", ";
				$values .= ", ";
			}
			$i++;
		}

		$request = mysql_query(
			$sql = sprintf(
				"insert into %s_%s (%s) values (%s)",
				Config::get("mysqlPrefix"), $tabName, $colNames, $values
			)
		);

		//Dbg::log($sql);

		if ($request) {
			return true;
		} else {
			Dbg::log(sprintf("Error: Cannot insert new row to table %s", $tabName));
			return false;
		}
	}

	/**
	 * Zkontroluje, zda-li je posloupnost řazení předávána jako pole
	 * @param {array} order Pole s posloupností řazení
	 **/
	public static function orderByControl($order) {
		if ($order && !is_array($order)) {
			Dbg::log("Error: Argument order must be an array");
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Shromáždí podmínky pro řazení posloupnosti z pole a vyrobí z něj řetězec pro SQL dotaz
	 * @param {array} order Pole s posloupností řazení
	 **/
	public static function makeSorting($order) {
		if ($order) {
			$sort = " order by ";
			$sortCount = count($order);
			for ($k=0; $k < $sortCount; $k++) {
				$sort .= FW::mres($order[$k]);
				if ($k != $sortCount-1) {
					$sort .= ", ";
				}
			}
			return $sort;
		} else {
			return false;
		}
	}

	/**
	 * Porovnávání pro dotazy v MySQL
	 * @param {string} grader Porovnávací operátor
	 **/
	public static function getComparsion($grader) {
		switch ($grader) {
			case "<":
			case ">":
			case "<=":
			case ">=":
			case "=":
			case "like":
				return $grader;
				break;
		}
		return false;
	}

	/**
	 * Vrátí pole se seznamem řádků z tabulky na základě daných kritérií pro SQL dotaz
	 * @param {array} cols Seznam sloupců (lze použít i tvary count(nazevSloupce) apod.) - povinné
	 * @param {string} table Název tabulky - povinné
	 * @param {array} where Podmínky, může být false - nepovinné (nebo vložená pole, kde první prvek v poli značí porovnávač a druhý hodnotu, se kterou porovnáváme)
	 * @param {array} order Řazení sloupců (dle posloupnosti), může být false - nepovinné
	 * @param {float} limit Limit počtu zobrazení
	 **/
	public static function getList($cols, $table, $where = false, $order = false, $limit = false) {
		if (!is_array($cols)) {
			Dbg::log("Error: Argument cols must be an array");
			return false;
		}

		if (!MySQL::orderByControl($order)) return false;

		# Řazení podle sloupců
		$sort = MySQL::makeSorting($order);

		# Sloupce, které budeme chtít načíst
		$colNames = "";
		$colsCount = count($cols);
		for ($j=0; $j < $colsCount; $j++) {
			$colNames .= FW::mres($cols[$j]);
			if ($j != $colsCount-1) {
				$colNames .= ", ";
			}
		}

		# Pole s podmínkama (where)
		$conCount = count($where);
		if ($conCount == 0 || !$where) {
			$conditions = "";
		} else {
			$conditions = " where ";
			$l = 0;
			foreach ($where as $key => $value) {
				if (is_array($value)) {
					if (is_array($value[0])) {
						$countVal = count($value);
						for ($m=0; $countVal > $m; $m++) {
							$grader = MySQL::getComparsion($value[$m][0]);
							$addVal = FW::mres($value[$m][1]);
							if ($grader) {
								$conditions .= FW::mres($key)." ". $grader ." '".$addVal."'";
								if ($m != $countVal-1) $conditions .= " and ";
							}
						}
					} else {
						$grader = MySQL::getComparsion($value[0]);
						$addVal = FW::mres($value[1]);
						if ($grader) $conditions .= FW::mres($key)." ". $grader ." '".$addVal."'";
					}
				} else {
					$conditions .= FW::mres($key)." like '".FW::mres($value)."'";
				}
				if ($l != $conCount-1) {
					$conditions .= " and ";
				}
				$l++;
			}
		}

		# Limit počtu zobrazení
		if ($limit) {
			$limit = sprintf(" limit %d", $limit);
		}

		$request = mysql_query($sql = sprintf("select %s from %s_%s%s%s%s",
			$colNames, Config::get("mysqlPrefix"), $table, $conditions, $sort, $limit)
		);

		//Dbg::log($sql);

		if (!$request) {
			Dbg::log(sprintf("Error: Cannot select data from %s", $table));
			return false;
		}

		$data = array();
		$i = 0;
		while($arr = mysql_fetch_assoc($request)) {
			foreach($arr as $key => $value) {
				$data[$i][$key] = $value;
			}
			$i++;
		}

		return $data;
	}
}
?>
