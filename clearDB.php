<?PHP

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

?>
