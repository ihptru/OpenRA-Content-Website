<?PHP

include_once "../settings.php";

include_once("../db_mysql.php");

db::connect();

header('Content-Type: application/javascript');
header('Access-Control-Allow-Origin: *');

function map_data($result)
{
    $json_result_array = array();
    while ($row = db::nextRowFromQuery($result))
    {
	$query = "SELECT COUNT(*) AS user FROM users";
	$res = db::executeQuery($query);
	$amount = db::nextRowFromQuery($res);
	$rest = 1;
	$arg = ($amount['user']-$amount['user']%20)/20;
	if ($arg > 1) { $rest = $arg; }

	$query = "SELECT * FROM reported WHERE table_name = 'maps' AND table_id = :1";
	$result = db::executeQuery($query, array($row['uid']));
	if (db::num_rows($result) >= $rest)
	    break;	// this map is reported (by required amount of users)
	$map_result = array();
	$map_result['id'] = $row['uid'];
	$map_result['title'] = $row['title'];
	$map_result['description'] = $row['description'];
	$map_result['author'] = $row['author'];
	$map_result['type'] = $row['type'];
	$map_result['players'] = $row['players'];
	$map_result['mod'] = $row['g_mod'];
	$map_result['hash'] = $row['maphash'];
	$map_result['width'] = $row['width'];
	$map_result['height'] = $row['height'];
	$map_result['tileset'] = $row['tileset'];
	$json_result_array[] = $map_result;
	unset($map_result);    
    }
    print(json_encode($json_result_array));
}

function map_link($result)
{
    $json_result_array = array();
    while ($row = db::nextRowFromQuery($result))
    {
	$query = "SELECT * FROM reported WHERE table_name = 'maps' AND table_id = :1";
	$result = db::executeQuery($query, array($row['uid']));
	if (db::num_rows($result) > 0)
	    break;	// this map is reported, do not allow downloading
	$url = array();
	$path = $row["path"];
	$name = explode("-",basename($path),3);
	$url["url"] = "http://".$_SERVER["SERVER_NAME"]."/".$path.$name[2].".oramap";
	$json_result_array[] = $url;
	if (isset($_GET["direct"]))
	{
	    $mimetype = "application/octet-stream";
	    $data = file_get_contents(str_replace(" ", "%20", $url["url"]));
	    $size = strlen($data);
	    header("Content-Disposition: attachment; filename = ".$name[2].".oramap");
	    header("Content-Length: $size");
	    header("Content-Type: $mimetype");
	    echo $data;
	    return;
	}
	print(json_encode($json_result_array));
	return;
    }
    header("HTTP/1.1 404 Not Found");
    return;
}

function result($condition, $value)
{
    for($i=0; $i < strlen($value); $i++)
    {
	if ($value[$i] == "'")
	{
	    //someone is probably trying to sql inject
	    return;
	}
    }
    if ($condition == "maphash")
    {
	$query = "SELECT * FROM maps WHERE maphash = :1
	    ORDER BY uid LIMIT 1
	";
	$result = db::executeQuery($query, array($value));
    }
    elseif ($condition == "title")
    {
	if (isset($_GET["mod"]))
	    $mod = $_GET["mod"];
	else
	    $mod = "";
	$query = "SELECT * FROM maps WHERE lower(title) LIKE lower(:1)
				    AND lower(g_mod) LIKE lower(:2)
	    ORDER BY RAND() LIMIT 1
	";
	$result = db::executeQuery($query, array("%".$value."%", "%".$mod."%"));
    }
    elseif ($condition == "load")
    {
	$query = "SELECT uid,path FROM maps WHERE maphash = :1
		    ORDER BY RAND() LIMIT 1
	";
	$result = db::executeQuery($query, array($value));
	map_link($result);
	return;
    }
    map_data($result);
}

#########################

if (isset($_GET["hash"]))
{
    result("maphash",$_GET["hash"]);
}
elseif (isset($_GET["title"]))
{
    result("title",$_GET["title"]);
}
elseif (isset($_GET["load"]))
{
    result("load",$_GET["load"]);
}

?>
