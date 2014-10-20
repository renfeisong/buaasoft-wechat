<?php
/**
 * These functions are needed to load the system.
 *
 * @author Renfei Song
 * @since 2.0.0
 */

/**
 * Start the micro-timer.
 *
 * @global float $time_start Unix timestamp set at the beginning of the page load.
 * @see timer_stop()
 */
function timer_start() {
	global $time_start;
	$time_start = microtime(true);
}

/**
 * Retrieve the time from the page start to when function is called.
 *
 * @global float $timestart Seconds from when timer_start() is called.
 * @global float $timeend   Seconds from when function is called.
 *
 * @param int $precision The number of digits from the right of the decimal to retrieve. Default 3.
 *
 * @return string The "second.microsecond" finished time calculation. The number is formatted or human consumption,
 * both localized and rounded.
 */
function timer_stop($precision = 3) {
	global $time_start, $time_end;
	$time_end = microtime(true);
	$time_total = $time_end - $time_start;
	$r = (function_exists( 'number_format_i18n')) ? number_format_i18n($time_total, $precision) : number_format($time_total, $precision);
	return $r;
}

/**
 * Load the database class file and instantiate the `$wxdb` global.
 *
 * @global wxdb $wxdb The database class.
 */
function require_db() {
	global $wxdb;
	require_once('wxdb.php');
	$wxdb = new wxdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
}

/**
 * Retrieve an array of module paths and names.
 *
 * @return array Array of exist modules.
 */
function get_modules() {
	$module_list = array();
	$path = ABSPATH . "/modules";
	$idx = 0;
	foreach (new DirectoryIterator($path) as $fileInfo) {
		if ($fileInfo->isDot() == false && $fileInfo->isDir()) {
			$module_name = $fileInfo->getFilename();
			$module_path = ABSPATH . "/modules/" . $module_name . "/index.php";
			if (file_exists($module_path)) {
				$module_list[$idx]["path"] = $module_path;
				$module_list[$idx]["name"] = $module_name;
				$idx++;
			}
		}
	}

	return $module_list;
}

/**
 * Load all valid modules from given list.
 *
 * @see get_modules()
 * @param $module_list Array of module paths and names.
 */
function load_modules($module_list) {
	global $modules;
	usort($modules, 'cmp');
	foreach ($module_list as $module) {
		require_once $module['path'];
		$m = new $module['name'];
		if (is_subclass_of($m, 'BaseModule')) {
			$modules[] = $m;
		}
	}
}

/**
 * Compare function to sort BaseModules to descending order of priorities.
 *
 * @param BaseModule $a First operator.
 * @param BaseModule $b Second operator.
 *
 * @return int Comparison result.
 */
function cmp(BaseModule $a, BaseModule $b) {
	if ($a->priority() == $b->priority())
		return 0;
	return ($a->priority() < $b->priority()) ? 1 : -1;
}