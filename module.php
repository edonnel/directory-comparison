<?
	ini_set('display_errors', 1);

	// start the session
	if (session_status() === PHP_SESSION_NONE)
		session_start();

	// load config
	if (file_exists(__DIR__.'/_config_custom.php'))
		require_once __DIR__.'/_config_custom.php';
	else
		require_once __DIR__.'/_config.php';

	// load custom functions
	if (file_exists(__DIR__.'/src/php/functions_custom.php'))
		require_once __DIR__.'/src/php/functions_custom.php';

	// directories not set
	if (!DIR_PROD || !DIR_STAG)
		die('Please set directories to compare.');

	// load src files
	require_once __DIR__.'/src/php/functions.php';
	require_once __DIR__.'/globals.php';

	// run install
	if (file_exists(__DIR__.'/install/install.php'))
		require_once __DIR__.'/install/install.php';

	// start csrf
	init_csrf();

	// load the rest
	require_once THIS_DIR.'/process.php';
	require_once THIS_DIR.'/listing.php';