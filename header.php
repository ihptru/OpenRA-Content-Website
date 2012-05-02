<?PHP

class header
{
    public static function main()
    {
	header::comment();
	header::delete_comment();
	header::upload_guide();
	header::edit_guide();
	header::fav();
	header::delete_item();
	header::apply_filter();
	header::following();
	header::edit_item_info();
	header::upload_screenshot();
	header::pm();
    }

    public static function pageTitle()
    {
	$title = "OpenRA - Resources";
	if (count($_GET) == 0)
	    return $title;
	else
	{
	    if (isset($_GET["action"]))
	    {
		if ($_GET["action"] == "show_user_followed")
		    return $title . " | User Followed By";
		if ($_GET["action"] == "show_user_follow")
		    return $title . " | User Follows";
		if ($_GET["action"] == "user_items" and isset($_GET["id"]) and isset($_GET["table"]))
		{
		    $what = $_GET["table"];
		    if ($what == "fav_item")
			$what = "favorited items";
		    return $title . " | " . user::login_by_uid($_GET["id"]) . "'s " . ucfirst($what);
		}
		if ($_GET["action"] == "show_favorited" and isset($_GET["favorited_id"]))
		    return $title . " | " . user::login_by_uid($_GET["favorited_id"]) . "'s favorited items";
		if ($_GET["action"] == "display_faction" and isset($_GET["faction"]))
		    return $title . " | Faction - " . strtoupper($_GET["faction"]);
		if ($_GET["action"] == "myunits")
		    if (user::online())
			return $title . " | My Units";
		if ($_GET["action"] == "mymaps")
		    if (user::online())
			return $title . " | My Maps";
		if ($_GET["action"] == "myguides")
		    if (user::online())
			return $title . " | My Guides";
		if ($_GET["action"] == "myreplays")
		    if (user::online())
			return $title . " | My Replays";
		if ($_GET["action"] == "new_version")
		    if (user::online())
			return $title . " | Upload a new version of \"".misc::item_title_by_uid($_GET["id"], "maps")."\"";
		if ($_GET["action"] == "versions" and isset($_GET["id"]))
		    return $title . " | Versions of the map: " . misc::item_title_by_uid($_GET["id"], "maps");
	    }
	    if (isset($_GET["p"]))
	    {
		$title = $title . " | " . ucfirst($_GET["p"]);
		if (isset($_GET["profile"]))
		    return $title . " - " . user::login_by_uid($_GET["profile"]);
		if (isset($_GET["edit"]))
		    return $title . " - Edit";
		if ($_GET["p"] == "detail" and isset($_GET["id"]) and isset($_GET["table"]))
		    return $title . " - " . ucfirst(rtrim($_GET["table"],"s")) . " - " . misc::item_title_by_uid($_GET["id"], $_GET["table"]);
		if ($_GET["p"] == "profile" and !user::online())
		    return "OpenRA - Resources";
		return $title;
	    }
	    if (isset($_GET["register"]))
		return $title . " | Registration";
	    if (isset($_GET["recover"]))
	    {
		if (isset($_GET["recover_pass"]))
		    return $title . " | Recover password";
		if (isset($_GET["recover_user"]))
		    return $title . " | Recover username";
		return $title . " | Recover Account Information";
	    }
	}
	return $title;
    }

    public static function comment()
    {
	if( isset($_POST['message']) and isset($_GET['table']) and isset($_GET['id']) )
	{
	    if (user::online())
	    {
		if (trim($_POST['message']) != "")
		{
		    $table = $_GET['table'];
		    $id = $_GET['id'];
		    $query = "SELECT uid FROM $table WHERE uid = :1";
		    $result = db::executeQuery($query, array($id));
		    while (db::nextRowFromQuery($result))
		    {
			db::executeQuery( "INSERT INTO comments (title, content, user_id, table_id, table_name) VALUES (:1,:2,:3,:4,:5)", array("", $_POST['message'], user::uid(), $_GET['id'], $_GET['table']) );
			misc::event_log(user::uid(), "comment", $_GET['table'], $_GET['id']);
			misc::increase_experience(5);
			header("Location: {$_SERVER['HTTP_REFERER']}");
		    }
		}
	    }
	}
    }
    
