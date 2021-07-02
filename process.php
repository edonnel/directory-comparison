<?
	if (session_status() === PHP_SESSION_NONE)
		@session_start();

	$valid_acts = array(
		'push_all',
		'sync_staging',
		'push_all_newer',
		'push',
		'delete',
		'ignore',
		'unignore',
	);

	if (isset($_GET['act']) && in_array($_GET['act'], $valid_acts)) {

		require_once __DIR__.'/src/php/directory.class.php';
		require_once __DIR__.'/src/php/deployment.class.php';
		require_once dirname(THIS_DIR).'/_src/php/result.class.php';
		require_once dirname(THIS_DIR).'/_src/php/changes.class.php';

		$files_to_exclude = \directory\deployment::get_ignored_files($conn, null, null, false);

		$dir_stag = new directory\directory(PATH_STAG, $files_to_exclude);
		$dir_prod = new directory\directory(PATH_PROD, $files_to_exclude);

		$all_files = directory\directory::combine_directories($dir_stag, $dir_prod);

		// push all

		if ($_GET['act'] == 'push_all') {

			$dir_from   = $dir_stag;
			$dir_to     = $dir_prod;
			$from       = 'stag';

			$changes        = \directory\directory::get_directory_changes($dir_from, $dir_to, $all_files);
			$changed_files  = $changes->get();
			$files          = array();

			\directory\deployment::backup_changes($changes);

			foreach ($changed_files as $changed_file) {

				if (!$changed_file->has_reason('dne')) {

					$file = $changed_file->get_object()->get_path();

					$push_result = \directory\deployment::push_file($conn, $file, $dir_from, $dir_to, $from);

					if (!$push_result->is_success())
						push_alert($push_result->get_msg(), $push_result->get_data('title'), $push_result->get_data('type'), THIS_URL_FULL);
				}
			}

			push_alert('All files and directories pushed successfully', 'Files Pushed', 'success', THIS_URL_FULL);
		}

		// sync staging

		if ($_GET['act'] == 'sync_staging') {

			$dir_from   = $dir_prod;
			$dir_to     = $dir_stag;
			$from       = 'prod';

			$changes        = \directory\directory::get_directory_changes($dir_from, $dir_to, $all_files);
			$changed_files  = $changes->get();
			$files          = array();

			\directory\deployment::backup_changes($changes);

			foreach ($changed_files as $changed_file) {

				if (!$changed_file->has_reason('dne')) {
					// add/overwrite file

					$file = $changed_file->get_object()->get_path();

					$push_result = \directory\deployment::push_file($conn, $file, $dir_from, $dir_to, $from);

					if (!$push_result->is_success())
						push_alert($push_result->get_msg(), $push_result->get_data('title'), $push_result->get_data('type'), THIS_URL_FULL);
				} else {
					// delete file

					$file = $changed_file->get_object()->get_path();

					$delete_result = \directory\deployment::delete_file($conn, $file, $dir_to, 'stag');

					if (!$delete_result->is_success())
						push_alert($delete_result->get_msg(), $delete_result->get_data('title'), $delete_result->get_data('type'), THIS_URL_FULL);
				}
			}

			push_alert('Staging synced with production successfully', 'Staging Synced', 'success', THIS_URL_FULL);
		}

		// push all new and newer

		if ($_GET['act'] == 'push_all_newer') {

			$dir_from   = $dir_stag;
			$dir_to     = $dir_prod;
			$from       = 'stag';

			$changes        = \directory\directory::get_directory_changes($dir_from, $dir_to, $all_files);
			$changed_files  = $changes->get();
			$files          = array();

			\directory\deployment::backup_changes($changes);

			foreach ($changed_files as $changed_file) {

				if (!$changed_file->has_reason('dne') && !$changed_file->has_reason('older')) {

					$file = $changed_file->get_object()->get_path();

					$push_result = \directory\deployment::push_file($conn, $file, $dir_from, $dir_to, $from);

					if (!$push_result->is_success())
						push_alert($push_result->get_msg(), $push_result->get_data('title'), $push_result->get_data('type'), THIS_URL_FULL);
				}
			}

			push_alert('All files and directories pushed successfully', 'Files Pushed', 'success', THIS_URL_FULL);
		}

		// push

		if ($_GET['act'] == 'push') {

			if (!($file = $_GET['file']))
				push_alert('File not specified.', 'Error', 'error', THIS_URL_FULL);

			if (!($from = $_GET['from']))
				push_alert('From parameter not specified.', 'Error', 'error', THIS_URL_FULL);

			if ($from == 'stag') {
				$dir_from   = $dir_stag;
				$dir_to     = $dir_prod;
			} else {
				$dir_from   = $dir_prod;
				$dir_to     = $dir_stag;
			}

			if ($backup_file = $dir_to->get_file($file))
				\directory\deployment::backup_file($backup_file->get_full_path());

			$push_result = \directory\deployment::push_file($conn, $file, $dir_from, $dir_to, $from);

			push_alert($push_result->get_msg(), $push_result->get_data('title'), $push_result->get_data('type'), THIS_URL_FULL);
		}

		// delete

		if ($_GET['act'] == 'delete') {

			if (!($file = $_GET['file']))
				push_alert('File not specified.', 'Error', 'error', THIS_URL_FULL);

			if (!($from = $_GET['from']))
				push_alert('From parameter not specified.', 'Error', 'error', THIS_URL_FULL);

			if ($from == 'stag')
				$dir = $dir_stag;
			else
				$dir = $dir_prod;

			\directory\deployment::backup_file($dir->get_file($file)->get_full_path());

			$delete_file = \directory\deployment::delete_file($conn, $file, $dir, $from);

			push_alert($delete_file->get_msg(), $delete_file->get_data('title'), $delete_file->get_data('type'), THIS_URL_FULL);
		}

		// ignore

		if ($_GET['act'] == 'ignore') {

			if (!($file = $_GET['file']))
				push_alert('File not specified.', 'Error', 'error', THIS_URL_FULL);

			$ignore_file = \directory\deployment::ignore_file($conn, $file, $dir_stag, $dir_prod);

			push_alert($ignore_file->get_msg(), $ignore_file->get_data('title'), $ignore_file->get_data('type'), THIS_URL_FULL);
		}

		// uningore

		if ($_GET['act'] == 'unignore') {

			if (!($file = $_GET['file']))
				push_alert('File not specified.', 'Error', 'error', THIS_URL_FULL);

			$unignore_file = \directory\deployment::unignore_file($conn, $file);

			push_alert($unignore_file->get_msg(), $unignore_file->get_data('title'), $unignore_file->get_data('type'), THIS_URL_FULL);
		}
	}