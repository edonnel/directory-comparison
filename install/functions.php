<?
	namespace directory_comparison;

	function install_the_tables(\mysqli $conn, $tables) {

		foreach ($tables as $table) {

			$table_name = $table['name'];

			$stmt   = "
				SELECT * 
				FROM INFORMATION_SCHEMA.TABLES 
				WHERE 
			        TABLE_SCHEMA = SCHEMA() AND
				    TABLE_NAME = '$table_name'";
			$query          = $conn->query($stmt);
			$table_exists   = $query->num_rows > 0;

			$stmt = '';

			if (!$table_exists)
				$stmt .= "CREATE TABLE `$table_name` (`id` int PRIMARY KEY AUTO_INCREMENT);";

			foreach ($table['columns'] as $column_name => $column) {
				$column_type    = $column['type'];
				$column_null    = $column['null'] ? '' : 'NOT NULL';
				$column_primary = $column['primary'] ? 'PRIMARY KEY' : '';
				$column_extra   = $column['extra'];

				// columns exists?
				$stmt_2 = "
					SELECT 1
					FROM INFORMATION_SCHEMA.COLUMNS 
					WHERE 
				        TABLE_SCHEMA = SCHEMA() AND
					    TABLE_NAME = '$table_name' AND
					    COLUMN_NAME = '$column_name'";
				$query  = $conn->query($stmt_2);
				$exists = $query->num_rows > 0;

				if ($exists)
					$action = "MODIFY";
				else
					$action = "ADD COLUMN";

				$stmt .= "ALTER TABLE `$table_name` $action `$column_name` $column_type $column_null $column_primary $column_extra;"."\r";

			}

			if (!$table_exists && isset($table['insert'])) {
				foreach ($table['insert'] as $insert) {
					$stmt .= "INSERT INTO `$table_name` SET ";

					$set = array();

					foreach ($insert as $column_name => $value)
						$set[] = "`$column_name` = '$value'";

					$stmt .= implode(',', $set).";"."\r";
				}
			}

			$query = $conn->multi_query($stmt);

			if ($query) {
				do {
					if (!$conn->more_results())
						break;

					if (!$conn->next_result())
						die($conn->error);
				} while (true);
			} else
				die($conn->error);
		}
	}

	function download_github_repo($user, $repo, $branch, $dest) {
		// get the repo zip
		$file_get_contents = file_get_contents("https://github.com/$user/$repo/archive/$branch.zip");

		if (!$file_get_contents)
			return false;

		// define the zip name
		$file_name = $repo.'_temp.zip';
		$file_path = $dest.'/'.$file_name;

		// create the dest dir if it doesn't exist already
		if (!file_exists($dest)) {
			$mkdir = mkdir($dest, 0755, true);

			if (!$mkdir)
				return false;
		}

		// write the repo zip to the server
		$file_put_contents = file_put_contents($file_path, $file_get_contents);

		if (!$file_put_contents)
			return false;

		// unzip the zip
		$zip        = new \ZipArchive;
		$zip_open   = $zip->open($file_path);

		if ($zip_open === true) {
			$zip->extractTo($dest);
			$zip->close();

			// delete the zip
			unlink($file_path);

			// there will be one folder called {repo}-{$branch}
			$new_file_path = "$dest/$repo-$branch";

			// get files and folders
			$scandir = scandir($new_file_path);

			// move everything in that folder up one
			foreach ($scandir as $file) {
				if ($file != '.' && $file != '..') {
					$rmove = \directory_comparison\deployment::rmove($new_file_path.'/'.$file, $dest);

					if (!$rmove)
						return false;
				}
			}

			// delete empty dir
			rmdir($new_file_path);
		} else {
			unlink($file_path);

			return false;
		}

		return true;
	}

	function install_the_submodules() {
		$dir_changes = THIS_DIR.'/lib/changes';
		$dir_result = THIS_DIR.'/lib/result';

		if (!file_exists($dir_changes) || \directory_comparison\deployment::is_dir_empty($dir_changes)) {
			$download = download_github_repo('edonnel', 'changes.class.php', 'main', $dir_changes);

			if (!$download)
				return false;
		}

		if (!file_exists($dir_result) || \directory_comparison\deployment::is_dir_empty($dir_result)) {
			$download = download_github_repo('edonnel', 'result.class.php', 'main', $dir_result);

			if (!$download)
				return false;
		}

		return true;
	}