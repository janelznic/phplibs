<?php
/**
 * @name pagegen
 * @version 1.6
 * @description Simple PHP template generator
 * @depends dbglog (>= 1.0)
 * @branch testing
**/

# Absolute path for file with template functions (optional)
if (apache_getenv('TEMPL_FUNC_FILE')) {
	@include_once(require_once(apache_getenv('TEMPL_FUNC_FILE')));
}

/**
 * Constructor
 * @param {string} filename Path to template file (*.html, *.json etc.)
 * @param {array} data Input data
 * @param {array} config Configuration object
 * @param {array} config {string} templPath Templates directory path (default: "templ/")
 * @param {array} config {string} configPath Config files directory path (default: "config/")
 * @param {array} config {string} content_type Content type (default: "text/html")
 * @param {array} config {string} dict Dictionary file (default: "dictionary.conf")
 * @param {array} config {string} config Template config (default: "templates.conf")
 * @param {array} config {boolean} debug Debug mode
 **/
class Pagegen
{
	function Pagegen($filename, $data, $conf = array()) {
		# Generate data template structure
		if (is_array($data)) {
			foreach ($data as $index => $value) {
				$$index = $value; // Two dollars variable set
			}
		} else {
			Dbg::log("Error: Data structure for template is not an array");
		}

		# Default configuration options
		if (!isset($conf["content_type"])) $conf["content_type"] = "text/html";
		if (!isset($conf["dict"])) $conf["dict"] = "dictionary.dict";
		if (!isset($conf["config"])) $conf["config"] = "templates.conf";
		if (!isset($conf["templPath"])) $conf["templPath"] = "templ/";
		if (!isset($conf["configPath"])) $conf["configPath"] = "config/";

		# Templates configuration
		include($conf["configPath"].$conf["dict"]);
		include($conf["configPath"].$conf["config"]);

		# Template content type
		if (!isset($conf["debug"])) {
			header(sprintf("Content-type: %s", $conf["content_type"]));
		}

		# Get template file
		$debug["templateSource"] = $filename;
		$debug["content_type"] = $conf["content_type"];

		if (!isset($conf["debug"])) {
			$templateFile = $conf["templPath"].$filename;
		} else {
			$templateFile = $conf["templPath"]."debug.html";
		}

		# Generate template
		if (file_exists($templateFile)) {
			require_once($templateFile);
		} else {
			Dbg::log("Error: Template file ".$templateFile." does not exist");
		}
		exit;
	}
}
?>
