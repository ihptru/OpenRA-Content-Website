<?PHP
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

session_start();

include_once "settings.php";

include_once "functions.php";

include_once "user.php";

include_once "mail.php";

############## database class ##################
include_once("db_mysql.php");
################################################

############# prepare database #################
db::connect();
if (!db::check())
{
    db::setup();
}
################################################

###
# for cookie and db modifications in case of session is destroyed and `remember me` is set
user::online();

user::check_logout();
user::login();
###

?>
