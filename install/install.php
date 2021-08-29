<?
	namespace directory_comparison;

	if (!file_exists(THIS_DIR.'/DEVMACHINE')) {

		$tables = array();

		require_once __DIR__.'/_config.php';
		require_once __DIR__.'/functions.php';
		require_once THIS_DIR.'/src/php/classes/directory.class.php';
		require_once THIS_DIR.'/src/php/classes/deployment.class.php';

		// install the tables
		install_the_tables($conn, $tables);

		// install submodules
		if (!install_the_submodules())
			push_alert('Could not install one or more submodules.', 'Submodule Install Error', 'error', false, true);

		// delete install directory
		deployment::rrmdir(dirname(__DIR__).'/install');
	}