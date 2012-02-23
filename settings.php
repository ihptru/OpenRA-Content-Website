<?PHP
ini_set("display_errors","1");
ini_set("display_startup_errors","1");
ini_set("error_reporting", E_ALL);

define("DB_HOST","localhost");
define("DB_USERNAME","");
define("DB_PASSWORD","");
define("DB_DATABASE","");

### website's path
function site_path($path)
{
    if (trim($path) == "")
	return;
    $last = $path[strlen($path)-1];
    if ($last == DIRECTORY_SEPARATOR)
    {
	return $path;
    }
    else
    {
	return $path . DIRECTORY_SEPARATOR;
    }
}

define("WEBSITE_PATH", site_path($_SERVER['DOCUMENT_ROOT']));	# with / at the end

$use_db = "mysql";	//mysql or pgsql

?>
