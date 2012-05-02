<?PHP
#  0 - All fine for the request
# -1 - Login failed

include_once "../settings.php";

include_once "../db_mysql.php";

include_once "../user.php";

include_once "../functions.php";

db::connect();

header( 'Content-type: text/plain' );

function check_login($login, $pass)
{
    $query = "SELECT login,pass FROM users WHERE login = :1 AND pass = :2";
    $result = db::executeQuery($query, array($login, $pass));
    while (db::nextRowFromQuery($result))
    {
	return "0";
    }
    return "-1";
}


if (isset($_GET["login"]) and isset($_GET["pass"]))
{
    echo check_login($_GET["login"], $_GET["pass"]);
    exit();
}

if(isset($_FILES["map_upload"]["name"]))
{
    if (isset($_POST["login"]) and isset($_POST["pass"]))
    {
	$res = check_login($_POST["login"], $_POST["pass"]);
	if ($res == "0")
	{
	    $query = "SELECT uid FROM users WHERE login = :1 AND pass = :2";
	    $result = db::executeQuery($query, array($_POST["login"], $_POST["pass"]));
	    $user_id = "";
	    while ($row = db::nextRowFromQuery($result))
	    {
		$user_id = $row["uid"];
	    }
	    if ($user_id == "")
	    {
		echo "-1";
		exit();
	    }
	    $p_id = "0";
	    if (isset($_POST["p_id"]))
		$p_id = $_POST["p_id"];
	    chdir(WEBSITE_PATH);
	    $return = upload::upload_oramap($_POST["login"], $p_id, $user_id);
	    echo $return;
	    exit();
	}
	else
	{
	    echo "-1";
	    exit();
	}
    }
    exit();
}

if (isset($_GET["list"]) and isset($_GET["vlogin"]) and isset($_GET["vpass"]))
{
    $res = check_login($_GET["vlogin"], $_GET["vpass"]);
    if ($res == "0")
    {
	$query = "SELECT uid FROM users WHERE login = :1 AND pass = :2";
	$result = db::executeQuery($query, array($_GET["vlogin"], $_GET["vpass"]));
	$user_id = "";
	while ($row = db::nextRowFromQuery($result))
	{
	    $user_id = $row["uid"];
	}
	if ($user_id == "")
	{
	    echo "-1";
	    exit();
	}
	
	$query = "SELECT * FROM maps WHERE user_id = :1 AND n_ver = 0";
	$result = db::executeQuery($query, array($user_id));
	$json_result_array = array();
	while ($row = db::nextRowFromQuery($result))
	{
	    $map_result = array();
	    $map_result['id'] = $row['uid'];
	    $map_result['title'] = $row['title'];
	    $map_result['hash'] = $row['maphash'];
	    $json_result_array[] = $map_result;
	    unset($map_result);
	}
	print(json_encode($json_result_array));
	exit();
    }
    echo "-1";
    exit();
}

echo "-1";

?>
