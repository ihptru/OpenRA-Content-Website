<?PHP
session_start();

include_once "settings.php";

include_once "user.php";

include_once "functions.php";

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
	db::clear();
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
}

require_once("languages/".USER_LANGUAGE.".php");
################################################

?>
