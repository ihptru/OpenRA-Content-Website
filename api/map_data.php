<?PHP

include_once "../settings.php";

if ( $use_db == 'mysql' )
{
    include_once("../db_mysql.php");
}
elseif ( $use_db == 'pgsql' )
{
    include_once("../db_pgsql.php");
}

db::connect();


function map_data($result)
{
    $json_result_array = array();
    while ($row = db::nextRowFromQuery($result))
    {
	$map_result = array();
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
	$query = "SELECT * FROM maps WHERE lower(".$condition.") LIKE lower('%".$value."%')
	    ORDER BY RAND() LIMIT 1
	";
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


?>