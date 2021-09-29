<?
	namespace directory_comparison;

	ini_set('display_errors', 1);

	define('THIS_DIR', dirname(__DIR__));

	header('Content-Type: application/json');

	// load functions
	require_once THIS_DIR.'/src/php/functions.php';

	// start the session
	start_the_session();

	// load function functions
	if (file_exists(THIS_DIR.'/src/php/functions_custom.php'))
		require_once THIS_DIR.'/src/php/functions_custom.php';

	// load config
	load_config();

	// load classes
	require_once THIS_DIR.'/src/php/classes/directory.class.php';
	require_once THIS_DIR.'/src/php/classes/deployment.class.php';
	require_once THIS_DIR.'/lib/result/result.class.php';
	require_once THIS_DIR.'/lib/changes/changes.class.php';
	require_once THIS_DIR.'/lib/csrf/csrf.class.php';

	// load globals
	require_once THIS_DIR.'/globals.php';

	// security
	$validate_csrf = \csrf::validate();

	if (!$validate_csrf['success']) {
		$result = (new \result)
			->set_success(false)
			->set_msg($validate_csrf['msg']);

		$result->echo_json(true);
	}

	// init result
	$result = new \result;
	$result
		->set_msg('Nothing happened.')
		->set_data('title', 'Error')
		->set_data('type', 'error');

	$valid_acts = array(
		'get_listing_files',
		'push',
		'push_dir',
		'delete',
		'ignore',
	);

	if (in_array($_POST['act'], $valid_acts)) {

		// ordering
		$order_date_options = array('asc', 'desc');
		$order_type         = $_POST['order_type'] ?? false;
		$order_value        = false;

		if ($_POST['change_order']) {

			if ($order_type == 'date') {

				$key = array_search($_POST['order_value'], $order_date_options);

				if ($key !== false) {

					if (!isset($order_date_options[$key + 1]))
						$order_value = '';
					else {

						// move to next item
						$order_value = $order_date_options[$key + 1];
					}
				} else
					$order_value = $order_date_options[0];
			}
		}

		// variables
		$files_to_exclude   = deployment::get_ignored_files($conn, null, null, false);
		$dir_stag           = new directory(PATH_STAG, $files_to_exclude, $order_type, $order_value);
		$dir_prod           = new directory(PATH_PROD, $files_to_exclude, $order_type, $order_value);

		//--  get listing of changed files

		if ($_POST['act'] == 'get_listing_files') {

			if (!($from = $_POST['from'])) {
				$result
					->set_success(false)
					->set_msg('From parameter not specified.')
					->set_data('title', 'Error')
					->set_data('type', 'error');
			} else {
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

				$all_files = directory::combine_directories($dir_stag, $dir_prod);

				if (isset($_POST['limit']) && is_integer($_POST['limit']))
					$limit = $_POST['limit'];
				else
					$limit = LIMIT_FILES;

				if ($_POST['load_all'])
					$limit = 9999999;

				$just_rows = $_POST['just_rows'] ?? false;

				$html = listing($dir_from, $dir_to, $all_files, $header, $from, $position, $limit, $just_rows, $order_type, $order_value);

				$result
					->set_success(true)
					->set_data('html', $html)
					->set_data('all_loaded', $_POST['load_all'] || count($all_files) <= $limit);
			}
		}

		//-- push

		if ($_POST['act'] == 'push') {

			$result = new \result;

			if ($file = $_POST['file']) {

				if ($from = $_POST['from']) {

					if ($from == 'stag') {
						$dir_from   = $dir_stag;
						$dir_to     = $dir_prod;
					} else {
						$dir_from   = $dir_prod;
						$dir_to     = $dir_stag;
					}

					if ($backup_file = $dir_to->get_file($file))
						deployment::backup_file($backup_file->get_full_path());

					$result = deployment::push_file($conn, $file, $dir_from, $dir_to, $from);
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
		}

		//-- push entire directory

		if ($_POST['act'] == 'push_dir') {

			$result = new \result;

			if ($dir = $_POST['file']) {

				if ($from = $_POST['from']) {

					if ($from == 'stag') {
						$dir_from   = $dir_stag;
						$dir_to     = $dir_prod;
					} else {
						$dir_from   = $dir_prod;
						$dir_to     = $dir_stag;
					}

					// make sure it's a directory
					if (is_dir(($dir_full = $dir_from->get_dir().'/'.$dir))) {

						$children = directory::get_directory_listing($dir_full);

						foreach ($children as $child_file_path => $child) {

							$child_file_path = $dir.$child_file_path;

							$child_file = $dir_from->get_file($child_file_path);

							if ($child_file) {

								// back up the file if it exists
								if ($backup_file = $dir_to->get_file($child_file_path))
									deployment::backup_file($backup_file->get_full_path());

								$result = deployment::push_file($conn, $child_file_path, $dir_from, $dir_to, $from);
							}
						}
					} else {
						$result
							->set_success(false)
							->set_msg('File is not a directory.')
							->set_data('title', 'Error')
							->set_data('type', 'error');
					}
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
		}

		//-- delete

		if ($_POST['act'] == 'delete') {

			$result = new \result;

			if ($file = $_POST['file']) {

				if ($from = $_POST['from']) {

					if ($from == 'stag')
						$dir = $dir_stag;
					else
						$dir = $dir_prod;

					deployment::backup_file($dir->get_file($file)->get_full_path());

					$result = deployment::delete_file($conn, $file, $dir, $from);
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
		}

		//-- ignore

		if ($_POST['act'] == 'ignore') {

			$result = new \result;

			if ($file = $_POST['file']) {
				$result = deployment::ignore_file($conn, $file, $dir_stag, $dir_prod, $_POST['type']);
			} else {
				$result
					->set_success(false)
					->set_msg('File not specified.')
					->set_data('title', 'Error')
					->set_data('type', 'error');
			}
		}

	}

	//-- get listing of ignored files

	if ($_POST['act'] == 'get_ignored_files') {
		$html = listing_ignored($conn);

		$result
			->set_success(true)
			->set_data('html', $html);
	}

	//-- get listing of previously pushed files

	if ($_POST['act'] == 'get_pushed_files') {

		$page = isset($_POST['pag']) && $_POST['pag'] ? $_POST['pag'] : 1;

		$html = listing_pushed($conn, $page);

		$result
			->set_success(true)
			->set_data('html', $html);
	}

	//-- unignore

	if ($_POST['act'] == 'unignore') {

		$result = new \result;

		if ($file = $_POST['file']) {
			$result = deployment::unignore_file($conn, $file);
		} else {
			$result
				->set_success(false)
				->set_msg('File not specified.')
				->set_data('title', 'Error')
				->set_data('type', 'error');
		}
	}

	//-- save notes

	if ($_POST['act'] == 'save_notes') {

		if (isset($_POST['notes'])) {

			$notes = mysqli_real_escape_string($conn, $_POST['notes']);

			$stmt   = "UPDATE `staging_files_settings` SET `notes` = '$notes' WHERE `id` = 1 LIMIT 1";
			$query  = $conn->query($stmt);

			if ($query) {
				$result
					->set_success(true)
					->set_msg('Notes saved successfully')
					->set_data('title', 'Notes Saved')
					->set_data('type', 'success');
			} else {
				$result
					->set_success(false)
					->set_msg('Notes could not be saved.<br><br>Error:<br>'.$conn->error)
					->set_data('title', 'Error')
					->set_data('type', 'error');
			}
		} else {
			$result
				->set_success(false)
				->set_msg('Notes parameter not sent.')
				->set_data('title', 'Error')
				->set_data('type', 'error');
		}

	}

	// echo the result
	$result->echo_json();