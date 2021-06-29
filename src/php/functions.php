<?
	function listing(\directory\directory $directory, \directory\directory $directory_other, $all_files, $header, $from, $position) {
		if ($from != 'stag' && $from != 'prod')
			$from = 'stag';

		if ($position != 'left' && $position != 'right')
			$position = 'left';

		$changed_files = \directory\directory::get_directory_changes($directory, $directory_other, $all_files);
		$changed_files = $changed_files->get();

		$allow_push = false;

		foreach ($changed_files as $changed_file) {
			if (!$changed_file->has_reason('dne'))
				$allow_push = true;
		}

		require THIS_DIR.'/view/files.php';
	}

	function listing_ignored(mysqli $conn, $ignored_files) {
		require THIS_DIR.'/view/files_ignored.php';
	}

	function listing_pushed(mysqli $conn, $page = 0) {
		$limit              = 20;
		$pushed_files       = \directory\deployment::get_pushed_files($conn, $limit, $page);
		$pushed_files_count = \directory\deployment::get_pushed_files_count($conn);

		$num_pages = ceil($pushed_files_count / $limit);

		require THIS_DIR.'/view/files_pushed.php';
	}

	function log_message_redirect($text, $type, $title, $redirect) {
		if (session_status() === PHP_SESSION_NONE)
			session_start();

		$_SESSION['log_msg'] = array(
			'text'  => $text,
			'type'  => $type,
			'title' => $title,
		);

		header('Location: '.$redirect);
		die();
	}

	// recursively deletes directory
	function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);

			foreach ($objects as $object) {
				if ($object != '.' && $object != '..') {
					if (is_dir($dir.DIRECTORY_SEPARATOR.$object) && !is_link($dir.'/'.$object))
						rrmdir($dir.DIRECTORY_SEPARATOR.$object);
					else {
						$unlink = unlink($dir.DIRECTORY_SEPARATOR.$object);

						if (!$unlink)
							return false;
					}
				}
			}

			rmdir($dir);

			return true;
		}

		return false;
	}

	// returns whether or not an item is in a one-dimensional associative array at a certain key
	function in_array_assoc($value, $array, $key){
		foreach ($array as $item) {
			if ($item[$key] == $value)
				return true;
		}

		return false;
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

	if (!function_exists('pre_dump')) {
		function pre_dump($x) {
			echo '<pre>';
			var_dump($x);
			echo '</pre>';
		}
	}