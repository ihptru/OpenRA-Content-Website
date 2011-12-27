<?PHP
ini_set("display_errors","0");
ini_set("display_startup_errors","0");
ini_set("error_reporting", E_ALL);

define("DB_HOST","localhost");
define("DB_USERNAME","oramod");
define("DB_PASSWORD","iequeiR6");
define("DB_DATABASE","oramod");

define("WEBSITE_PATH", $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);	# with / at the end

$use_db = "mysql";	//mysql or pgsql

?>
