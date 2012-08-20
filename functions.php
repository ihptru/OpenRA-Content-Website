<?PHP

class upload
{
    public static function upload_oramap($username, $pre_version="0", $user_id)
    {
	if(isset($_FILES["map_upload"]["name"]))
	{
	    if (is_uploaded_file($_FILES["map_upload"]["tmp_name"]))
	    {
		$filename = $_FILES["map_upload"]["name"];
		$source = $_FILES["map_upload"]["tmp_name"];
		$type = $_FILES["map_upload"]["type"];
		$name = explode(".", $filename);
		$accepted_type = "application/octet-stream";
		if ($type != $accepted_type)
		{
		    return "Not supported file type";	// that's not a map file
		}
		if (strtolower(end($name)) != "oramap")
		{
		    return "Not supported file type";	// that's not a map file (map file must have `oramap` extention)
		}
		exec("python python/minimap.py -s " . str_replace(" ", "\ ", $source) . " -i " . $user_id . " -u " . $username . " -t " . str_replace(" ", "\ ", $filename) . " -p " . $pre_version, $output, $return_code);
		function code_match($code)
		{
		    $codes = array(
			'0' => "0",
			'1' => "Error's while uploading map, contact administrator",
			'2' => "Incorrect options",
			'3' => "Unknown map format",
			'4' => "Unknown mod",
			'5' => "Map already exists",
			'6' => "Could not upload the map",
			'7' => "Database error, try again later",
			'8' => "You already have a map with the same hash",
		    );
		    return $codes[$code];
		}
		//return codes:
		// 0  -  Success
		// 1  -  Other errors
		// 2  -  Incorrect options
		// 3  -  Unknown map format
		// 4  -  Unknown mod
		// 5  -  Map exists
		// 6  -  Could not upload map
		// 7  -  Database error
		// 8  -  User already uploaded such a map
		if ($return_code == 0)
		{
		    misc::increase_experience(10);
		    $row = db::nextRowFromQuery(db::executeQuery("SELECT uid,maphash FROM maps WHERE user_id = :1 ORDER BY posted DESC LIMIT 1", array($user_id)));
		    misc::event_log(user::uid(), "add", "maps", $row["uid"]);
		    if (isset($_POST["additional_desc"]))
			db::executeQuery("UPDATE maps SET additional_desc = :1 WHERE user_id = :2 AND maphash = :3", array($_POST["additional_desc"], $user_id, trim($row["maphash"])));
		}
		return code_match($return_code);
	    }
	    else
		return "";
	}
	else
	    return "";	// file is not choosen
    }
    
    public static function upload_unit($username)
    {
	function insert_unit($dirname,$description,$type,$unit_palette)
	{
	    $query = "INSERT INTO units
		(title,description,preview_image,user_id,type,palette)
		VALUES
		(:1,:2,:3,:4,:5,:6)
		";
	    db::executeQuery($query, array($dirname, $description, "users/".user::username()."/units/".$dirname."/preview.gif", user::uid(), $type, $unit_palette));
	    misc::increase_experience(50);
	    $row = db::nextRowFromQuery(db::executeQuery("SELECT uid FROM units WHERE user_id = :1 ORDER BY posted DESC LIMIT 1", array(user::uid())));
	    misc::event_log(user::uid(), "add", "units", $row["uid"]);
	}
	$unit_palette = "temperat.pal"; //Needed for shp extractor
	if(isset($_POST['unit_palette']))
	    $unit_palette = $_POST['unit_palette'];
	$count = 0;
	$messages = "";
	while (isset($_FILES["file_".$count]))
	{
	    $run_shp = false;
	    $filename = $_FILES["file_".$count]["name"];
	    if ($filename == "")
		return $messages;

	    if (!isset($_POST["unit_name"]) or $_POST["unit_name"] == "")
		return "1"; // Name of unit is not set!
	    $dirname = str_replace(" ", "_" , $_POST["unit_name"]);
	    
	    $description = "";
	    if (isset($_POST["unit_description"]))
		$description = $_POST["unit_description"];
	    
	    $unit_type = "other";
	    if (isset($_POST["unit_type"]))
		$unit_type = $_POST["unit_type"];
	    
	    $source = $_FILES["file_".$count]["tmp_name"];
	    $type = $_FILES["file_".$count]["type"];
	    $name = explode(".", $filename);	//array

	    $accepted_types = array("application/octet-stream","application/x-qgis");
	    $accepted_exts = array("shp","yaml","aud");
	    if(!in_array($type, $accepted_types) or !in_array(strtolower($name[1]), $accepted_exts))
	    {
		$messages .= $filename . " - upload fail: not supported file type<br>";
		continue;
	    }

	    $path = WEBSITE_PATH . "users/" . $username . "/units/" . $dirname;
	    if ($count == 0)	//make dir checking at first file recognised
	    {
		if (is_dir($path))
		{
		    return "Unit with such name already exists";
		}
		else
		{
		    mkdir($path);
		    insert_unit($dirname, $description, $unit_type, $unit_palette);
		}
	    }
	    else
	    {
		//directory was not created before this moment - file types were unsupported
		if (!is_dir($path))
		{
		    mkdir($path);
		    insert_unit($dirname, $description, $unit_type, $unit_palette);
		}
	    }
	    
	    $target_path = $path . "/" . $filename;
	    if(move_uploaded_file($source, $target_path))
	    {
		$messages .= $filename ." - uploaded<br>";
	    }
	    if (strtolower(end($name)) == "shp" and $run_shp == false)
	    {
		exec("python python/shpextractor.py  -s ".$target_path." -p ".$unit_palette, $output, $return_code);
		$run_shp = true;
	    }
	    $count++;
	}
	return $messages;
    }
    
