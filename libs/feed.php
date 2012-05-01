<?PHP
date_default_timezone_set('UTC');

include_once "../settings.php";
include_once "../functions.php";
include_once "../user.php";
include_once "../db_mysql.php";

db::connect();

function date3339($timestamp=0)
{
    if (!$timestamp)
    {
        $timestamp = time();
    }

    $date = date('Y-m-d\TH:i:s', $timestamp);

    $matches = array();
    if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches))
    {
        $date .= $matches[1].$matches[2].':'.$matches[3];
    }
    else
    {
        $date .= 'Z';
    }

    return $date;
}

header('Content-type: text/xml');

$query = "SELECT * FROM event_log WHERE type = 'add' AND table_name <> 'screenshot' GROUP BY user_id,table_name,table_id ORDER BY posted DESC LIMIT 25";

$result = db::executeQuery($query);

$content = "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>";
$content .= "<channel>";
$content .= "<atom:link href='http://content.open-ra.org/libs/feed.php' rel='self' type='application/rss+xml' />";
$content .= "<title>OpenRA New Content</title>";
$content .= "<link>http://content.open-ra.org</link>";
$content .= "<description></description>";
$content .= "<lastBuildDate>".date3339()."</lastBuildDate>";


while($row = db::nextRowFromQuery($result))
{
    if (!misc::item_exists($row['table_id'], $row['table_name']))
	continue;
    $articleDate = $row['posted'];
    $articleDateRfc3339 = date3339(strtotime($articleDate));
    $content .= "<item>";
    $content .= "<guid isPermaLink='false'>http://content.open-ra.org/?p=detail&amp;table=".$row['table_name']."&amp;id=".$row['table_id']."</guid>";
    $content .= "<title>";
    $content .= "New ".ucfirst(rtrim($row["table_name"],'s')).": ".misc::item_title_by_uid($row['table_id'], $row['table_name']);
    $content .= "</title>";
    $content .= "<link>http://content.open-ra.org/?p=detail&amp;table=".$row['table_name']."&amp;id=".$row['table_id']."</link>";
    $content .= "<description>";
    $desc = "";
    $res_info = db::executeQuery("SELECT * FROM ".$row['table_name']." WHERE uid = ".$row['table_id']);
    while ($row_info = db::nextRowFromQuery($res_info))
    {
	switch($row['table_name'])
	{
	    case "maps":
		$add_info = $row_info['additional_desc'];
		if ($add_info != '')
		    $add_info = $add_info."&lt;br />";
		$description = $row_info['description'];
		if ($description != '')
		    $description = $row_info['description']."&lt;br />";
		$desc .= $description.$add_info."Mod: ".strtoupper($row_info['g_mod'])."&lt;br />Rev: ".ltrim($row_info["tag"], "r");
		break;
	    case "guides":
		$text = $row_info['html_content'];
		if (strlen($text) > 500)
		    $text = substr($text,0,500);
		$text = str_replace("\\\\\\", "", str_replace("\\r\\n", "", $text));
		$desc .= $text."&lt;br />Type: ".$row_info['guide_type'];
		break;
	    case "units":
		$desc .= $row_info['description']."&lt;br />Type: ".$row_info['type'];
		break;
	    case "replays":
		$query = "SELECT * FROM replay_players WHERE id_replays = ".$row_info["uid"]." ORDER BY team";
		$res_players = db::executeQuery($query);
		$players = "";
		while ($inner_row = db::nextRowFromQuery($res_players))
		{
		    $players .= $inner_row["name"] . ", ";
		}
		if ($players != "")
		    $players = "Players: ".rtrim($players,", ");
		$desc .= "Version: ".$row_info["version"]."&lt;br />Mods: ".$row_info["mods"]."&lt;br />Server name: ".$row_info["server_name"]."&lt;br />".$players;
		break;
	}
    }
    $content .= $desc;
    $content .= "</description>";
    $content .= "<pubDate>".$articleDateRfc3339."</pubDate>";
    $content .= "<author>";
    $content .= "<name>";
    $content .= user::login_by_uid($row['user_id']);
    $content .= "</name>";
    $content .= "</author>";
    $content .= "</item>";
}
$content .= "</channel>";
$content .= "</rss>";
echo $content;

?>
