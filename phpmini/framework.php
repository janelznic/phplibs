<?php
/**
 * @name phpfw
 * @version 1.0.3-9
 * @description Miniaturní PHP framework
 * @branch unstable
 */
class FW
{
	/**
	 * Vyčistí řetězec od HTML znaků (převede na entity)
	 * @param {string} val řetězec
	 */
	public static function clean($val) {
		return htmlspecialchars($val, ENT_QUOTES);
	}

	/**
	 * Načte parametr z URL a vrátí jeho hodnotu
	 * @param {string} val řetězec
	 */
	public static function get($val) {
		if (!isset($_GET[$val])) $_GET[$val]="";
		$val = $_GET[$val];
		return FW::clean($val, ENT_QUOTES);
	}

	/**
	 * Načte proměnnou poslanou HTTP metodou POST a vrátí její hodnotu
	 * @param {string} val řetězec
	 */
	public static function post($val) {
		if (!isset($_POST[$val])) $_POST[$val]="";
		$val = $_POST[$val];
		return FW::clean($val);
	}

	/**
	 * Načte proměnnou poslanou HTTP metodou POST obsahující HTML kód a vrátí její hodnotu
	 * @param {string} val řetězec
	 */
	public static function postHTML($val) {
		if (!isset($_POST[$val])) $_POST[$val]="";
		$val = $_POST[$val];
		$val = str_replace("\r", "", $val);
		$val = str_replace("\t", "", $val);
		return str_replace("\n", "", $val);
	}

	/**
	 * Inicializuje číslo, případně přetypuje proměnnou na číslo
	 * @param {string} val řetězec
	 */
	public static function num($val) {
		$val = sprintf("%d", $val);
		return (int) $val;
	}

	/**
	 * Převede hromadně hodnoty vybraných klíčů pole
	 * @param {array} array Pole, ze kterého budeme hodnoty převádět
	 * @param {string} type Na jaký datový typ hodnoty převedeme (int|float|bool|array|obj|str|bin|null)
	 * @param {array} keys Vybrané klíče, jejichž hodnotu z proměnné $array budeme převádět
	 */
	public static function bulkCast($array, $type, $keys) {
		foreach ($array as $key => $value) {
			if (in_array($key, $keys)) {
				switch ($type) {
					case "int":
						$array[$key] = (integer) $value;
						break;
					case "float":
						$array[$key] = (float) $value;
						break;
					case "bool":
						$array[$key] = (boolean) $value;
						break;
					case "array":
						$array[$key] = (array) $value;
						break;
					case "obj":
						$array[$key] = (object) $value;
						break;
					case "str":
						$array[$key] = (string) $value;
						break;
					case "bin":
						$array[$key] = (binary) $value;
						$array[$key] = "b\"".$value;
						break;
					case "null":
						$array[$key] = (unset) $value;
						break;
				}
			}
		}
		return $array;
	}

	/**
	 * Zvaliduje data poslaná HTTP metodou POST, vrátí pole pouze povolenými klíči
	 * @param {array} allowed Pole s povolenými klíči
	 */
	public static function validPostData($allowed) {
		$postData = array();
		foreach ($_POST as $key => $value) {
			if (in_array($key, $allowed)) {
				$postData[$key] = $value;
			}
		}
		return $postData;
	}

	/**
	 * Prohledá seznam klíčů zaslaných formulářem POST metodou
	 * a vrátí ty vybrané s hodnotou true
	 * @param {array} keys 
	 */
	public static function searchTrueInPostByKeys($keys) {
		$validKeys = array();
		foreach ($_POST as $key => $value) {
			if (in_array($key, $keys) && $value == true) {
				array_push($validKeys, $key);
			}
		}

		return $validKeys;
	}