    public static function upload_replay($username, $user_id)
    {
	if(isset($_FILES["replay_upload"]["name"]))
	{
	    if (is_uploaded_file($_FILES["replay_upload"]["tmp_name"]))
	    {
		$filename = $_FILES["replay_upload"]["name"];
		$source = $_FILES["replay_upload"]["tmp_name"];
		$type = $_FILES["replay_upload"]["type"];
		$name = explode(".", $filename);
		$accepted_type = "application/octet-stream";
		if ($type != $accepted_type)
		{
		    return "Not supported file type";	// that's not a replay file
		}
		if (strtolower($name[1]) != "rep")
		{
		    return "Not supported file type";	// that's not a replay file (replay file must have `rep` extention)
		}
		$query = "SELECT COUNT(*) AS count FROM replays WHERE user_id = :1";
		$row = db::nextRowFromQuery(db::executeQuery($query, array($user_id)));
		if ($row["count"] > 50)
		{
		    return "You have reached your limit!"; // can't upload more then 50 replays
		}
		exec("python python/replay.py -s " . str_replace(" ", "\ ", $source) . " -i " . $user_id . " -u " . $username . " -t " . str_replace(" ", "\ ", $filename), $output, $return_code);
		function code_match($code)
		{
		    $codes = array(
			'0' => "0",
			'1' => "Error's while uploading replay, contact administrator",
			'2' => "Incorrect options",
			'3' => "StartGame point is not detected",
			'4' => "You already have this replay uploaded",
		    );
		    return $codes[$code];
		}
		//return codes:
		// 0  -  Success
		// 1  -  Other errors
		// 2  -  Incorrect options
		// 3  -  StartGame point is not detected
		// 4  -  User already has an identical replay uploaded
		if ($return_code == 0)
		{
		    misc::increase_experience(10);
		    $row = db::nextRowFromQuery(db::executeQuery("SELECT uid FROM replays WHERE user_id = :1 ORDER BY posted DESC LIMIT 1", array($user_id)));
		    misc::event_log(user::uid(), "add", "replays", $row["uid"]);
		    if (isset($_POST["description"]))
			db::executeQuery("UPDATE replays SET description = :1 WHERE user_id = :2 AND uid = :3", array($_POST["description"], $user_id, $row["uid"]));
		}
		return code_match($return_code);
	    }
	    else
		return "";
	}
	else
	    return "";	// file is not choosen
    }
    
