<?PHP

include_once "../settings.php";

if ( $use_db == 'mysql' )
    include_once("../db_mysql.php");
elseif ( $use_db == 'pgsql' )
    include_once("../db_pgsql.php");

db::connect();

function map_data($result)
{
    $json_result_array = array();
    while ($row = db::nextRowFromQuery($result))
    {
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
    while ($row = db::nextRowFromQuery($result))
    {
	$json_result_array = array();
	$url = array();
	$path = $row["path"];
	$name = explode("-",basename($path),3);
	$url["url"] = "http://".$_SERVER["SERVER_NAME"]."/".$path.$name[2].".oramap";
	$json_result_array[] = $url;
	if (isset($_GET["direct"]))
	{
	    $mimetype = "application/octet-stream";
	    $data = file_get_contents($url["url"]);
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
    echo "-1";
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
	$query = "SELECT * FROM maps WHERE ".$condition." = '".$value."'
	    ORDER BY uid LIMIT 1
	";
    }
    elseif ($condition == "title")
    {
	if (isset($_GET["mod"]))
	{
	    $mod = $_GET["mod"];
	}
	else
	{
	    $mod = "";
	}
	$query = "SELECT * FROM maps WHERE lower(".$condition.") LIKE lower('%".$value."%')
				    AND lower(g_mod) LIKE lower('%".$mod."%')
	    ORDER BY RAND() LIMIT 1
	";
    }
    elseif ($condition == "load")
    {
	$query = "SELECT path FROM maps WHERE maphash = '".$value."'
		    ORDER BY RAND() LIMIT 1
	";
	map_link(db::executeQuery($query));
	return;
    }
    $result = db::executeQuery($query);
    map_data($result);
}



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
