<?
	ini_set('display_errors', 1);

	if (!defined('BASE_DIR'))
		define('BASE_DIR', dirname(__DIR__));

	require_once __DIR__.'/_config.php';
	require_once __DIR__.'/src/php/extra.php';
	require_once __DIR__.'/src/php/functions.php';
	require_once __DIR__.'/src/php/directory.class.php';
	require_once __DIR__.'/src/php/deployment.class.php';
	require_once __DIR__.'/globals.php';
	require_once dirname(THIS_DIR).'/_src/php/result.class.php';
	require_once dirname(THIS_DIR).'/_src/php/changes.class.php';

	$files_to_exclude   = \directory\deployment::get_ignored_files($conn, null, null, false);
	$dir_stag           = new directory\directory(PATH_STAG, $files_to_exclude);
	$dir_prod           = new directory\directory(PATH_PROD, $files_to_exclude);

	if ($_GET['act'] == 'get_listing_files') {

		if (!($from = $_GET['from']))
			die('<b>From</b> parameter was not specified');

		if ($from == 'stag') {
			$dir_from   = $dir_stag;
			$dir_to     = $dir_prod;
			$header     = 'Files on Staging';
			$position   = 'Left';
		} else {
			$dir_from   = $dir_prod;
			$dir_to     = $dir_stag;
			$header     = 'Files on Production';
			$position   = 'Right';
		}

		$all_files = directory\directory::combine_directories($dir_stag, $dir_prod);

		listing($dir_from, $dir_to, $all_files, $header, $from, $position);
	}

	if ($_GET['act'] == 'get_ignored_files') {
		$ignored_files = \directory\deployment::get_ignored_files($conn, PATH_STAG, PATH_PROD);

		listing_ignored($conn, $ignored_files);
	}

	if ($_GET['act'] == 'get_pushed_files') {

		$page = isset($_GET['pag']) && $_GET['pag'] ? $_GET['pag'] : 1;

	    listing_pushed($conn, $page);
	}

	// push

	if ($_GET['act'] == 'push') {

		$result = new result;

		if ($file = $_GET['file']) {

			if ($from = $_GET['from']) {

				if ($from == 'stag') {
					$dir_from   = $dir_stag;
					$dir_to     = $dir_prod;
				} else {
					$dir_from   = $dir_prod;
					$dir_to     = $dir_stag;
				}

				if ($backup_file = $dir_to->get_file($file))
					\directory\deployment::backup_file($backup_file->get_full_path());

				$result = \directory\deployment::push_file($conn, $file, $dir_from, $dir_to, $from);
			} else {
				$result
					->set_success(false)
					->set_msg('From parameter not specified.')
					->set_data('title', 'Error')
					->set_data('type', 'error');
			}
		} else {
			$result
				->set_success(false)
				->set_msg('File not specified.')
				->set_data('title', 'Error')
				->set_data('type', 'error');
		}

		$result->echo_json();
	}

	// delete

	if ($_GET['act'] == 'delete') {

		$result = new result;

		if ($file = $_GET['file']) {

			if ($from = $_GET['from']) {

				if ($from == 'stag')
					$dir = $dir_stag;
				else
					$dir = $dir_prod;

				\directory\deployment::backup_file($dir->get_file($file)->get_full_path());

				$result = \directory\deployment::delete_file($conn, $file, $dir, $from);
			} else {
				$result
					->set_success(false)
					->set_msg('From parameter not specified.')
					->set_data('title', 'Error')
					->set_data('type', 'error');
			}
		} else {
			$result
				->set_success(false)
				->set_msg('File not specified.')
				->set_data('title', 'Error')
				->set_data('type', 'error');
		}

		$result->echo_json();
	}

	// ignore

	if ($_GET['act'] == 'ignore') {

		$result = new result;

		if ($file = $_GET['file']) {
			$result = \directory\deployment::ignore_file($conn, $file, $dir_stag, $dir_prod);
		} else {
			$result
				->set_success(false)
				->set_msg('File not specified.')
				->set_data('title', 'Error')
				->set_data('type', 'error');
		}

		$result->echo_json();
	}

	// unignore

	if ($_GET['act'] == 'unignore') {

		$result = new result;

		if ($file = $_GET['file']) {
			$result = \directory\deployment::unignore_file($conn, $file);
		} else {
			$result
				->set_success(false)
				->set_msg('File not specified.')
				->set_data('title', 'Error')
				->set_data('type', 'error');
		}

		$result->echo_json();
	}