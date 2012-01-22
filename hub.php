<?PHP
session_start();

include_once "settings.php";

include_once "functions.php";

include_once "user.php";

############## database class ##################
if ( $use_db == 'mysql' )
{
    include_once("db_mysql.php");
}
elseif ( $use_db == 'pgsql' )
{
    include_once("db_pgsql.php");
}
################################################

############# prepare database #################
db::connect();
if (!db::check())
{
    db::setup();
}
################################################

########## check and set language ##############
if(isset($_COOKIE['language']))
{
    define("USER_LANGUAGE",$_COOKIE['language']);
}
else
{
    define("USER_LANGUAGE","en");
    setcookie("language", USER_LANGUAGE, time()+3600*24*360, "/");
}

if(isset($_GET['lang']))
{
    if($_GET['lang']=="en")
    {
	setcookie("language", "en", time()+3600*24*360, "/");
	header("Location: {$_SERVER['HTTP_REFERER']}");
    }
    elseif($_GET['lang']=="ru")
    {
	setcookie("language", "ru", time()+3600*24*360, "/");
	header("Location: {$_SERVER['HTTP_REFERER']}");
    }
    elseif($_GET['lang']=="de")
    {
	setcookie("language", "de", time()+3600*24*360, "/");
	header("Location: {$_SERVER['HTTP_REFERER']}");
    }
    elseif($_GET['lang']=="sv")
    {
	setcookie("language", "sv", time()+3600*24*360, "/");
	header("Location: {$_SERVER['HTTP_REFERER']}");
    }
    else
    {
	setcookie("language", "en", time()+3600*24*360, "/");
	header("Location: {$_SERVER['HTTP_REFERER']}");
    }
}

require_once("languages/".USER_LANGUAGE.".php");
################################################

###
# for cookie and db modifications in case of session is destroyed and `remember me` is set
user::start_cookie_remember();
user::online();

user::check_logout();
user::login();
###

?>
