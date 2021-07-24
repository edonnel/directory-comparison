<?
	if (!file_exists(THIS_DIR.'/DEVMACHINE')) {

		$tables = array();

		require_once __DIR__.'/_config.php';
		require_once __DIR__.'/functions.php';

		install_the_tables($conn, $tables);

		if (!file_exists(THIS_DIR.'/DEVMACHINE'))
			rmdir(dirname(__DIR__).'/install');	// TODO RECURSIVE DELETE
	}