    public static function screenshot()
    {
	if(isset($_FILES["screenshot_upload"]["name"]))
	{
	    if (!is_uploaded_file($_FILES["screenshot_upload"]["tmp_name"]))
		return "";
	    if ( !(isset($_POST["table_name"]) and isset($_POST["table_id"])) )
		return "";
	    $id = $_POST["table_id"];
	    $table = $_POST["table_name"];
	    
	    $query = "SELECT * FROM screenshot_group WHERE table_name = :1 AND table_id = :2 AND user_id = :3";
	    $res = db::executeQuery($query, array($table, $id, user::uid()));
	    if (db::num_rows($res) >= 4)
		return "";
	    $filename = $_FILES["screenshot_upload"]["name"];
	    $name = explode(".", $filename);
	    $source = $_FILES["screenshot_upload"]["tmp_name"];
	    $type = $_FILES["screenshot_upload"]["type"];
	    $accepted_types = array("image/jpeg","image/png","image/gif","image/bmp","image/x-png");
	    if(!in_array($type, $accepted_types))
		return "";
	    $path = "images/screenshots/".$table."_".$id."_".user::uid()."_".chr(97 + mt_rand(0, 25)).chr(97 + mt_rand(0, 25)).".".$name[1];
	    move_uploaded_file($source, $path);
	    $query = "INSERT INTO screenshot_group
		    (table_id,table_name,user_id,image_path)
		    VALUES (:1,:2,:3,:4)
	    ";
	    db::executeQuery($query, array($id,$table,user::uid(),$path));
	    
	    //create thumbnail
	    $type = image_type_to_mime_type(exif_imagetype($path));
	    $new_name = explode(".", $path);
	    $new_name = $new_name[0] . "_thumbnail." . $new_name[1];
	    misc::imageresize($new_name,$path,300,300,100, $type);
	    
	    $query = "SELECT uid FROM screenshot_group WHERE table_id = :1 AND table_name = :2 AND user_id = :3 ORDER BY posted DESC";
	    $row_sc = db::nextRowFromQuery(db::executeQuery($query, array($id, $table, user::uid())));
	    misc::event_log(user::uid(), "add", "screenshot", $row_sc["uid"]);
	}
	return "";
    }
    
    public static function avatar()
    {
	if(isset($_FILES["avatar_upload"]["name"]))
	{
	    if (!is_uploaded_file($_FILES["avatar_upload"]["tmp_name"]))
		return "";
	    $filename = $_FILES["avatar_upload"]["name"];
	    $source = $_FILES["avatar_upload"]["tmp_name"];
	    $type = $_FILES["avatar_upload"]["type"];
	    $accepted_types = array("image/jpeg","image/png","image/gif","image/bmp","image/x-png");
	    if(!in_array($type, $accepted_types))
	    {
		return "type error";
	    }
	    move_uploaded_file($source, "users/".user::username()."/avatar_original.jpg");
	    misc::imageresize("users/".user::username()."/avatar.jpg","users/".user::username()."/avatar_original.jpg",200,400,100, $type);
	    unlink("users/".user::username()."/avatar_original.jpg");
	    $query = "UPDATE users SET avatar = :1 WHERE uid = :2";
	    db::executeQuery($query, array("Some", user::uid()));
	    return "done";
	}
	return "";
    }
}

class pages
{
    public static function main_page_request()
    {
	if (isset($_GET['register']) and (!user::online()))
	{
	    user::register_actions();
	    return;
	}
	if (isset($_GET['recover']) and (!user::online()))
	{
	    echo "<a href='?recover&recover_pass'>Recover password</a><br>";
	    echo "<a href='?recover&recover_user'>Recover username</a><br>";
	    user::recover();
	    return;
	}
	if (isset($_GET['action']))
	{
	    // non menu or profile: other pages
	    content::action($_GET['action']);
	    return;
	}
	// other checks should be done before $_GET['p'], because it will override page
	if (isset($_GET['profile']))
	{
	    profile::show_profile();
	    return;
	}
	if (isset($_GET['p']))
	{
	    content::page($_GET['p']);
	    return;
	}

	if (count($_GET) == 0)
	{
	    echo "<h3>Recent Articles</h3>";
	    $result = db::executeQuery("SELECT * FROM articles ORDER BY posted DESC");
	    echo content::createArticleItems($result);

	    return;
	}
	
    }

    public static function current($page, $request)
    {
	if ($page == $request)
	    return "current";
	else
	    return "";
    }
    
    public static function allISSet($arr)
    {
	for($i = 0; $i < count($arr); $i++)
	    if(isset($_POST[$arr[$i]]) == false)
		return false;
	return true;
    }
    
    public static function serialize_array($arr)
    {
	return base64_encode(json_encode($arr)); 
    }
    
    public static function deserialize_array($str)
    {
	return json_decode(base64_decode($str)); 
    }
}

class misc
{
    public static function avatar($user_id)
    {
	$query = "SELECT avatar,login FROM users WHERE uid = :1";
	$ava = db::nextRowFromQuery(db::executeQuery($query, array($user_id)));
	if ($ava["avatar"] == "None")
	{
	    return "images/noavatar.png";
	}
	elseif ($ava["avatar"] == "Some")
	{
	    return "users/".$ava["login"]."/avatar.jpg";
	}
    }
    
