<?PHP

class mail
{
    public static function mbox()
    {
	if (!user::online())
	    return;
	if (!isset($_GET["m"]))
	    return;
	echo "<div style='margin-top:-33px;margin-left:-29px;'>";
	echo "<p><a href='?p=mail&m=inbox' class='".mail::current_menu("inbox")."' style='margin-left:-1px; width: 106px; text-align:center;'>Inbox</a></p>";
	echo "<p><a href='?p=mail&m=sent' class='".mail::current_menu("sent")."'>Sent messages</a></p>";
	echo "<p><a href='?p=mail&m=compose' class='".mail::current_menu("compose")."'>Compose message</a></p>";
	echo "<p><a href='?p=mail&m=find_member' class='".mail::current_menu("find_member")."'>Find a member</a></p>";
	echo "<p><a href='?p=mail&m=find_message' class='".mail::current_menu("find_message")."' style='width:auto;'>Find a message</a></p>";
	echo "</div>";
	
	switch($_GET["m"])
	{
	    case "inbox":
		if (isset($_GET["w"]))
		{
		    $msg_id = -1;
		    $msg_id = $_GET["w"];
		    $query = "SELECT * FROM pm WHERE uid = :1";
		    $msg_result = db::executeQuery($query, array($msg_id));
		    while ($row_msg = db::nextRowFromQuery($msg_result))
		    {
			if ($row_msg["to_user_id"] != user::uid())
			    break;	// can't read messages sent to anyone else
			echo "<table>";
			echo "<tr><td>Message subect:</td><td>".str_replace("\\\\\\", "", $row_msg["title"])."</td></tr>";
			echo "<tr><td>From:</td><td><a href='?profile=".$row_msg["from_user_id"]."'>".user::login_by_uid($row_msg["from_user_id"])."</a></td></tr>";
			echo "<tr><td>Sent:</td><td>".date("D M j, Y g:i a", mail::convert_timestamp($row_msg["posted"]))."</td></tr>";
			echo "<tr><td>To:</td><td><a href='?profile=".$row_msg["to_user_id"]."'>".user::login_by_uid($row_msg["to_user_id"])."</a></td></tr>";
			echo "</table>";
			echo "<table><tr><td>".str_replace("\\", "", str_replace('\r\n', "<br />", $row_msg["content"]))."</td></tr></table>";
			echo "<p><a href='?p=mail&m=compose&to=".$row_msg["from_user_id"]."&title=RE:%20".$row_msg["title"]."' class='more-link-selected'>Reply</a></p>";
			$q = "SELECT * FROM reported WHERE table_name = 'pm' AND table_id = :1 AND user_id = :2";
			$rep_result = db::executeQuery($q, array($row_msg["uid"], user::uid()));
			while ($row_rep = db::nextRowFromQuery($rep_result))
			    echo "<p><a href='?unreport_msg&table_id=".$row_msg["uid"]."&user_id=".user::uid()."' class='more-link-selected'>Cancel Report</a></p>";
		    }
		    break;
		}
		$filters = "";
		$filter_checked = "";
		$isread_filter = mail::inbox_filter();
		if ($isread_filter == true)
		{
		    $filters = " AND isread = 0 ";
		    $filter_checked = "checked";
		}
		
		//paging related
		$pointer = "#messages";
		$table = "";
		$maxItemsPerPage = 10;
		if(isset($_GET["current_messages_page_".$table]))
		    $current = $_GET["current_messages_page_".$table];
		else
		    $current = 1;

		echo "<a name='messages'></a><table style='min-width: 590px; margin-left: -10px; margin-top: 0; vertical-align: top;'><tr>";
		echo "<th style='text-align:center;min-width:200px;'>Subject</th><th style='text-align:center;'>Author</th><th style='text-align:center;'>Sent</th><th style='width:15px;text-align:center;'>Mark</th></tr>";
		echo "<form method=POST>";
		$query = "SELECT * FROM pm WHERE to_user_id = :1 ".$filters." ORDER BY posted DESC";
		$result = db::executeQuery($query, array(user::uid()));
		$num_rows = db::num_rows($result);
		$i = 0;
		while ($row = db::nextRowFromQuery($result))
		{
		    if( !($i >= ($current-1) * $maxItemsPerPage && $i < $current * $maxItemsPerPage ) )
		    {
			$i++;
			continue;
		    }
		    $i++;

		    $read = "";
		    if ($row["isread"] == 0)
			$read = "style='color:#ff0000;' title='unread'";
		    echo "<tr><td><a href='?p=mail&m=inbox&w=".$row["uid"]."' ".$read.">".str_replace("\\\\\\", "", $row["title"])."</a></td><td style='text-align:center;'><a href='?profile=".$row["from_user_id"]."'>".user::login_by_uid($row["from_user_id"])."</a></td><td style='text-align:center;'>".date("D M j, Y g:i a", mail::convert_timestamp($row["posted"]))."</td><td style='text-align:center;'><input type='checkbox' name='mark_".$row["uid"]."' value='".$row["uid"]."'></td></tr>";
		}
		echo "</table>";
		
		if ($num_rows != 0)
		{
		    $nrOfPages = floor(($num_rows-0.01) / $maxItemsPerPage) + 1;
		    $gets = "";
		    $pages = "<table><tr><td>";
		    $keys = array_keys($_GET);
		    foreach($keys as $key)
			if($key != "current_messages_page_".$table)
			    $gets .= "&" . $key . "=" . $_GET[$key];
		    for($i = 1; $i < $nrOfPages+1; $i++)
		    {
			$pages .= misc::paging($nrOfPages, $i, $current, $gets, $table, "messages", $pointer);
		    }
		    $pages .= "</td></tr></table>";
		    
		    if ($nrOfPages == 1)
			$pages = "";
	    
		    $pages = preg_replace("/(\.\.\.)+/", " ... ", $pages);
		    echo $pages;
		}
		
		echo "<table style='min-width:300px;'>";
		if ($num_rows > 0)
		    echo "<tr><td>With marked: <input type=hidden name='any_action'><input type=submit name='delete_msg' value='Delete'>   <input type=submit name='report_msg' value='Report'>   <input type=submit name='setasread_msg' value='Set as Read'></td><td>mark all  <input type='checkbox' name='sAll' onclick='selectAllcheckboxes(this)'/></td></tr></form>";
		echo "<tr><td><form method=POST action=''><label>show only unread messages </label><input type='checkbox' name='msg_unread_only_filter' ".$filter_checked."><input type=hidden name='apply_filter_type' value='msg_filter'><input type=hidden name='apply_filter'> <input style='float:right' type='submit' name='submit' value='Update'></form></td></tr></table>";
		
		// work with checked messages
		if (isset($_POST["any_action"]))
		{
		    $any_action = false;
		    foreach ($_POST as $key => $value)
		    {
			if (substr($key, 0, 4) == "mark")
			{
			    if (isset($_POST["delete_msg"]))
			    {
				$q = "SELECT * FROM pm WHERE uid = :1";
				$res_d = db::executeQuery($q, array($value));
				$row_d = db::nextRowFromQuery($res_d);
				$q = "INSERT INTO pm_trash
					(uid,from_user_id,to_user_id,title,content,isread,posted)
					VALUES
					(:1,:2,:3,:4,:5,:6,:7);
				";
				db::executeQuery($q, array($row_d["uid"], $row_d["from_user_id"], $row_d["to_user_id"], $row_d["title"], $row_d["content"], $row_d["isread"], $row_d["posted"]));
				$query = "DELETE FROM pm WHERE uid = :1";
				db::executeQuery($query, array($value));
				db::executeQuery("DELETE FROM reported WHERE table_name = 'pm' AND table_id = :1", array($value));
				$any_action = true;
			    }
			    else if (isset($_POST["setasread_msg"]))
			    {
				$query = "UPDATE pm SET isread = 1 WHERE uid = :1";
				db::executeQuery($query, array($value));
				$any_action = true;
			    }
			    else if (isset($_POST["report_msg"]))
			    {
				$query = "SELECT * FROM reported WHERE table_name = 'pm' AND table_id = :1";
				$res = db::executeQuery($query, array($value));
				if (db::num_rows($res) == 0)
				{
				    $query = "INSERT INTO reported
					    (table_name,table_id,user_id)
					    VALUES
					    (:1,:2,:3)
				    ";
				    db::executeQuery($query, array("pm", $value, user::uid()));
				    $any_action = true;
				}
			    }
			}
		    }
		    if ($any_action = true)
			echo "<script>location.href='http://".$_SERVER["HTTP_HOST"]."/?p=mail&m=inbox'</script>";
		}
		break;
	    case "sent":
		if (isset($_GET["w"]))
		{
		    $msg_id = -1;
		    $msg_id = $_GET["w"];
		    $query = "SELECT * FROM pm WHERE uid = :1";
		    $msg_result = db::executeQuery($query, array($msg_id));
		    if (db::num_rows($msg_result) == 0)
		    {
			$query = "SELECT * FROM pm_trash WHERE uid = :1";
			$msg_result = db::executeQuery($query, array($msg_id));
		    }
		    while ($row_msg = db::nextRowFromQuery($msg_result))
		    {
			if ($row_msg["from_user_id"] != user::uid())
			    break;	// can't read messages sent by anyone else
			echo "<table>";
			echo "<tr><td>Message subect:</td><td>".str_replace("\\\\\\", "", $row_msg["title"])."</td></tr>";
			echo "<tr><td>From:</td><td><a href='?profile=".$row_msg["from_user_id"]."'>".user::login_by_uid($row_msg["from_user_id"])."</a></td></tr>";
			echo "<tr><td>Sent:</td><td>".date("D M j, Y g:i a", mail::convert_timestamp($row_msg["posted"]))."</td></tr>";
			echo "<tr><td>To:</td><td><a href='?profile=".$row_msg["to_user_id"]."'>".user::login_by_uid($row_msg["to_user_id"])."</a></td></tr>";
			echo "</table>";
			echo "<table><tr><td>".str_replace("\\", "", str_replace('\r\n', "<br />", $row_msg["content"]))."</td></tr></table>";
		    }
		    break;
		}
		echo "<table style='min-width: 590px; margin-left: -10px; margin-top: 0; vertical-align: top;'><tr>";
		echo "<th style='text-align:center;min-width:200px;'>Subject</th><th style='text-align:center;'>Recipient</th><th style='text-align:center;'>Sent</th></tr>";
		$query = "SELECT * FROM pm WHERE from_user_id = :1 UNION SELECT * FROM pm_trash WHERE from_user_id = :2 ORDER BY posted DESC";
		$result = db::executeQuery($query, array(user::uid(), user::uid()));
		while ($row = db::nextRowFromQuery($result))
		{
		    $read = "";
		    if ($row["isread"] == 0)
			$read = "style='color:#ff0000;' title='recipient did not read this message'";
		    echo "<tr><td><a href='?p=mail&m=sent&w=".$row["uid"]."' ".$read.">".str_replace("\\\\\\", "", $row["title"])."</a></td><td style='text-align:center;'><a href='?profile=".$row["to_user_id"]."'>".user::login_by_uid($row["to_user_id"])."</a></td><td style='text-align:center;'>".date("D M j, Y g:i a", mail::convert_timestamp($row["posted"]))."</td></tr>";
		}
		echo "</table>";
		break;
	    case "compose":
		if (!user::online())
		    break;
		if (!isset($_GET["to"]))
		    echo "<script>location.href='http://".$_SERVER["HTTP_HOST"]."/?p=mail&m=find_member'</script>";
		$to_id = -1;
		$to_id = $_GET["to"];
		if (!user::exists($to_id))
		    break;
		if ($to_id == user::uid())
		    break;

		$title = "";
		if (isset($_GET["title"]))
		    $title = $_GET["title"];
		echo "<table style='margin-left: -10px;'><form method=POST action='' name='compose'>";
		echo "<tr><td><b>To:</b></td><td><a href='?profile=".$to_id."'>".user::login_by_uid($to_id)."</a></td></tr>";
		echo "<tr><td><b>Subject:</b></td><td><input type='text' size='40' name='msg_title' value='".$title."'></td></tr>";
		echo "<tr><td style='margin-top:0; vertical-align:top;'><b>Message body:</b><br />Enter your message here, it may contain no more than 5000 characters.</td><td><textarea id='message' wrap='physical' name='msg_message' rows='10' cols='20' tabindex='4' onKeyDown='textCounter(document.compose.msg_message,document.compose.remLen,5000)' onKeyUp='textCounter(document.compose.msg_message,document.compose.remLen,5000)'></textarea><br /><input readonly type='text' name='remLen' size='4' maxlength='4' value='5000'> characters left<br /><input type=submit value=Submit></td></tr>";
		echo "<input type=hidden name='to_user_id' value='".$to_id."'></form></table>";
		break;
	    case "find_member":
		echo "<table><tr><td><b>Enter username:</b></td><td><form method=POST action=''><input type='text' name='username' size=30></form></td></tr></table>";
		if (isset($_POST["username"]))
		{
		    if (trim($_POST["username"]) == "")
			break;
		    $query = "SELECT uid,login FROM users WHERE login LIKE (:1)";
		    $res = db::executeQuery($query, array("%".$_POST["username"]."%"));
		    if (db::num_rows($res) > 0)
			echo "<table><tr><td><b>Choose user:</b></td></tr>";
		    while ($row_user = db::nextRowFromQuery($res))
		    {
			echo "<tr><td><a href='?p=mail&m=compose&to=".$row_user["uid"]."'>".$row_user["login"]."</a> (<a href='?profile=".$row_user["uid"]."' target=_blank>profile</a>)</td></tr>";
		    }
		    if (db::num_rows($res) > 0)
			echo "</table>";
		}
		break;
	    case "find_message":
		echo "<table><tr><td><b>Enter a part of the subject:</b></td><td><form method=POST action=''><input type='text' name='title' size=30></form></td></tr></table>";
		if (isset($_POST["title"]))
		{
		    if (trim($_POST["title"]) == "")
			break;
		    $title = $_POST["title"];
		    $query = "SELECT * FROM pm WHERE UPPER(title) LIKE UPPER(:1) AND ( from_user_id = :2 OR to_user_id = :3 )
				UNION
				SELECT * FROM pm_trash WHERE UPPER(title) LIKE UPPER(:4) AND from_user_id = :5";
		    $res = db::executeQuery($query, array("%".$title."%", user::uid(), user::uid(), "%".$title."%", user::uid()));
		    if (db::num_rows($res) > 0)
			echo "<table><tr><td><b>Found messages:</b></td></tr>";
		    while ($row_msg = db::nextRowFromQuery($res))
		    {
			if ($row_msg["from_user_id"] == user::uid())
			    $page = "sent";
			else
			    $page = "inbox";
			echo "<tr><td><a href='?p=mail&m=".$page."&w=".$row_msg["uid"]."'>".$row_msg["title"]."</a></td></tr>";
		    }
		    if (db::num_rows($res) > 0)
			echo "</table>";
		}
		break;
	}
    }
    
    public static function parse_message($message)
    {
	$str = array("<" ,">");
	$to_str = array("&lt;", "&gt;");
	$replace_message = trim(str_replace($str,$to_str,$message));
	return preg_replace("#(https?|ftp)://\S+[^\s.,> )\];'\"!?]#", '<a href="\\0">\\0</a>', $replace_message);
    }
    
    public static function current_menu($menu)
    {
	if (isset($_GET["m"]))
	    if ($menu == $_GET["m"])
		return "more-link-selected";
	return "more-link";
    }
    
    public static function convert_timestamp($str)
    {
	list($date, $time) = explode(' ', $str);
	list($year, $month, $day) = explode('-', $date);
	list($hour, $minute, $second) = explode(':', $time);

	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
	return $timestamp;	// return type: int
    }
    
    public static function inbox_filter()
    {
	
	if (isset($_POST["msg_unread_only_filter"]))
	    return true;
	elseif (isset($_COOKIE["msg_unread_only_filter"]))
	    return true;
	return false;
    }
}

?>
