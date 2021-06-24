<?
	namespace directory;

	class deployment {

		public static function get_ignored_files(\mysqli $conn, $dir_1 = null, $dir_2 = null) {
			$stmt           = "SELECT `file_path`, `type`, false as 'inherit' FROM `staging_files_ignored`";
			$query          = $conn->query($stmt);
			$ignored_files  = $query->fetch_all(true);

			if ($dir_1 && $dir_2) {

				$dirs = array($dir_1, $dir_2);

				foreach ($ignored_files as $key => $ignored_file) {
					$ignored_file_path = $ignored_file['file_path'];

					$num_children   = 0;
					$file_path      = null;

					foreach ($dirs as $dir) {
						if (file_exists($file_path_new = ($dir.$ignored_file_path))) {
							if ($num_children < ($num_children_new = directory::get_num_children_files($file_path_new))) {
								$num_children   = $num_children_new;
								$file_path      = $file_path_new;
							}
						}
					}

					if ($file_path) {
						$ignored_files[$key] = array(
							'file_path' => $ignored_file_path,
							'type'      => is_file($file_path) ? 'file' : 'dir',
							'inherit'   => false,
							'children'  => $num_children,
						);
					}
				}
			}

			return $ignored_files;
		}

		public static function get_pushed_files_count(\mysqli $conn) {
			$stmt   = "SELECT 1 FROM `staging_files_pushed`";
			$query  = $conn->query($stmt);

			return $query->num_rows;
		}

		public static function get_pushed_files(\mysqli $conn, $limit = 20, $page = 1) {
			$offset = $page <= 1 ? 0 : ($page - 1) * $limit;

			$stmt   = "SELECT * FROM `staging_files_pushed` ORDER BY `timestamp` DESC, `id` DESC LIMIT $offset,$limit";
			$query  = $conn->query($stmt);

			return $query->fetch_all(true);
		}

		public static function push_file(\mysqli $conn, string $file, directory $dir_from, directory $dir_to, $from) : \result {
			$file_from  = $dir_from->get_file($file)->get_full_path();
			$file_to    = $dir_to->get_dir().$dir_from->get_file($file)->get_path();

			$result = new \result;

			if ($is_dir = is_dir($file_from)) {

				// directory already exists
				if (file_exists($file_to)) {
					$result
						->set_success(true)
						->set_msg('Directory <b>'.$file.'</b> already exists on '.($from == 'stag' ? 'Production' : 'Staging').'.')
						->set_data('title', 'Nothing to push')
						->set_data('type', 'success');

					return $result;
				}

				$mkdir = mkdir($file_to, 0755, true);

				if ($mkdir) {
					$query = self::add_to_push_table($conn, $file, 'dir', $from);

					$result
						->set_success(true)
						->set_msg('Directory <b>'.$file.'</b> pushed to '.($from == 'stag' ? 'Production' : 'Staging').'.')
						->set_data('title', 'Directory Pushed')
						->set_data('type', 'success');
				} else {
					$result
						->set_success(true)
						->set_msg('Directory <b>'.$file.'</b> could not be pushed to '.($from == 'stag' ? 'Production' : 'Staging').'.')
						->set_data('title', 'Directory Not Pushed')
						->set_data('type', 'error');
				}
			} else {

				// make the dir

				$dirname = dirname($file_to);

				if (!file_exists($dirname)) {
					$mkdir = mkdir($dirname, 0755, true);

					$query = self::add_to_push_table($conn, str_replace($dir_to->get_dir(), '', $dirname), 'dir', $from);

					if (!$mkdir) {
						$result
							->set_success(true)
							->set_msg('File <b>'.$file.'</b> could not be pushed to '.($from == 'stag' ? 'Production' : 'Staging').'. Directory could not be created.')
							->set_data('title', 'File Not Pushed')
							->set_data('type', 'error');

						return $result;
					}
				}

				// copy the file

				$copy = copy($file_from, $file_to);

				if ($copy) {

					// copy modified time
					$file_time = filemtime($file_from);

					touch($file_to, $file_time);

					if ($is_dir) {
						$name = 'Directory';
						$type = 'dir';
					} else {
						$name = 'File';
						$type = 'file';
					}

					// add to push table

					$query = self::add_to_push_table($conn, $file, $type, $from);

					if ($query) {
						$result
							->set_success(true)
							->set_msg($name.' <b>'.$file.'</b> pushed to '.($from == 'stag' ? 'Production' : 'Staging').'.')
							->set_data('title', 'File Pushed')
							->set_data('type', 'success');
					} else {
						$result
							->set_success(true)
							->set_msg($name.' <b>'.$file.'</b> pushed to '.($from == 'stag' ? 'Production' : 'Staging').' but there was an error adding to the pushes table.<br><br>Error:<br>'.$conn->error)
							->set_data('title', 'File Not Pushed')
							->set_data('type', 'warning');
					}
				} else {
					$result
						->set_success(true)
						->set_msg('<b>'.$file.'</b> could not be pushed to '.($from == 'stag' ? 'Production' : 'Staging'))
						->set_data('title', 'File Not Pushed')
						->set_data('type', 'error');
				}
			}

			return $result;
		}

		public static function delete_file(\mysqli $conn, string $file, directory $dir, $from) : \result {
			$result         = new \result;
			$file_full_path = $dir->get_file($file)->get_full_path();

			if (!file_exists($file_full_path)) {
				$result
					->set_success(true)
					->set_msg('<b>'.$file.'</b> does not exist on '.($from == 'stag' ? 'Production' : 'Staging').'.')
					->set_data('title', 'Nothing to delete')
					->set_data('type', 'success');

				return $result;
			}

			if (is_dir($file_full_path)) {
				$remove = rrmdir($file_full_path);
				$name   = 'Directory';
				$type   = 'dir';
			} else {
				$remove = unlink($file_full_path);
				$name   = 'File';
				$type   = 'file';
			}

			if ($remove) {

				// add to push table

				$query = self::add_to_push_table($conn, $file, $type, $from, true);

				if ($query) {
					$result
						->set_success(true)
						->set_msg($name.' <b>'.$file.'</b> deleted from '.($from == 'stag' ? 'Production' : 'Staging'))
						->set_data('title', $name.' Deleted')
						->set_data('type', 'success');
				} else {
					$result
						->set_success(true)
						->set_msg($name.' <b>'.$file.'</b> deleted from '.($from == 'stag' ? 'Production' : 'Staging').' but there was an error adding to the pushes table.<br><br>Error:<br>'.$conn->error)
						->set_data('title', $name.' Deleted')
						->set_data('type', 'warning');
				}
			} else {
				$result
					->set_success(true)
					->set_msg($name.' <b>'.$file.'</b> could not be deleted from '.($from == 'stag' ? 'Production' : 'Staging'))
					->set_data('title', $name.' Not Deleted')
					->set_data('type', 'error');
			}

			return $result;
		}

		public static function ignore_file(\mysqli $conn, string $file, directory $dir_stag, directory $dir_prod) : \result {
			$result = new \result;
			$stmt   = "SELECT `id` from `staging_files_ignored` WHERE `file_path` = '$file'";
			$query  = $conn->query($stmt);

			if ($query->num_rows == 0) {

				if ($dir_file = $dir_stag->get_file($file))
					$dir = $dir_stag;
				elseif ($dir_file = $dir_prod->get_file($file))
					$dir = $dir_prod;
				else {
					$result
						->set_success(false)
						->set_msg('File <b>'.$file.'</b> does not exist on either staging or production.')
						->set_data('title', 'Error Ignoring')
						->set_data('type', 'error');

					return $result;
				}

				if ($is_dir = $dir_file->is_dir()) {
					$name = 'Directory';
					$type = 'dir';
				} else {
					$name = 'File';
					$type = 'file';
				}

				$stmt  = "
					INSERT INTO `staging_files_ignored` 
					SET 
						`file_path` = '$file',
						`type` = '$type'";
				$query = $conn->query($stmt);

				if ($query) {
					$result
						->set_success(false)
						->set_msg($name.' <b>'.$file.'</b> ignored.')
						->set_data('title', 'File Ignored')
						->set_data('type', 'success');
				} else {
					$result
						->set_success(false)
						->set_msg($name.' <b>'.$file.'</b> could not be ignored.<br><br>Error:<br>'.$conn->error)
						->set_data('title', 'File Not Ignored')
						->set_data('type', 'error');
				}
			} else {
				$result
					->set_success(false)
					->set_msg('<b>'.$file.'</b> has already been ignored.')
					->set_data('title', 'File Already Ignored')
					->set_data('type', 'error');
			}

			return $result;
		}

		public static function unignore_file(\mysqli $conn, string $file) : \result {
			$result = new \result;
			$stmt = "
				DELETE FROM `staging_files_ignored`
				WHERE `file_path` = '$file'";
			$query = $conn->query($stmt);

			if ($query) {
				$result
					->set_success(true)
					->set_msg('File <b>'.$file.'</b> unignored.')
					->set_data('title', 'File Unignored')
					->set_data('type', 'success');
			} else {
				$result
					->set_success(false)
					->set_msg('File <b>'.$file.'</b> could not be unignored.<br><br>Error:<br>'.$conn->error)
					->set_data('title', 'File Not Unignored')
					->set_data('type', 'error');
			}

			return $result;
		}

		public static function backup_changes(\changes $changes) {
			$changes                = $changes->get();
			$backup_dir_name            = THIS_DIR.'/backup';
			$backup_dir_new_name        = date('m-d-Y_h-i-s');
			$backup_dir_new_full_name   = $backup_dir_name.'/'.$backup_dir_new_name;

			if (!file_exists($backup_dir_name))
				mkdir($backup_dir_name, 0755);

			mkdir($backup_dir_new_full_name, 0755);

			foreach ($changes as $change) {
				$file           = $change->get_object();
				$file_name      = $file->get_path();
				$full_file_name = $file->get_full_path();

				$new_file_name = $backup_dir_new_full_name.'/'.$file_name;

				self::backup_file($full_file_name, $new_file_name);
			}

			shell_exec('tar -C '.dirname($backup_dir_new_full_name).'/ -czvf "'.$backup_dir_new_full_name.'.tgz" "'.$backup_dir_new_name.'" && rm -R "'.$backup_dir_new_full_name.'"');
		}

		public static function backup_file($file_path, $_new_file_name = false) {
			if (!$_new_file_name) {
				$file_name                  = basename($file_path);
				$backup_dir_name            = THIS_DIR.'/backup';
				$backup_dir_new_name        = date('m-d-Y_h-i-s');
				$backup_dir_new_full_name   = $backup_dir_name.'/'.$backup_dir_new_name;
				$new_file_name              = $backup_dir_new_full_name.'/'.$file_name;

				if (!file_exists($backup_dir_name))
					mkdir($backup_dir_name, 0755);

				mkdir($backup_dir_new_full_name, 0755);
			} else
				$new_file_name = $_new_file_name;

			if (is_dir($file_path)) {
				if (!file_exists($new_file_name))
					shell_exec('cp -r "'.$file_path.'" "'.$new_file_name.'"');
			} else
				copy($file_path, $new_file_name);

			if (!$_new_file_name) {
				shell_exec('tar -C '.dirname($backup_dir_new_full_name).'/ -czvf "'.$backup_dir_new_full_name.'.tgz" "'.$backup_dir_new_name.'" && rm -R "'.$backup_dir_new_full_name.'"');
			}
		}

		private static function add_to_push_table(\mysqli &$conn, $file, $type, $from, $deleted = false) {
			$deleted = $deleted ? 'true' : 'false';

			$stmt = "
				INSERT INTO `staging_files_pushed` 
				SET 
					`file_path` = '$file',
				    `type` = '$type',
				    `from` = '$from',
				    `deleted` = $deleted";

			return $conn->query($stmt);
		}
	}