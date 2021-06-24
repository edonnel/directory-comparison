<?
	$files_to_exclude   = array();
	$ignored_files      = \directory\deployment::get_ignored_files($conn);

	foreach ($ignored_files as $file_to_exclude)
		$files_to_exclude[] = $file_to_exclude['file_path'];

	$dir_stag = new directory\directory(PATH_STAG, $files_to_exclude);
	$dir_prod = new directory\directory(PATH_PROD, $files_to_exclude);

	$ignored_files = \directory\deployment::get_ignored_files($conn, PATH_STAG, PATH_PROD);

	$all_files = directory\directory::combine_directories($dir_stag, $dir_prod);