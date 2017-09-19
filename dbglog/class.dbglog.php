<?php
/**
 * @name dbglog
 * @version 1.1
 * Simple log tool for PHP debug
 * Your Apache2 config must contain this row in sections for logs e.g.:
 * SetEnv DEBUG_LOG_FILE __PATH__/log/debug.log
 */
class Dbg
{
	/**
	 * Write string to document
	 * @param string $var String
	 * @param boolean $p Format for indent?
	 */
	public static function write($var, $p) {
		if ($p == 0) echo "<pre>";
		print_r($var);
		if ($p == 0) echo "</pre>";
	}

	/**
	 * Log string to file
	 * @param string $var String
	 * @param boolean $des Date view?
	 * @param integer $lines Indent for new messages
	 */
	public static function log($var, $des = false, $lines = 0) {
		$output = "";
		if ($lines) { foreach ($lines as $line) $output .= "\n"; }
		$output .= date("[d.m.Y - H:i:s] ");
		if ($des) $output .= $des.":"."\n";
		$output .= print_r($var, true);

		$file = fopen(apache_getenv('DEBUG_LOG_FILE'), "a");
		fwrite($file, ($output."\n"));
		fclose($file);
	}
}
?>
