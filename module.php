<?
	ini_set('display_errors', 1);

	if (session_status() === PHP_SESSION_NONE)
		session_start();

	if (file_exists(__DIR__.'/DEVMACHINE')) {
		require_once __DIR__.'/_dev/_config.php';
		require_once __DIR__.'/_dev/functions.php';
	} else
		require_once __DIR__.'/_config.php';

	if (!DIR_PROD || !DIR_STAG)
		die('Please set directories to compare.');

	require_once __DIR__.'/src/php/extra.php';
	require_once __DIR__.'/src/php/functions.php';
	require_once __DIR__.'/globals.php';

	if (file_exists(__DIR__.'/install/install.php'))
		require_once __DIR__.'/install/install.php';

	init_csrf();

	require_once THIS_DIR.'/process.php';
	require_once THIS_DIR.'/listing.php';