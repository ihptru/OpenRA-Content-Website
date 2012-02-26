<?PHP
date_default_timezone_set('Europe/Dublin');

if ( php_sapi_name() != "cli" )
    exit(1);

include_once "settings.php";

if ( $use_db == 'mysql' )
    include_once("db_mysql.php");
elseif ( $use_db == 'pgsql' )
    include_once("db_pgsql.php");

db::connect();
db::clearOldRecords();
db::disconnect();

$fp = fopen(dirname(__FILE__)."/log", "a");
fwrite($fp, date("F j, Y, g:i a")."\n");
fclose($fp);

?>
