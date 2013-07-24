<?php
/**
 * @name dbglog
 * @version 1.0.4-1
 * Debugovací nástroj
 */
class Dbg
{
	/**
	 * Vytiskne řetězec na obrazovku
	 * @param string $var Řetězec
	 * @param boolean $p Budeme používat formátování pro odsazení?
	 */
	public static function write($var, $p) {
		if ($p == 0) echo "<pre>";
		print_r($var);
		if ($p == 0) echo "</pre>";
	}

	/**
	 * Zaloguje řetězec do debug logu v souboru
	 * @param string $var Řetězec
	 */
	public static function log($var, $des = false, $lines = 0) {
		$output = "";
		if ($lines) { foreach ($lines as $line) $output .= "\n"; }
		$output .= date("[d.m.Y - H:i:s] ");
		if ($des) $output .= $des.":"."\n";
		$output .= print_r($var, true);

		$file = fopen("log/debug.log", "a");
		//if (gettype($var == "boolean")) $var = ($var == 1 ? "true" : "false");
		fwrite($file, ($output."\n"));
		fclose($file);
	}
}
?>