    public static function comment_owner($id)
    {
	if (user::online())
	{
	    if ($id == user::uid())
	    {
		return True;
	    }
	}
	return False;
    }
    
    public static function delete_comment($id, $user)
    {
	if ( $user == user::uid() )
	{
	    $query = "DELETE FROM comments WHERE uid = :1";
	    db::executeQuery($query, array($id));
	}
    }
    
    public static function delete_item($item_id, $table_name, $user_id)
    {
	if ($user_id == user::uid())
	{	    
	    //remove map directory and it's content from disk
	    if ($table_name == "maps")
	    {
		$p_ver = 0;
		$n_ver = 0;
		$query = "SELECT path,p_ver,n_ver FROM maps WHERE uid = :1";
		$result = db::executeQuery($query, array($item_id));
		while ($db_data = db::nextRowFromQuery($result))
		{
		    $p_ver = $db_data["p_ver"];
		    $n_ver = $db_data["n_ver"];
		    $path = WEBSITE_PATH . $db_data['path'];
		}
		foreach (scandir($path) as $item)
		{
		    if ($item == '.' || $item == '..') continue;
		    unlink($path.$item);
		}
		rmdir($path);
		
		$query = "UPDATE maps
			    SET n_ver = :1
			    WHERE uid = :2
		";
		db::executeQuery($query, array($n_ver, $p_ver));
		$query = "UPDATE maps
			    SET p_ver = :1
			    WHERE uid = :2
		";
		db::executeQuery($query, array($p_ver, $n_ver));
		misc::decrease_experience(10);
	    }
	    
	    if ($table_name == "units")
	    {
		$query = "SELECT title FROM units WHERE uid = :1";
		$result = db::executeQuery($query, array($item_id));
		while ($db_data = db::nextRowFromQuery($result))
		{
		    $title = $db_data['title'];
		}
		$query = "SELECT login FROM users WHERE uid = :1";
		$result = db::executeQuery($query, array($user_id));
		while ($db_data = db::nextRowFromQuery($result))
		{
		    $username = $db_data['login'];
		}
		$path = WEBSITE_PATH . "users/" . $username . "/units/" . $title . "/";
		foreach (scandir($path) as $item)
		{
		    if ($item == '.' || $item == '..') continue;
		    unlink($path.$item);
		}
		rmdir($path);
		misc::decrease_experience(50);
	    }
	    
	    if ($table_name == "replays")
	    {
		$query = "SELECT path FROM replays WHERE uid = :1";
		$result = db::executeQuery($query, array($item_id));
		while ($db_data = db::nextRowFromQuery($result))
		{
		    $path = WEBSITE_PATH . $db_data['path'];
		}
		unlink($path);
		$query = "DELETE FROM replay_players WHERE id_replays = :1";
		db::executeQuery($query, array($item_id));
		misc::decrease_experience(10);
	    }
	    
	    if ($table_name == "screenshot_group")
	    {
		$query = "SELECT image_path FROM screenshot_group WHERE uid = :1";
		$result = db::executeQuery($query, array($item_id));
		$path = False;
		while ($db_data = db::nextRowFromQuery($result))
		{
		    $path = $db_data['image_path'];
		}
		if ($path)
		{
		    $dir = dirname($path) . "/";
		    $name = basename($path);
		    $thumb = explode(".", $name);
		    $thumb = $dir . $thumb[0] . "_thumbnail." . $thumb[1];
		    unlink(WEBSITE_PATH . $path);
		    unlink(WEBSITE_PATH . $thumb);
		}
	    }
	    
	    if ($table_name == "guides")
		misc::decrease_experience(50);

	    //remove screenshots for item if there are any
	    if (in_array($table_name, array("maps","units")))
	    {
		$query = "SELECT * FROM screenshot_group WHERE table_id = :1 AND table_name = :2 AND user_id = :3 AND image_path NOT LIKE ('%/fullPreview%')";
		$result_screen = db::executeQuery($query, array($item_id, $table_name, $user_id));
		while ($row_screen = db::nextRowFromQuery($result_screen))
		{
		    $dir = dirname($row_screen["image_path"]) . "/";
		    $name = basename($row_screen["image_path"]);
		    $thumb = explode(".", $name);
		    $thumb = $dir . $thumb[0] . "_thumbnail." . $thumb[1];
		    unlink(WEBSITE_PATH . $row_screen["image_path"]);
		    unlink(WEBSITE_PATH . $thumb);
		}
		$query = "DELETE FROM screenshot_group WHERE table_id = :1 AND table_name = :2 AND user_id = :3";
		db::executeQuery($query, array($item_id, $table_name, $user_id));
	    }

	    //remove item from DB
	    $query = "DELETE FROM $table_name WHERE uid = :1";
	    db::executeQuery($query, array($item_id));
	    //remove comments from DB
	    //remove records from fav_item table related to current item for each user
	    //remove records in featured and reported tables; remove screenshots for this item
	    $tables = array("comments", "fav_item", "featured", "reported", "screenshot_group");
	    foreach($tables as $table)
	    {
		$query = "DELETE FROM $table WHERE table_name = :1 AND table_id = :2";
		db::executeQuery($query, array($table_name, $item_id));
	    }
	}
    }
    