    public static function delete_comment()
    {
	if ( isset($_GET['delete_comment']) and isset($_GET['user_comment']) )
	{
	    $id = $_GET['delete_comment'];
	    $user = $_GET['user_comment'];
	    misc::delete_comment($id, $user);
	    misc::event_log($user, "delete_comment", $_GET["table_name"], $_GET["table_id"]);
	    misc::decrease_experience(5);
	    header("Location: ?p=detail&table=".$_GET["table_name"]."&id=".$_GET["table_id"]);
	}
    }
    
    public static function upload_guide()
    {
	if( isset($_POST['upload_guide_title']) && isset($_POST['upload_guide_text']) && isset($_POST['upload_guide_type']))
	{
	    if (user::online())
	    {
		if (trim($_POST['upload_guide_text']) != "" && trim($_POST['upload_guide_title']) != "" && trim($_POST['upload_guide_type'] != ""))
		{
		    $text = nl2br($_POST['upload_guide_text']);
		    db::executeQuery("INSERT INTO guides (title, html_content, guide_type, user_id) VALUES (:1,:2,:3,:4)", array($_POST['upload_guide_title'], $text, $_POST['upload_guide_type'], user::uid()));
		    misc::increase_experience(50);
		    $row = db::nextRowFromQuery(db::executeQuery("SELECT uid FROM guides WHERE user_id = :1 ORDER BY posted DESC LIMIT 1", array(user::uid())));
		    misc::event_log(user::uid(), "add", "guides", $row["uid"]);
		    header("Location: ?p=detail&table=guides&id=".$row["uid"]);
		}
	    }
	}
    }
    
    public static function edit_guide()
    {
	if( isset($_POST['edit_guide_title']) && isset($_POST['edit_guide_text']) && isset($_POST['edit_guide_type']) && isset($_POST['edit_guide_uid']))
	{
	    if (user::online())
	    {
		if (trim($_POST['edit_guide_text']) != "" && trim($_POST['edit_guide_title']) != "" && trim($_POST['edit_guide_type'] != "") && trim($_POST['edit_guide_uid'] != ""))
		{
		    $text = nl2br($_POST['edit_guide_text']);
		    db::executeQuery("UPDATE guides SET title = :1 WHERE uid = :2", array($_POST['edit_guide_title'], $_POST['edit_guide_uid']));
		    db::executeQuery("UPDATE guides SET html_content = :1 WHERE uid = :2", array(str_replace('\r\n', "<br />", $text), $_POST['edit_guide_uid']));
		    db::executeQuery("UPDATE guides SET guide_type = :1 WHERE uid = :2", array($_POST['edit_guide_type'], $_POST['edit_guide_uid']));
		    misc::event_log(user::uid(), "edit", "guides", $_POST['edit_guide_uid']);
		    header("Location: {$_SERVER['HTTP_REFERER']}");
		}
	    }
	}
    }
    
    public static function fav()
    {
	if ( isset($_GET["table"]) && isset($_GET["id"]) )
	{
	    if (user::online())
	    {
		if(isset($_GET["fav"]))
		{
		    if( db::nextRowFromQuery(db::executeQuery("SELECT * FROM fav_item WHERE table_name = :1 AND table_id = :2 AND user_id = :3", array($_GET["table"], $_GET["id"], user::uid()))) )
		    {
			db::executeQuery("DELETE FROM fav_item WHERE table_name = :1 AND table_id = :2 AND user_id = :3", array($_GET["table"], $_GET["id"], user::uid()));
			misc::event_log(user::uid(), "unfav", $_GET["table"], $_GET["id"]);
		    }
		    else
		    {
			db::executeQuery("INSERT INTO fav_item (user_id,table_name,table_id) VALUES (:1,:2,:3)", array(user::uid(), $_GET["table"], $_GET["id"]));
			misc::event_log(user::uid(), "fav", $_GET["table"], $_GET["id"]);
		    }
		    header("Location: {$_SERVER['HTTP_REFERER']}");
		}
		else if(isset($_GET["report"]))
		{
		    if( db::nextRowFromQuery(db::executeQuery("SELECT * FROM reported WHERE table_name = :1 AND table_id = :2 AND user_id = :3", array($_GET["table"], $_GET["id"], user::uid()))) )
		    { } else {
			db::executeQuery("INSERT INTO reported (table_name, table_id, user_id) VALUES (:1,:2,:3)", array($_GET["table"], $_GET["id"], user::uid()));
			misc::event_log(user::uid(), "report", $_GET["table"], $_GET["id"]);
		    }
		}
	    }
	}
    }
    
