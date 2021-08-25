<?
	ini_set('display_errors', 1);

	const THIS_DIR = __DIR__;

	// start the session
	if (session_status() === PHP_SESSION_NONE)
		session_start();

	// load functions
	require_once THIS_DIR.'/src/php/functions.php';

	// load custom functions
	if (file_exists(THIS_DIR.'/src/php/functions_custom.php'))
		require_once THIS_DIR.'/src/php/functions_custom.php';

	// load config
	load_config();

	// directories not set
	if (!DIR_PROD || !DIR_STAG)
		die('Please set directories to compare.');

	// load globals
	require_once THIS_DIR.'/globals.php';

	// run install
	if (file_exists(THIS_DIR.'/install/install.php'))
		require_once THIS_DIR.'/install/install.php';

	// start csrf
	init_csrf();

	// check submodules
	if (!file_exists(__DIR__.'/lib/result/result.class.php') || !file_exists(__DIR__.'/lib/changes/changes.class.php'))
		push_alert('One of more submodules are not present. Please view the <a href="'.THIS_URL_DIR.'/README.md" target="_blank">README</a> for download instructions.', 'Submodules Missing', 'error', false, true);

	// load the rest
	require_once THIS_DIR.'/process.php';
	require_once THIS_DIR.'/listing.php';