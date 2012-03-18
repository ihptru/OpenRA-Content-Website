<?PHP
date_default_timezone_set('Europe/Dublin');

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

$query = "SELECT * FROM event_log WHERE type = 'add' GROUP BY user_id,table_name,table_id ORDER BY posted DESC LIMIT 25";

$result = db::executeQuery($query);

$content = "<?xml version='1.0' encoding='UTF-8'?>";

$content .= "<feed xml:lang='en-US' xmlns='http://www.w3.org/2005/Atom'>";
$content .= "<title>OpenRA New Content</title>";
$content .= "<subtitle>The latest content from OpenRA Content website</subtitle>";
$content .= "<link href='http://content.open-ra.org' rel='self'/>";
$content .= "<updated>";
$content .= date3339();
$content .= "</updated>";
$content .= "<author>";
$content .= "<name>ihptru</name>";
$content .= "<email>ihptru@gmail.com</email>";
$content .= "</author>";
$content .= "<id>tag:content.open-ra.org,2012:http://content.open-ra.org/libs/feed.php</id>";

while($row = db::nextRowFromQuery($result))
{
    if (!misc::item_exists($row['table_id'], $row['table_name']))
	continue;
    $articleDate = $row['posted'];
    $articleDateRfc3339 = date3339(strtotime($articleDate));
    $content .= "<entry>";
    $content .= "<title>";
    $content .= "New ".ucfirst(rtrim($row["table_name"],'s')).": ".misc::item_title_by_uid($row['table_id'], $row['table_name']);
    $content .= "</title>";
    $content .= "<link type='text/html' href='http://content.open-ra.org/?p=detail&amp;table=".$row['table_name']."&amp;id=".$row['table_id']."' />";
    $content .= "<id>";
    $content .= "tag:content.open-ra.org,2012:http://content.open-ra.org/?p=detail&amp;table=".$row['table_name']."&amp;id=".$row['table_id'];
    $content .= "</id>";
    $content .= "<updated>";
    $content .= $articleDateRfc3339;
    $content .= "</updated>";
    $content .= "<author>";
    $content .= "<name>";
    $content .= user::login_by_uid($row['user_id']);
    $content .= "</name>";
    $content .= "</author>"; 
    $content .= "<summary>";
    $content .= "";
    $content .= "</summary>";
    $content .= "</entry>";
}                       

$content .= "</feed>";
echo $content;

?>
