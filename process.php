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

	if (in_array($_GET['act'], $valid_acts)) {

		require_once __DIR__.'/src/php/directory.class.php';
		require_once __DIR__.'/src/php/deployment.class.php';
		require_once __DIR__.'/_globals_dir.php';
		require_once dirname(THIS_DIR).'/_src/php/result.class.php';
		require_once dirname(THIS_DIR).'/_src/php/changes.class.php';

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
						log_message_redirect($push_result->get_msg(), $push_result->get_data('type'), $push_result->get_data('title'), THIS_URL_FULL);
				}
			}

			log_message_redirect('All files and directories pushed successfully', 'success', 'Files Pushed', THIS_URL_FULL);
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
						log_message_redirect($push_result->get_msg(), $push_result->get_data('type'), $push_result->get_data('title'), THIS_URL_FULL);
				} else {
					// delete file

					$file = $changed_file->get_object()->get_path();

					$delete_result = \directory\deployment::delete_file($conn, $file, $dir_to, 'stag');

					if (!$delete_result->is_success())
						log_message_redirect($delete_result->get_msg(), $delete_result->get_data('type'), $delete_result->get_data('title'), THIS_URL_FULL);
				}
			}

			log_message_redirect('Staging synced with production successfully', 'success', 'Staging Synced', THIS_URL_FULL);
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
						log_message_redirect($push_result->get_msg(), $push_result->get_data('type'), $push_result->get_data('title'), THIS_URL_FULL);
				}
			}

			log_message_redirect('All files and directories pushed successfully', 'success', 'Files Pushed', THIS_URL_FULL);
		}

		// push

		if ($_GET['act'] == 'push') {

			if (!($file = $_GET['file']))
				log_message_redirect('File not specified.', 'error', 'Error', THIS_URL_FULL);

			if (!($from = $_GET['from']))
				log_message_redirect('From parameter not specified.', 'error', 'Error', THIS_URL_FULL);

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

			log_message_redirect($push_result->get_msg(), $push_result->get_data('type'), $push_result->get_data('title'), THIS_URL_FULL);
		}

		// delete

		if ($_GET['act'] == 'delete') {

			if (!($file = $_GET['file']))
				log_message_redirect('File not specified.', 'error', 'Error', THIS_URL_FULL);

			if (!($from = $_GET['from']))
				log_message_redirect('From parameter not specified.', 'error', 'Error', THIS_URL_FULL);

			if ($from == 'stag')
				$dir = $dir_stag;
			else
				$dir = $dir_prod;

			\directory\deployment::backup_file($dir->get_file($file)->get_full_path());

			$delete_file = \directory\deployment::delete_file($conn, $file, $dir, $from);

			log_message_redirect($delete_file->get_msg(), $delete_file->get_data('type'), $delete_file->get_data('title'), THIS_URL_FULL);
		}

		// ignore

		if ($_GET['act'] == 'ignore') {

			if (!($file = $_GET['file']))
				log_message_redirect('File not specified.', 'error', 'Error', THIS_URL_FULL);

			$ignore_file = \directory\deployment::ignore_file($conn, $file, $dir_stag, $dir_prod);

			log_message_redirect($ignore_file->get_msg(), $ignore_file->get_data('type'), $ignore_file->get_data('title'), THIS_URL_FULL);
		}

		// uningore

		if ($_GET['act'] == 'unignore') {

			if (!($file = $_GET['file']))
				log_message_redirect('File not specified.', 'error', 'Error', THIS_URL_FULL);

			$unignore_file = \directory\deployment::unignore_file($conn, $file);

			log_message_redirect($unignore_file->get_msg(), $unignore_file->get_data('type'), $unignore_file->get_data('title'), THIS_URL_FULL);
		}
	}

	if (isset($_SESSION['log_msg'])) {
		log_message($_SESSION['log_msg']['text'], $_SESSION['log_msg']['type'], $_SESSION['log_msg']['title']);
		unset($_SESSION['log_msg']);
	}