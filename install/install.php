<?
	if (!file_exists(THIS_DIR.'/DEVMACHINE')) {

		$tables = array();

		require_once __DIR__.'/_config.php';
		require_once __DIR__.'/functions.php';
		require_once dirname(__DIR__).'/src/php/classes/directory.class.php';
		require_once dirname(__DIR__).'/src/php/classes/deployment.class.php';

		install_the_tables($conn, $tables);

		if (!file_exists(THIS_DIR.'/DEVMACHINE'))
			\directory\deployment::rrmdir(dirname(__DIR__).'/install');
	}