    public static function imageresize($result_file, $original_file, $new_width, $new_height, $quality, $type)
    {
	if($type == "image/jpeg")
	{
	    $im=imagecreatefromjpeg($original_file);
	} 
	elseif($type == "image/png" or $type == "image/x-png")
	{
	    $im=imagecreatefrompng($original_file);
	} 
	elseif($type == "image/gif")
	{
	    $im=imagecreatefromgif($original_file);
	}
	elseif($type == "image/bmp")
	{
	    $im=imagecreatefrombmp($original_file);
	}

	$k1=$new_width/imagesx($im);
	$k2=$new_height/imagesy($im);
	$k=$k1>$k2?$k2:$k1;

	$w=intval(imagesx($im)*$k);
	$h=intval(imagesy($im)*$k);

	$im1=imagecreatetruecolor($w,$h);
	if($type == "image/png" or $type == "image/x-png")
	{
	    imagealphablending($im1, false);
	    imagesavealpha($im1, true);
	}
	imagecopyresampled($im1,$im,0,0,0,0,$w,$h,imagesx($im),imagesy($im));

	if($type == "image/png" or $type == "image/x-png")
	{
	    imagepng($im1,$result_file);
	}
	else
	{
	    imagejpeg($im1,$result_file,$quality);
	}
	imagedestroy($im);
	imagedestroy($im1);
    }

    public static function increase_experience($points)
    {
	$query = "SELECT experience FROM users WHERE uid = :1";
	$value = db::nextRowFromQuery(db::executeQuery($query, array(user::uid())));
	$value = $value["experience"] + $points;
	$query = "UPDATE users SET experience = :1 WHERE uid = :2";
	db::executeQuery($query, array($value, user::uid()));
    }
    
    public static function decrease_experience($points)
    {
	$query = "SELECT experience FROM users WHERE uid = :1";
	$value = db::nextRowFromQuery(db::executeQuery($query, array(user::uid())));
	$value = $value["experience"] - $points;
	$query = "UPDATE users SET experience = :1 WHERE uid = :2";
	db::executeQuery($query, array($value, user::uid()));
    }
    
    public static function event_log($user_id, $type, $table_name="", $table_id=0)
    {
	// types: add,delete_item,delete_comment,report,fav,unfav,edit,login,logout,comment,follow,unfollow
	// issue with `delete_item`: we can not show what user removed because it's removed completely (even basic info of item)
	$query = "INSERT INTO event_log
		(user_id, type, table_name, table_id)
		VALUES
		(:1,:2,:3,:4)
	";
	db::executeQuery($query, array($user_id, $type, $table_name, $table_id));
    }
    
    public static function amount_rows($result, $value)
    {
	if (db::num_rows($result) > $value)
	    return True;
	return False;
    }

    public static function minimap($path)
    {
	if (file_exists($path . "minimap.bmp"))
	    return $path . "minimap.bmp";
	return "images/nominimap.png";
    }
    
    public static function fullPreviewExists($path)
    {
	if (file_exists($path . "fullPreview.bmp"))
	    return True;
	return False;
    }
    
    public static function option_selected($value, $request)
    {
	if ($value == $request)
	    return "selected='selected'";
	return "";
    }
    
    public static function option_selected_bool($value, $request)
    {
	if ($value == $request)
	    return "true";
	return "false";
    }
    
