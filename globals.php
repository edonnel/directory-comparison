<?
	global $db;
	const THIS_DIR = __DIR__;
	define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
	define('REAL_ROOT', realpath($_SERVER['DOCUMENT_ROOT']));
	define('THIS_URL', '?a='.$_GET['a']);
	define('ACCOUNT_ROOT', dirname(DOCUMENT_ROOT));

	const PATH_PROD = ACCOUNT_ROOT.'/'.DIR_PROD;
	const PATH_STAG = ACCOUNT_ROOT.'/'.DIR_STAG;

	if (!isset($db))
		require_once DOCUMENT_ROOT.'/sources/init/db.config.php';

	$conn = new mysqli($db['host'], $db['user'], $db['pass'], $db['db']);

	if (mysqli_connect_error())
		die('Connection could not be made. Error: '.$conn->connect_error);

	define('URL', $conn->query("SELECT `url` FROM `settings` LIMIT 1")->fetch_object()->url);

	const THIS_URL_FULL = URL.'/admin/'.THIS_URL;
	define('THIS_URL_DIR', URL.(str_replace(REAL_ROOT, '', THIS_DIR)));