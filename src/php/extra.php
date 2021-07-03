<?
	function db_cred_callback() {
		global $db;

		if (!isset($db))
			require_once DOCUMENT_ROOT.'/sources/init/db.config.php';

		return array(
			'host' => $db['host'],
			'user' => $db['user'],
			'pass' => $db['pass'],
			'name' => $db['db'],
		);
	}