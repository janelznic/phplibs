<?php
/**
 * @name pagegen
 * @version 1.5
 * @description Generátor jednoduchých PHP šablon
 * @depends dbglog (>= 1.0)
 * @branch testing
**/
@include_once("src/dict/templdict.php");

/**
 * Constructor
 * @param {string} filename Název souboru, ze kterého budeme šablonou generovat
 * @param {array} data Výstupní data do šablon
 * @param {array} config Objekt s konfigurací
 * @param {array} config {string} templPath Cesta k souborům s šablonama (výchozí hodnota "templ/")
 * @param {array} config {string} configPath Cesta k souborům s konfiguračníma souborama (výchozí hodnota "config/")
 * @param {array} config {string} file Název souboru s šablonou
 * @param {array} config {string} content_type Content type souboru s šablonou (výchozí hodnota "text/html")
 * @param {array} config {string} dict Soubor se slovníkem (výchozí hodnota "dictionary.conf")
 * @param {array} config {string} config Soubor k obecným konfiguracím šablon (výchozí hodnota "templates.conf")
 * @param {array} config {string} debug Přepínač pro debug mód
 **/
class Pagegen
{
	function Pagegen($filename, $data, $conf = array()) {
		# Převedeme proměnné z pole do stromu
		if (is_array($data)) {
			foreach ($data as $index => $value) {
				$$index = $value;
			}
		} else {
			Dbg::log("Error: Data structure for template is not an array");
		}

		# Výchozí volby konfigurace
		if (!isset($conf["content_type"])) $conf["content_type"] = "text/html";
		if (!isset($conf["dict"])) $conf["dict"] = "dictionary.dict";
		if (!isset($conf["config"])) $conf["config"] = "templates.conf";
		if (!isset($conf["templPath"])) $conf["templPath"] = "templ/";
		if (!isset($conf["configPath"])) $conf["configPath"] = "config/";

		# Načteme konfiguraci pro šablony
		include($conf["configPath"].$conf["dict"]);
		include($conf["configPath"].$conf["config"]);
		@include("src/vars.php");

		# Určíme content type pro šablonu
		if (!isset($conf["debug"])) {
			header(sprintf("Content-type: %s", $conf["content_type"]));
		}

		# Použijeme soubor s šablonou
		$debug["templateSource"] = $filename;
		$debug["content_type"] = $conf["content_type"];

		if (!isset($conf["debug"])) {
			$templFile = $conf["templPath"].$filename;
		} else {
			$templFile = $conf["templPath"]."debug.html";
		}

		if (file_exists($templFile)) {
			require_once($templFile);
		} else {
			Dbg::log("Error: Template file ".$templFile." does not exist");
		}
		exit;
	}
}
?>
