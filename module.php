<?
	ini_set('display_errors', 1);

	require_once __DIR__.'/_config.php';

	if (!DIR_PROD || !DIR_STAG)
		die('Please set directories to compare.');

	require_once __DIR__.'/functions.php';
	require_once __DIR__.'/_globals.php';

	require_once THIS_DIR.'/process.php';
	require_once THIS_DIR.'/listing.php';

	echo '<script>';
	require_once THIS_DIR.'/src/js/javascript.js.php';
	echo '</script>';