    public static function delete_item()
    {
	if ( isset($_GET['del_item']) and isset($_GET['del_item_table']) and isset($_GET['del_item_user']))
	{
	    $item_id = $_GET['del_item'];
	    $table_name = $_GET['del_item_table'];
	    $user_id = $_GET['del_item_user'];
	    misc::delete_item($item_id, $table_name, $user_id);	//delete item and comments related to it
	    misc::event_log(user::uid(), "delete_item", $table_name, $item_id);
	    if ($table_name == "screenshot_group")
	    {
		header("Location: {$_SERVER['HTTP_REFERER']}");
		return;
	    }
	    header("Location: /?p=$table_name");
	}
    }
    
    public static function apply_filter()
    {
	if (isset($_POST["apply_filter"]))
	{
	    if ($_POST["apply_filter_type"] == "map")
	    {
		setcookie("map_sort_by", $_POST["sort"], time()+3600*24*360, "/");
		setcookie("map_mod", $_POST["mod"], time()+3600*24*360, "/");
		setcookie("map_tileset", $_POST["tileset"], time()+3600*24*360, "/");
		setcookie("map_type", $_POST["type"], time()+3600*24*360, "/");
		if (isset($_POST["map_my_items"]) and user::online())
		    setcookie("map_my_items", "1", time()+3600*24*360, "/");
		else
		    if (isset($_COOKIE["map_my_items"]) and user::online())
			setcookie("map_my_items", "", time()-60*60, "/");
		$keys = array_keys($_GET);
		$gets = "";
		foreach($keys as $key)
		{
		    if($key != "current_grid_page_maps")
			$gets .= "&" . $key . "=" . $_GET[$key];
		}
		header("Location: /?current_grid_page_maps=1".$gets);
	    }
	    else if ($_POST["apply_filter_type"] == "replay")
	    {
		setcookie("replay_sort_by", $_POST["sort"], time()+3600*24*360, "/");
		if (isset($_POST["replay_version"]))
		    setcookie("replay_version", $_POST["replay_version"], time()+3600*24*360, "/");
		if (isset($_POST["replay_my_items"]) and user::online())
		    setcookie("replay_my_items", "1", time()+3600*24*360, "/");
		else
		    if (isset($_COOKIE["replay_my_items"]) and user::online())
			setcookie("replay_my_items", "", time()-60*60, "/");
		$keys = array_keys($_GET);
		$gets = "";
		foreach($keys as $key)
		{
		    if($key != "current_grid_page_replays")
			$gets .= "&" . $key . "=" . $_GET[$key];
		}
		header("Location: /?current_grid_page_replays=1".$gets);
	    }
	    else if ($_POST["apply_filter_type"] == "msg_filter")
	    {
		if (isset($_POST["msg_unread_only_filter"]))
		    setcookie("msg_unread_only_filter", "1", time()+3600*24*360, "/");
		else
		    if (isset($_COOKIE["msg_unread_only_filter"]) and user::online())
			setcookie("msg_unread_only_filter", "", time()-60*60, "/");
		header("Location: {$_SERVER['HTTP_REFERER']}");
	    }
	    else
	    {
		$arg = $_POST["apply_filter_type"];
		setcookie($arg."_sort_by", $_POST["sort"], time()+3600*24*360, "/");
		setcookie($arg."_type", $_POST["type"], time()+3600*24*360, "/");
		if (isset($_POST[$arg."_my_items"]) and user::online())
		    setcookie($arg."_my_items", "1", time()+3600*24*360, "/");
		else
		    if (isset($_COOKIE[$arg."_my_items"]) and user::online())
			setcookie($arg."_my_items", "", time()-60*60, "/");
		$keys = array_keys($_GET);
		$gets = "";
		foreach($keys as $key)
		{
		    if($key != "current_grid_page_maps")
			$gets .= "&" . $key . "=" . $_GET[$key];
		}
		header("Location: /?current_grid_page_maps=1".$gets);
	    }
	}
    }
    
