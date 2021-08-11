<?
	function install_the_tables(mysqli $conn, $tables) {

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