<?
	global $db;

	const THIS_DIR = __DIR__;
	define('THIS_URL', '?a='.$_GET['a']);
	define('ACCOUNT_ROOT', dirname($_SERVER['DOCUMENT_ROOT']));

	const PATH_PROD = ACCOUNT_ROOT.'/'.DIR_PROD;
	const PATH_STAG = ACCOUNT_ROOT.'/'.DIR_STAG;

	if (!isset($db))
		require_once BASE_DIR.'/sources/init/db.config.php';

	$conn = new mysqli($db['host'], $db['user'], $db['pass'], $db['db']);

	if (mysqli_connect_error())
		die('Connection could not be made. Error: '.$conn->connect_error);

	define('URL', $conn->query("SELECT `url` FROM `settings` LIMIT 1")->fetch_object()->url);

	const THIS_URL_FULL = URL.'/admin/'.THIS_URL;
	define('THIS_URL_DIR', URL.(str_replace(BASE_DIR, '', THIS_DIR)));