    public static function following()
    {
	if (isset($_GET["follow"]))
	{
	    $id = $_GET["follow"];
	    if (user::online())
	    {
		$query = "SELECT * FROM following WHERE who = :1 and whom = :2";
		$result = db::executeQuery($query, array(user::uid(), $id));
		while (db::nextRowFromQuery($result))
		{
		    return;
		}
		//check if users exists
		$query = "SELECT uid FROM users WHERE uid = :1";
		$result = db::executeQuery($query, array($id));
		while (db::nextRowFromQuery($result))
		{
		    $query = "INSERT INTO following
				(who,whom)
			    VALUES
			    (:1,:2)
		    ";
		    db::executeQuery($query, array(user::uid(), $id));
		    misc::event_log(user::uid(), "follow", "", $id);
		    header("Location: {$_SERVER['HTTP_REFERER']}");
		}
	    }
	}
	elseif(isset($_GET["unfollow"]))
	{
	    $id = $_GET["unfollow"];
	    if (user::online())
	    {
		$query = "SELECT * FROM following WHERE who = :1 AND whom = :2";
		$result = db::executeQuery($query, array(user::uid(), $id));
		while (db::nextRowFromQuery($result))
		{
		    //check if users exists
		    $query = "SELECT uid FROM users WHERE uid = :1";
		    $result = db::executeQuery($query, array($id));
		    while (db::nextRowFromQuery($result))
		    {
			$query = "DELETE FROM following WHERE who = :1 AND whom = :2";
			db::executeQuery($query, array(user::uid(), $id));
			misc::event_log(user::uid(), "unfollow", "", $id);
			header("Location: {$_SERVER['HTTP_REFERER']}");
		    }
		}
	    }
	}
    }
    
    public static function edit_item_info()
    {
	if (isset($_POST['add_map_info']))
	{
	    if (user::uid() != $_POST['user_id'])
		return;
	    $map_id = $_POST['map_id'];
	    $query = "UPDATE maps SET additional_desc = :1 WHERE uid = :2";
	    db::executeQuery($query, array(trim($_POST['add_map_info']), $map_id));
	    header("Location: /?p=detail&table=maps&id=".$map_id);
	}
	if (isset($_POST['add_replay_info']))
	{
	    if (user::uid() != $_POST['user_id'])
		return;
	    $replay_id = $_POST['replay_id'];
	    $query = "UPDATE replays SET description = :1 WHERE uid = :2";
	    db::executeQuery($query, array(trim($_POST['add_replay_info']), $replay_id));
	    header("Location: /?p=detail&table=replays&id=".$replay_id);
	}
	if (isset($_POST['add_unit_info']))
	{
	    if (user::uid() != $_POST['user_id'])
		return;
	    $unit_id = $_POST['unit_id'];
	    $query = "UPDATE units SET description = :1 WHERE uid = :2";
	    db::executeQuery($query, array(trim($_POST['add_unit_info']), $unit_id));
	    header("Location: /?p=detail&table=units&id=".$unit_id);
	}
	if (isset($_POST['edit_unit_type']))
	{
	    if (user::uid() != $_POST['user_id'])
		return;
	    $unit_id = $_POST['unit_id'];
	    $query = "UPDATE units SET type = :1 WHERE uid = :2";
	    db::executeQuery($query, array(trim($_POST['edit_unit_type']), $unit_id));
	    header("Location: /?p=detail&table=units&id=".$unit_id);
	}
    }
    
    public static function upload_screenshot()
    {
	if(isset($_FILES["screenshot_upload"]["name"]))
	{
	    header("Location: {$_SERVER['HTTP_REFERER']}");
	}
    }
    
    public static function pm()
    {
	if (isset($_POST["msg_title"]))
	{
	    $title = trim($_POST["msg_title"]);
	    $to_id = $_POST["to_user_id"];
	    if (!user::exists($to_id))
		return;
	    if (!user::online())
		return;
	    if (user::uid() == $to_id)
		return;
	    $content = trim(mail::parse_message($_POST["msg_message"]));
	    if ($content == "")
		return;
	    if ($title == "")
		return;
	    $query = "INSERT INTO pm
		    (from_user_id,to_user_id,title,content)
		    VALUES
		    (:1,:2,:3,:4)";
	    db::executeQuery($query, array(user::uid(), $to_id, $title, $content));
	    $email = user::email_by_uid($to_id);
	    misc::send_mail( $email, 'New PM at OpenRA Content Website', 'You\'ve got a new private message! Your inbox is: http://'.$_SERVER['HTTP_HOST'].'/?p=mail&m=inbox', array( 'From' => 'noreply@'.$_SERVER['HTTP_HOST'] ) );
	    header("Location: /?p=mail&m=sent");
	}
    }
}

?>
