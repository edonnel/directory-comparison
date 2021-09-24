<?
	namespace directory_comparison;

	start_the_session();

	$valid_subs = array(
		'sub_push_all',
		'sub_sync',
		'sub_push_all_newer',
		'sub_bulk',
	);

	// check to see if valid sub

	$valid = false;

	foreach ($valid_subs as $valid_sub) {

		if (isset($_POST[$valid_sub]))
			$valid = true;
	}

	// run sub

	if ($valid) {

		require_once __DIR__.'/src/php/classes/directory.class.php';
		require_once __DIR__.'/src/php/classes/deployment.class.php';
		require_once __DIR__.'/lib/result/result.class.php';
		require_once __DIR__.'/lib/changes/changes.class.php';

		$files_to_exclude = deployment::get_ignored_files($conn, null, null, false);

		$dir_stag = new directory(PATH_STAG, $files_to_exclude);
		$dir_prod = new directory(PATH_PROD, $files_to_exclude);

		$all_files = directory::combine_directories($dir_stag, $dir_prod);

		// push all

		if (isset($_POST['sub_push_all'])) {

			$dir_from   = $dir_stag;
			$dir_to     = $dir_prod;
			$from       = 'stag';

			$changes        = directory::get_directory_changes($dir_from, $dir_to, $all_files, 999999);
			$changed_files  = $changes->get();
			$files          = array();

			$backup = deployment::backup_changes($changes);

			if (!$backup)
				\alerts::push('Files and directories not pushed. Could not create backup zip file.', 'Files Not Pushed', 'error', THIS_URL_FULL);

			foreach ($changed_files as $changed_file) {

				if (!$changed_file->has_reason('dne')) {

					$file = $changed_file->get_object()->get_path();

					$push_result = deployment::push_file($conn, $file, $dir_from, $dir_to, $from);

					if (!$push_result->is_success())
						\alerts::push($push_result->get_msg(), $push_result->get_data('title'), $push_result->get_data('type'), THIS_URL_FULL);
				}
			}

			\alerts::push('All files and directories pushed successfully', 'Files Pushed', 'success', THIS_URL_FULL);

		}

		// sync staging

		if (isset($_POST['sub_sync'])) {

			$dir_from   = $dir_prod;
			$dir_to     = $dir_stag;
			$from       = 'prod';

			$changes        = directory::get_directory_changes($dir_from, $dir_to, $all_files, 999999);
			$changed_files  = $changes->get();
			$files          = array();

			deployment::backup_changes($changes);

			foreach ($changed_files as $changed_file) {

				if (!$changed_file->has_reason('dne')) {

					// add/overwrite file
					$file = $changed_file->get_object()->get_path();

					$push_result = deployment::push_file($conn, $file, $dir_from, $dir_to, $from);

					if (!$push_result->is_success())
						\alerts::push($push_result->get_msg(), $push_result->get_data('title'), $push_result->get_data('type'), THIS_URL_FULL);
				} else {

					// delete file
					$file = $changed_file->get_object()->get_path();

					$delete_result = deployment::delete_file($conn, $file, $dir_to, 'stag');

					if (!$delete_result->is_success())
						\alerts::push($delete_result->get_msg(), $delete_result->get_data('title'), $delete_result->get_data('type'), THIS_URL_FULL);
				}
			}

			\alerts::push('Staging synced with production successfully', 'Staging Synced', 'success', THIS_URL_FULL);
		}

		// push all new and newer

		if ($_POST['sub_push_all_newer']) {

			$dir_from   = $dir_stag;
			$dir_to     = $dir_prod;
			$from       = 'stag';

			$changes        = directory::get_directory_changes($dir_from, $dir_to, $all_files, 999999);
			$changed_files  = $changes->get();
			$files          = array();

			deployment::backup_changes($changes);

			foreach ($changed_files as $changed_file) {

				if (!$changed_file->has_reason('dne') && !$changed_file->has_reason('older')) {

					$file = $changed_file->get_object()->get_path();

					$push_result = deployment::push_file($conn, $file, $dir_from, $dir_to, $from);

					if (!$push_result->is_success())
						\alerts::push($push_result->get_msg(), $push_result->get_data('title'), $push_result->get_data('type'), THIS_URL_FULL);
				}
			}

			\alerts::push('All files and directories pushed successfully.', 'Files Pushed', 'success', THIS_URL_FULL);
		}

		// bulk

		if (isset($_POST['sub_bulk'])) {

			if (!isset($_POST['from']) || !($from = $_POST['from']))
				\alerts::push('"From" parameter wasn\'t set somehow... Check your code.', 'Bulk Action', 'error', THIS_URL_FULL);

			if (!isset($_POST['bulk_action']) || !($bulk_action = $_POST['bulk_action']))
				\alerts::push('Please select an action.', 'Bulk Action', 'error', THIS_URL_FULL);

			if (!isset($_POST['file_paths']) || !($file_paths = $_POST['file_paths']))
				\alerts::push('Please select at least one file.', 'Bulk Action', 'error', THIS_URL_FULL);

			$result = new \result;

			foreach ($file_paths as $file_path) {

				if ($from == 'stag') {
					$dir_from   = $dir_stag;
					$dir_to     = $dir_prod;
				} else {
					$dir_from   = $dir_prod;
					$dir_to     = $dir_stag;
				}

				// get file obj
				if ($dir_from->get_file($file_path))
					$file = $dir_from->get_file($file_path);
				elseif ($dir_to->get_file($file_path))
					$file = $dir_to->get_file($file_path);
				else
					\alerts::push('File <b>'.$file_path.'</b> does not exist.', 'Bulk Action', 'error', THIS_URL_FULL);

				// check if file exists
				if ($bulk_action === 'push' || $bulk_action === 'delete') {
					if (!$dir_from->get_file($file_path))
						\alerts::push('File <b>'.$file_path.'</b> does not exist on '.($from == 'stag' ? 'staging' : 'production').'.', 'Bulk Action - '.ucwords($bulk_action).' Error', 'error', THIS_URL_FULL);
				}

				// do action
				if ($bulk_action === 'push') {

					// backup the file
					if ($backup_file = $dir_to->get_file($file_path))
						deployment::backup_file($backup_file->get_full_path());

					$result = deployment::push_file($conn, $file_path, $dir_from, $dir_to, $from);

				} elseif ($bulk_action === 'delete') {

					deployment::backup_file($dir_from->get_file($file_path)->get_full_path());

					$result = deployment::delete_file($conn, $file_path, $dir_from, $from);

				} elseif ($bulk_action === 'ignore') {

					// get type
					$file_type = $file->is_dir() ? 'dir' : 'file';

					$result = deployment::ignore_file($conn, $file_path, $dir_stag, $dir_prod, $file_type);
				}

				if (!$result->is_success())
					\alerts::push($result->get_msg(), 'Bulk Action', 'error', THIS_URL_FULL);
			}

			if ($result->is_success())
				\alerts::push('Bulk '.$bulk_action.' completed successfully.', 'Bulk Action', 'success', THIS_URL_FULL);
			else
				\alerts::push($result->get_msg(), 'Bulk Action', 'error', THIS_URL_FULL);
		}
	}

	// check directory exists

	if (!file_exists(PATH_PROD))
		\alerts::push('Production directory <span style="font-family:monospace;">'.DIR_PROD.'</span> does not exist.', 'Directory Error', 'error');


	if (!file_exists(PATH_STAG))
		\alerts::push('Staging directory <span style="font-family:monospace;">'.DIR_STAG.'</span> does not exist.', 'Directory Error', 'error', false, true);