	/**
	 * Vrátí klíče s hodnotou true z pole předaném jako argument
	 * @param {array} allowed Pole, které budeme prohledávat
	 */
	public static function searchTrueInArray($array) {
		$validKeys = array();
		foreach ($array as $key => $value) {
			if ($value == true) {
				array_push($validKeys, $key);
			}
		}
		return $validKeys;
	}

	/**
	 * Zvaliduje dvě pole, vrátí pole $b podle existujících hodnot v poli $a
	 * @param {array} a Pole s povolenými klíči
	 * @param {array} b Pole které budeme osekávat o zakázané hodnoty
	 */
	public static function validArrays($a, $b) {
		$output = array();
		foreach ($b as $key => $value) {
			if (in_array($key, $a)) {
				$output[$key] = $value;
			}
		}
		return $output;
	}

	/**
	 * Setřídí pole abecedně vzestupně podle klíče
	 * @param {string} key Název klíče
	 * @param {boolean} desc Sestupné řazení
	 */
	public static function arrByKey($key, $desc) {
		return function ($a, $b) use ($key, $desc) {
			if ($a[$key] == $b[$key]) return 0;
			if ($desc) {
				# Vzestupně
				return (($a[$key] > $b[$key])?-1:1);
			} else {
				# Sestupně
				return (($a[$key] < $b[$key])?-1:1);
			}
		};
	}

	/**
	 * Vrátí string platformy
	 */
	public static function getPlatform() {
		$device = "";
		$httpua = $_SERVER["HTTP_USER_AGENT"];

		if (stristr($httpua,"ipad") ) {
			$device = "ipad";
		} else if (stristr($httpua, "iphone") || strstr($httpua, "iphone") ) {
			$device = "iphone";
		} else if (stristr($httpua, "blackberry")) {
			$device = "blackberry";
		} else if (stristr($httpua, "android")) {
			$device = "android";
		} else {
			$device = "unknown";
		}

		if ($device) {
			return $device;
		} else {
			return false;
		}
	}

	/**
	 * Zjistí, zda-li je předané pole asociativní či nikoliv
	 */
	public static function isAssoc($arr) {
		return array_keys($arr) !== range(0, count($arr) - 1);
	}


	/**
	 * Vrátí hodnotu cookie s daným klíčem v surovém tvaru
	 */
	public static function getRawCookie($key) {
		if ($_SERVER["HTTP_COOKIE"] == null) return false;

		$cookie = array();
		foreach (explode("; ", $_SERVER["HTTP_COOKIE"]) as $rawCookieStr) {
			$rc = explode("=" , $rawCookieStr, 2);
			$cookie[$rc[0]] = $rc[1];
		}
		return (isset($cookie[$key])) ? $cookie[$key] : false;
	}

	/**
	 * Posílá statusy do HTTP hlaviček
	 */
	public function httpStatus($code) {
		switch ($code) {
			case 301:
				$status = "Moved Permanently";
				break;
			case 206:
				$status = "Partial Content";
			case 302:
				$status = "Found";
				break;
			case 401:
				$status = "Unauthorized";
				break;
			case 403:
				$status = "Forbidden";
				break;
			case 404:
				$status = "Not Found";
				break;
			case 500:
				$status = "Internal Server Error";
				break;
			case 503:
				$status = "Service Unavailable";
				break;
			default:
				$status = "OK";
		}
		header(sprintf("HTTP/1.1 %d %s", $code, $status));
	}

	/**
	 * Vrati aktualni url
	 * @return string Url
	 */
	public function currentUrl($path = true) {
		$protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? "https" : "http";
		$host = $_SERVER["SERVER_NAME"];
		$port = $_SERVER["SERVER_PORT"];

		if (($protocol == "http" && $port == "80") || ($protocol == "https" && $port == "443")) {
			$port = "";
		} else {
			$port = ":" + $port;
		}

		if ($path) {
			$path = preg_replace("/\?.*/", "", $_SERVER["REQUEST_URI"]);
		} else {
			$path = "";
		}

		return $protocol."://".$host.$port.$path;   
	}
}
?>
