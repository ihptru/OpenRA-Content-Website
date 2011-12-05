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
		exec("python python/ml.py " . $path . "/ ? " . $filename);
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
