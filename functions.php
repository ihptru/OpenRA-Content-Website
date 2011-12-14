<?PHP

class upload
{
    public static function upload_oramap($username)
    {
	if(isset($_FILES["map_upload"]["name"]))
	{
	    $filename = $_FILES["map_upload"]["name"];
	    $source = $_FILES["map_upload"]["tmp_name"];
	    $type = $_FILES["map_upload"]["type"];
	    $name = explode(".", $filename);
	    $accepted_type = "application/octet-stream";
	    if ($type != $accepted_type)
	    {
		return "";	// that's not a map file
	    }
	    if (strtolower($name[1]) != "oramap")
	    {
		return "";	// that's not a map file (map file must have `oramap` extention)
	    }
	    $path = WEBSITE_PATH . "users/" . $username . "/maps/" . $name[0];
	    if (is_dir($path))
	    {
		return "Map with such name already exists";
	    }
	    mkdir($path);
	    $target_path = $path . "/" . $filename;
	    if(move_uploaded_file($source, $target_path))
	    {
		exec("python python/ml.py -f " . str_replace(" ", "\ ", $target_path) . " -u " . user::uid());
		return $filename;
	    }
	    else
	    {
		return "";	// no idea why map could not be uploaded...
	    }
	}
	else
	{
	    return "";	// file is not choosen
	}
    }
    
    public static function upload_unit($username)
    {
	function insert_unit($dirname,$description)
	{
	    $query = "INSERT INTO units
		(title,description,preview_image,user_id,screenshot_group_id)
		VALUES
		(
		'".$dirname."','".$description."','0',".user::uid().",0
		)
		";
	    db::executeQuery($query);
	}
	$count = 0;
	$messages = "";
	while (isset($_FILES["file_".$count]))
	{
	    $filename = $_FILES["file_".$count]["name"];
	    if ($filename == "")
		return $messages;

	    if (!isset($_POST["unit_name"]) or $_POST["unit_name"] == "")
		return "Name of unit is not set!";
	    $dirname = $_POST["unit_name"];
	    
	    $description = "";
	    if (isset($_POST["unit_description"]))
	    {
		$description = $_POST["unit_description"];
	    }
	    
	    $source = $_FILES["file_".$count]["tmp_name"];
	    $type = $_FILES["file_".$count]["type"];
	    $name = explode(".", $filename);	//array

	    $accepted_types = array("application/octet-stream","application/x-qgis");
	    $accepted_exts = array("shp","yaml");
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
		    insert_unit($dirname, $description);
		}
	    }
	    else
	    {
		//directory was not created before this moment - file types were unsupported
		if (!is_dir($path))
		{
		    mkdir($path);
		    insert_unit($dirname, $description);
		}
	    }
	    
	    $target_path = $path . "/" . $filename;
	    if(move_uploaded_file($source, $target_path))
	    {
		$messages .= $filename ." - uploaded<br>";
	    }
	    $count++;
	}
	return $messages;
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
	    echo "<a href='index.php?recover&recover_pass'>".lang::$lang['recover pw']."</a><br>";
	    echo "<a href='index.php?recover&recover_user'>".lang::$lang['recover usr']."</a><br>";
	    user::recover();
	    return;
	}
	if (isset($_GET['action']))
	{
	    // non menu or profile: other pages
	    if (user::online())
		content::action($_GET['action']);
	    return;
	}
	// other checks should be done before $_GET['p'], because it will override page
	if (isset($_GET['p']))
	{
	    if ($_GET['p'] == "profile")
	    {
		    profile::show_profile();
	    }
	    else
	    {
		content::page($_GET['p']);
		return;
	    }
	}

	if (count($_GET) == 0)
	{
	    echo "<h3>".lang::$lang['recent articles']."</h3>";
	    $result = db::executeQuery("SELECT * FROM articles");
		echo content::createArticleItems($result);
	    return;
	}
	
    }

    public static function current($page, $request)
    {
	if ($page == $request)
	{
	    return "current";
	}
	else
	{
	    return "";
	}
    }
    
    public static function cur_lang($lang)
    {
	if ($lang == $_COOKIE['language'])
	{
	    return "underline_link";
	}
	else
	{
	    return "";
	}
    }
}

class misc
{
    public static function avatar($ava)
    {
	if ($ava == "None")
	{
	    return "images/noavatar.jpg";
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
	    $query = "DELETE FROM comments WHERE uid = " . $id;
	    db::executeQuery($query);
	}
    }
    
    public static function delete_item($item_id, $table_name, $user_id)
    {
	if ($user_id == user::uid())
	{	    
	    //remove map directory and it's content from disk
	    if ($table_name == "maps")
	    {
		$query = "SELECT path FROM maps WHERE uid = ".$item_id;
		$result = db::executeQuery($query);
		while ($db_data = db::fetch_array($result))
		{
		    $path = WEBSITE_PATH . $db_data['path'];
		}
		foreach (scandir($path) as $item)
		{
		    if ($item == '.' || $item == '..') continue;
		    unlink($path.$item);
		}
		rmdir($path);
	    }
	    
	    if ($table_name == "units")
	    {
		$query = "SELECT title FROM units WHERE uid = ".$item_id;
		$result = db::executeQuery($query);
		while ($db_data = db::fetch_array($result))
		{
		    $title = $db_data['title'];
		}
		$query = "SELECT login FROM users WHERE uid = ".$user_id;
		$result = db::executeQuery($query);
		while ($db_data = db::fetch_array($result))
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
	    }
	    
	    //remove item from DB
	    $query = "DELETE FROM ".$table_name." WHERE uid = ".$item_id;
	    db::executeQuery($query);
	    //remove comments from DB
	    $query = "DELETE FROM comments WHERE table_name = '".$table_name."' AND table_id = ".$item_id;
	    db::executeQuery($query);
	}
    }
}

?>
