<?
	namespace directory_comparison;

	function listing(
		\directory_comparison\directory $directory,
		\directory_comparison\directory $directory_other,
		$all_files,
		$header,
		$from,
		$position,
		$limit = LIMIT_FILES,
		$just_rows = false,
		$order_type = false,
		$order_value = false
	) {
		if ($from != 'stag' && $from != 'prod')
			$from = 'stag';

		if ($position != 'left' && $position != 'right')
			$position = 'left';

		$changed_files = \directory_comparison\directory::get_directory_changes($directory, $directory_other, $all_files, $limit);
		$changed_files = $changed_files->get();

		$allow_push = false;

		foreach ($changed_files as $changed_file) {
			if (!$changed_file->has_reason('dne'))
				$allow_push = true;
		}

		$data = array(
			'changed_files' => $changed_files,
			'from'          => $from,
			'header'        => $header,
			'position'      => $position,
			'allow_push'    => $allow_push,
			'order_type'    => $order_type,
			'order_value'   => $order_value,
			// TODO: find better solution for hiding the "load more" button if all files have already been loaded
			'all_loaded'    => \directory_comparison\directory::get_directory_changes_all_loaded($directory, $directory_other, $all_files, $limit),
			'svg'           => array(
				'sort_numeric_down_alt' => get_svg_icon('sort-numeric-down-alt'),
				'sort_numeric_up_alt'   => get_svg_icon('sort-numeric-up-alt'),
				'ellipsis'              => get_svg_icon('ellipsis-h'),
			),
		);

		$output = '';

		if (!$just_rows) {
			$output = require_return(THIS_DIR.'/view/files.php', $data);
		} else
			$output .= listing_rows($changed_files, $from);

		return $output;
	}

	// returns HTML output of just rows
	function listing_rows(array $changed_files, $from) {
		$output = '';
		$data   = array('svg' => array(
			'ignore'    => get_svg_icon('ban'),
			'trash'     => get_svg_icon('trash'),
			'arrow'     => $from == 'stag' ? get_svg_icon('long-arrow-alt-right') : get_svg_icon('long-arrow-alt-left'),
			'file'      => get_svg_icon('file'),
			'folder'    => get_svg_icon('folder-open'),
		));

		foreach ($changed_files as $changed_file)
			$output .= listing_row($changed_file, $from, $data);

		return $output;
	}

	// returns output of one row
	function listing_row(\change $changed_file, $from, $data_extra = array()) {
		$data = array(
			'from'          => $from,
			'changed_file'  => $changed_file,
		);
		$data = array_merge($data, $data_extra);

		return require_return(THIS_DIR.'/view/partials/files_row.php', $data);
	}

	function listing_ignored($conn) {
		$ignored_files = \directory_comparison\deployment::get_ignored_files($conn, PATH_STAG, PATH_PROD);

		return require_return(THIS_DIR.'/view/files_ignored.php', array(
			'ignored_files' => $ignored_files,
			'svg'           => array(
				'ignore'    => get_svg_icon('ban'),
				'unignore'  => get_svg_icon('undo'),
				'file'      => get_svg_icon('file'),
				'folder'    => get_svg_icon('folder-open'),
				'question'  => get_svg_icon('question'),
			),
		));
	}

	function listing_pushed(\mysqli $conn, $page = 0) {
		$limit              = 10;
		$pushed_files       = \directory_comparison\deployment::get_pushed_files($conn, $limit, $page);
		$pushed_files_count = \directory_comparison\deployment::get_pushed_files_count($conn);

		$num_pages = ceil($pushed_files_count / $limit);

		return require_return(THIS_DIR.'/view/files_pushed.php', array(
			'page'                  => $page,
			'limit'                 => $limit,
			'pushed_files'          => $pushed_files,
			'pushed_files_count'    => $pushed_files_count,
			'num_pages'             => $num_pages,
			'svg'                   => array(
				'file'      => get_svg_icon('file'),
				'folder'    => get_svg_icon('folder-open'),
				'trash'     => get_svg_icon('trash'),
				'arrow'     => get_svg_icon('long-arrow-alt-right'),
			),
		));
	}

	function listing_pag($element, $total = 1, $current = 1) {
		$total = $total > 0 ? $total : 1;

		echo '<div class="pag" id="pag_'.$element.'" data-current="'.$current.'" data-total="'.$total.'">';
		echo '<div class="pag-icons">';
		echo '<span class="pag-icon pag-first '.($current==1?'disabled':'').'">'.get_svg_icon('angle-double-left').'</span>';
		echo '<span class="pag-icon pag-prev '.($current==1?'disabled':'').'">'.get_svg_icon('angle-left').'</span>';
		echo '</div>';
		echo '<div class="pag-text">Page <span class="pag-num pag-num-cur">'.$current.'</span> of <span class="pag-num pag-num-total">'.$total.'</span></div>';
		echo '<div class="pag-icons">';
		echo '<span class="pag-icon pag-next '.($current==$total?'disabled':'').'">'.get_svg_icon('angle-right').'</span>';
		echo '<span class="pag-icon pag-last '.($current==$total?'disabled':'').'">'.get_svg_icon('angle-double-right').'</span>';
		echo '</div>';
		echo '</div>';
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

	function pre_dump($x) {
		echo '<pre>';
		var_dump($x);
		echo '</pre>';
	}

	function get_conn() {
		if (DB_CRED_CALLBACK && function_exists(__NAMESPACE__.'\\'.DB_CRED_CALLBACK))
			$db_cred = DB_CRED_CALLBACK();
		else {
			$db_cred = array(
				'host' => DB_HOST,
				'user' => DB_USER,
				'pass' => DB_PASS,
				'name' => DB_NAME,
			);
		}

		$conn = new \mysqli($db_cred['host'], $db_cred['user'], $db_cred['pass'], $db_cred['name']);

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

	function get_svg_icon($name) {
		if (file_exists($file_path = THIS_DIR.'/src/svg/'.$name.'.svg'))
			return file_get_contents($file_path);
		else
			return '';
	}

	function start_the_session() {
		if (session_status() === PHP_SESSION_NONE)
			@session_start();
	}