    public static function item_title_by_uid($id, $table)
    {
	$query = "SELECT title FROM $table WHERE uid = :1";
	$result = db::executeQuery($query, array($id));
	while ($row = db::nextRowFromQuery($result))
	    return $row["title"];
	return "";
    }
    
    public static function item_exists($id, $table)
    {
	$query = "SELECT uid FROM $table WHERE uid = :1";
	$result = db::executeQuery($query, array($id));
	while ($row = db::nextRowFromQuery($result))
	    return True;
	return False;
    }
    
    public static function item_owner($id, $table, $user_id)
    {
	$query = "SELECT user_id FROM $table WHERE uid = :1";
	$result = db::executeQuery($query, array($id));
	while ($row = db::nextRowFromQuery($result))
	{
	    if ($row["user_id"] == $user_id)
		return True;
	}
	return False;
    }
    
    public static function paging($nrOfPages, $i, $current, $gets, $table, $type="grid", $pointer = "")
    {
	if( $i <= 3 || $i >= $nrOfPages - 3 || ( $i < $current + 3 && $i > $current - 3) )
	{
	    if($current == $i)
		$pages = "<span id='page_count_none'>" . $i . "</span>";
	    else
		$pages = "<span id='page_count'><a href='?current_".$type."_page_".$table."=".$i.$gets.$pointer."'>" . $i . "</a></span>";
	    return $pages;
	}
	return "...";
    }
    
    public static function current_map_version($current, $id)
    {
	if ($current == $id)
	    return "map_grid_current";
	else
	    return "map_grid";
    }
    
    public static function send_mail($email, $subject, $body, Array $headers = array(), $additional_parameters = NULL)
    {
	// Make sure we set Content-Type and charset
	if ( !isset( $headers['Content-Type'] ) )
	{
	    $headers['Content-Type'] = 'text/plain; charset=utf-8';
	}
  
	$headers_str = '';
	foreach( $headers as $key => $val )
	{
	    $headers_str .= sprintf( "%s: %s\r\n", $key, $val );
	}

	// Use mb_send_mail() function instead of mail() so that headers, including subject are properly encoded
	return mb_send_mail( $email, $subject, $body, $headers_str, $additional_parameters );
    }
    
    public static function amount_of_items_option($table, $option, $my_items=false)
    {
	$my = "";
	$my_array = array();
	if ($my_items == true)
	{
	    $my = " AND user_id = :1";
	    $my_array = array(user::uid());
	}
	$query = "SELECT * FROM $table ".$option.$my;
	$result = db::executeQuery($query, $my_array);
	return db::num_rows($result);
    }
    
    /* creates a compressed zip file */
    public static function create_zip($dir, $destination="", $overwrite=false)
    {
	//if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) { return false; }
	//vars
	$valid_files = array();
	//if files were passed in...
	if (is_dir($dir))
	{
	    foreach (scandir($dir) as $item)
	    {
		if ($item == '.' || $item == '..') continue;
		//make sure the file exists
		if(file_exists($dir."/".$item))
		{
		    $valid_files[] = $dir."/".$item;
		}
	    }
	}
	$dirname = basename($dir);
	//if we have good files...
	if(count($valid_files))
	{
	    //create the archive
	    $zip = new ZipArchive();
	    if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true)
	    {
		return false;
	    }
	    $zip->addEmptyDir($dirname);
	    //add the files
	    foreach($valid_files as $file)
	    {
		$zip->addFile($file,$dirname."/".basename($file));
	    }
	    //debug
	    //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

	    //close the zip -- done!
	    $zip->close();
    
	    //check to make sure the file exists
	    return file_exists($destination);
	}
	else
	{
	    return false;
	}
    }
    
    public static function ingame_players()
    {
	$ra = 0;
	$cnc = 0;
	$d2k = 0;
	
	$string = file_get_contents("http://master.open-ra.org/list_json.php");
	$json = json_decode($string, true);
	$json_len = count($json);
	
	for ($i=0;$i<$json_len;$i++)
	{
	    $mod = explode("@",$json[$i]["mods"]);
	    
	    if (strtolower($mod[0]) == "ra")
		$ra = $ra + (int)$json[$i]["players"];
	    else if (strtolower($mod[0]) == "cnc")
		$cnc = $cnc + (int)$json[$i]["players"];
	    else if (strtolower($mod[0]) == "d2k")
		$d2k = $d2k + (int)$json[$i]["players"];
	}
	return array($ra,$cnc,$d2k);
    }
}

?>
