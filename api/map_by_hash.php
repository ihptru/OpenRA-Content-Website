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

function result()
{
    if (isset($_GET["hash"]))
    {
	for($i=0; $i < strlen($_GET["hash"]); $i++)
	{
	    if ($_GET["hash"][$i] == "'")
	    {
		return;
	    }
	}

	$found = strpos($_GET["hash"], "\'");
	if ($found === true)
	{
	    //someone trying to sql inject
	    echo "error";
	    return;
	}
	$query = "SELECT * FROM maps WHERE maphash = '".$_GET["hash"]."'
		ORDER BY uid LIMIT 1
	";
	$result = db::executeQuery($query);
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
}
result();

?>
