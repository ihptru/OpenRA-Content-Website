<?PHP

class upload
{
	public static function upload_oramap($website_path, $username, $form_input_name)
	{
		if(isset($_FILES[$form_input_name]["name"]))
		{
			$filename = $_FILES[$form_input_name]["name"];
			$source = $_FILES[$form_input_name]["tmp_name"];
			$type = $_FILES[$form_input_name]["type"];
			$supported = false;
			$name = explode(".", $filename);
			$accepted_types = array('application/octet-stream');
			foreach($accepted_types as $mime_type)
			{
				if($mime_type == $type)
                {
					$supported = true;
					break;
                }
			}
			if ($supported == false)
			{
				return '';
			}
			else
			{
                if (strtolower($name[1]) == 'oramap')
                {	
					$path = $website_path . "users/" . $username . "/maps/" . $name[0];
					if (is_dir($path))
					{
						return 'exists';
					}
					mkdir($path);
					$target_path = $path . "/" . $filename;
					if(move_uploaded_file($source, $target_path))
					{
						exec("python python/ml.py " . $path . "/ ? " . $filename);
						return $target_path;
					}
					else
					{
						echo "upload error";
						return '';
					}
                }
                else
                {
					return '';
				}
			}
		}
		else
		{
			return '';
		}
	}
}

class pages
{
	public static function current($page, $request)
	{
		if ($page == $request)
		{
			return 'current';
		}
		else
		{
			return '';
		}
	}
}

?>
