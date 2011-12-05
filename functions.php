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
		return "exists";
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
	    content::action($_GET['action']);
	    return;
	}
	// other checks should be done before $_GET['p'], because it will override page
	if (isset($_GET['p']))
	{
	    if ($_GET['p'] == "profile")
	    {
		if (user::online())
		{
		    profile::show_profile();
		}
		else
		{
		    echo "<h3>".lang::$lang['not logged']."</h3>";
		}
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
	    return;
	}
	//content::createArticleItems($result);
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

?>
