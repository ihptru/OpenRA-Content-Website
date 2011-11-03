<?php

if(isset($_COOKIE['language']))
{
	define("USER_LANGUAGE",$_COOKIE['language']);
}
else
{
	define("USER_LANGUAGE","en");
	setcookie("language", USER_LANGUAGE, time()+3600*24*30, "/");
}


if($_GET['lang']=="en")
{
	setcookie("language", "en", time()+3600*24*30, "/");
	header("Location: {$_SERVER['HTTP_REFERER']}");
}
elseif($_GET['lang']=="ru")
{
	setcookie("language", "ru", time()+3600*24*30, "/");
	header("Location: {$_SERVER['HTTP_REFERER']}");
}
elseif($_GET['lang']=="de")
{
	setcookie("language", "de", time()+3600*24*30, "/");
	header("Location: {$_SERVER['HTTP_REFERER']}");
}

require_once("languages/".USER_LANGUAGE.".php");

?>
