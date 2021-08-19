<?
	function listing(\directory\directory $directory, \directory\directory $directory_other, $all_files, $header, $from, $position, $limit = LIMIT_FILES, $start = 0, $just_rows = false) {
		if ($from != 'stag' && $from != 'prod')
			$from = 'stag';

		if ($position != 'left' && $position != 'right')
			$position = 'left';

		$changed_files = \directory\directory::get_directory_changes($directory, $directory_other, $all_files, $limit, $start);
		$changed_files = $changed_files->get();

		$allow_push = false;

		foreach ($changed_files as $changed_file) {
			if (!$changed_file->has_reason('dne'))
				$allow_push = true;
		}

		$output = '';

		if (!$just_rows) {
			$output = require_return(THIS_DIR.'/view/files.php', array(
				'changed_files' => $changed_files,
				'from'          => $from,
				'header'        => $header,
				'position'      => $position,
				'allow_push'    => $allow_push,
			));
		} else
			$output .= listing_rows($changed_files, $from);

		return $output;
	}

	// returns HTML output of just rows
	function listing_rows(array $changed_files, $from) {
		$output = '';

		foreach ($changed_files as $changed_file)
			$output .= listing_row($changed_file, $from);

		return $output;
	}

	// returns output of one row
	function listing_row(change $changed_file, $from) {
		return require_return(THIS_DIR.'/view/partials/files_row.php', array(
			'from'          => $from,
			'changed_file'  => $changed_file,
		));
	}

	function listing_ignored($conn) {
		$ignored_files = \directory\deployment::get_ignored_files($conn, PATH_STAG, PATH_PROD);

		return require_return(THIS_DIR.'/view/files_ignored.php', array(
			'ignored_files' => $ignored_files,
		));
	}

	function listing_pushed(mysqli $conn, $page = 0) {
		$limit              = 10;
		$pushed_files       = \directory\deployment::get_pushed_files($conn, $limit, $page);
		$pushed_files_count = \directory\deployment::get_pushed_files_count($conn);

		$num_pages = ceil($pushed_files_count / $limit);

		return require_return(THIS_DIR.'/view/files_pushed.php', array(
			'page'                  => $page,
			'limit'                 => $limit,
			'pushed_files'          => $pushed_files,
			'pushed_files_count'    => $pushed_files_count,
			'num_pages'             => $num_pages,
		));
	}

	function listing_pag($element, $total = 1, $current = 1) {
		$total = $total > 0 ? $total : 1;

		echo '<div class="pag" id="pag_'.$element.'" data-current="'.$current.'" data-total="'.$total.'">';
		echo '<div class="pag-icons">';
		echo '<i class="pag-icon pag-first fa fa-angle-double-left '.($current==1?'disabled':'').'" aria-hidden="true"></i>';
		echo '<i class="pag-icon pag-prev fa fa-angle-left '.($current==1?'disabled':'').'" aria-hidden="true"></i>';
		echo '</div>';
		echo '<div class="pag-text">Page <span class="pag-num pag-num-cur">'.$current.'</span> of <span class="pag-num pag-num-total">'.$total.'</span></div>';
		echo '<div class="pag-icons">';
		echo '<i class="pag-icon pag-next fa fa-angle-right '.($current==$total?'disabled':'').'" aria-hidden="true"></i>';
		echo '<i class="pag-icon pag-last fa fa-angle-double-right '.($current==$total?'disabled':'').'" aria-hidden="true"></i>';
		echo '</div>';
		echo '</div>';
	}

	function push_alert($text, $title = '', $type = 'alert', $redirect = false, $critical = false) {
		if (!is_array($_SESSION['ed_alerts']) || !isset($_SESSION['ed_alerts']))
			$_SESSION['ed_alerts'] = array();

		array_push($_SESSION['ed_alerts'], array(
			'text'      => $text,
			'title'     => $title,
			'type'      => $type,
			'critical'  => $critical,
		));

		if ($redirect) {
			if (!headers_sent())
				header('Location: '.$redirect);
			else
				echo '<script type="text/javascript">window.location="'.$redirect.'"</script>';

			die();
		}
	}

	function get_alerts($only_first = false) {
		$output = '';

		if (is_array($_SESSION['ed_alerts']) && count($_SESSION['ed_alerts']) > 0) {
			$output .= '<div class="ed-alerts">';

			foreach ($_SESSION['ed_alerts'] as $alert) {
				$output .= '<div class="ed-alert '.$alert['type'].'">';
				$output .= '<div class="fa fa-fw fa-close close" style="float:right; line-height:inherit; cursor:pointer;" onclick="RemoveEDAlert(this);"></div>';

				if ($alert['title'])
					$output .= '<div class="ed-alert-title">'.$alert['title'].'</div>';

				$output .= '<div class="ed-alert-text">'.$alert['text'].'</div>';
				$output .= '</div>';

				if ($only_first)
					break;
			}

			$output .= '</div>';

			$output .= '<script>function RemoveEDAlert(that){$(that).parent().slideUp();}</script>';

			unset($_SESSION['ed_alerts']);
		}

		return $output;
	}

	function alerts_are_critical() {
		if (isset($_SESSION['ed_alerts']) && $_SESSION['ed_alerts']) {
			foreach ($_SESSION['ed_alerts'] as $alert) {
				if (isset($alert['critical']) && $alert['critical'])
					return true;
			}
		}

		return false;
	}

	// returns content of file
	function require_return($file_path, $data = array(), $decode = false) {
		// check if file exists
		if (!file_exists($file_path))
			return 'FILE <b>'.$file_path.'</b> DOES NOT EXIST';

		// start capturing output
		ob_start();

		// extract data
		if (is_array($data))
			extract($data);

		include $file_path;

		// get the contents from the buffer
		$content = ob_get_clean();

		if ($decode)
			return json_decode($content, true);
		else
			return $content;
	}

	if (!function_exists('pre_dump')) {
		function pre_dump($x) {
			echo '<pre>';
			var_dump($x);
			echo '</pre>';
		}
	}

	function init_csrf() {
		if (!isset($_SESSION['csrf_token']) || (isset($_SESSION['csrf_token']) && !$_SESSION['csrf_token']))
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	function validate_csrf() {
		$return = array(
			'success'   => false,
			'msg'       => 'Unknown CSRF Error',
		);

		init_csrf();

		$headers = apache_request_headers();

		if (isset($headers['Csrftoken']))
			$csrf_token = $headers['Csrftoken'];
		elseif (isset($headers['CsrfToken']))
			$csrf_token = $headers['CsrfToken'];
		else
			$csrf_token = false;
		
		if ($csrf_token) {
			if (!hash_equals($csrf_token, $_SESSION['csrf_token'])) {
				$return['success'] = false;
				$return['msg']     = 'Wrong CSRF token.';
			} else
				$return['success'] = true;
		} else {
			$return['success']  = false;
			$return['msg']      = 'No CSRF token.';
		}

		return $return;
	}

	function get_conn() {
		if (DB_CRED_CALLBACK && function_exists(DB_CRED_CALLBACK))
			$db_cred = DB_CRED_CALLBACK();
		else {
			$db_cred = array(
				'host' => DB_HOST,
				'user' => DB_USER,
				'pass' => DB_PASS,
				'name' => DB_NAME,
			);
		}

		$conn = new mysqli($db_cred['host'], $db_cred['user'], $db_cred['pass'], $db_cred['name']);

		if (mysqli_connect_error())
			die('Connection could not be made. Error: '.$conn->connect_error);
		else
			return $conn;
	}

	function load_config() {
		$ini = parse_ini_file(THIS_DIR.'/_config.ini', true);

		// load custom config
		if (file_exists(THIS_DIR.'/_config_custom.ini')) {
			$ini_custom = parse_ini_file(THIS_DIR.'/_config_custom.ini', true);
			$ini        = array_replace_recursive($ini, $ini_custom);
		}

		// load dev config
		if (file_exists(THIS_DIR.'/_config_dev.ini') && file_exists(THIS_DIR.'/DEVMACHINE')) {
			$ini_custom = parse_ini_file(THIS_DIR.'/_config_dev.ini', true);
			$ini        = array_replace_recursive($ini, $ini_custom);
		}

		define('LIMIT_FILES', $ini['limit_files']);
		define('DB_CRED_CALLBACK', $ini['db_cred_callback']);
		define('THEME', $ini['theme']);
		define('DIR_PROD', $ini['directory']['prod']);
		define('DIR_STAG', $ini['directory']['stag']);
		define('DB_HOST', $ini['database']['host']);
		define('DB_USER', $ini['database']['user']);
		define('DB_PASS', $ini['database']['pass']);
		define('DB_NAME', $ini['database']['name']);
	}