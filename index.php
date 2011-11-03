<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<head></head><title>OpenRA Content Website</title>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/style.css\" /></head>";

include_once("config.php");

echo $lang['test'];

include("footer.php");
?>
