<?PHP
    
    class db
    {
	public static $con = null; //Database connection

        public static function connect()
        {
            db::$con = mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD);
            if(!db::$con)
            {
                die("Could not connect: " . mysql_error());
            }
            mysql_select_db(DB_DATABASE, db::$con);
        }

        public static function is_connected()
        {
            return (db::$con != null);
        }

        public static function nextRowFromQuery($result)
        {
            return mysql_fetch_assoc($result);
        }

        public static function getTableNameFrom($row)
        {
            return "maps";	//mysql_tablename($row);
        }

        public static function clearOldRecords()
        {
            $query = "DELETE FROM activation WHERE register_date < (CURRENT_TIMESTAMP-2629743)"; //one month
            $query = "DELETE FROM recover WHERE date_time < (CURRENT_TIMESTAMP-2629743)";
            db::executeQuery($query);
        }

        public static function setup()
        {
	    	$query = "CREATE TABLE IF NOT EXISTS activation (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    pass VARCHAR(80) NOT NULL,
			    login VARCHAR(80) NOT NULL,
			    email VARCHAR(80) NOT NULL,
			    register_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    hash VARCHAR(500) NOT NULL);";
	    	db::executeQuery($query);

            $query = "CREATE TABLE IF NOT EXISTS users (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    pass VARCHAR(80) NOT NULL,
			    login VARCHAR(80) NOT NULL,
			    experiance INTEGER NOT NULL DEFAULT 0,
			    gender INTEGER NOT NULL DEFAULT 1,
			    permission INTEGER NOT NULL DEFAULT 0,
				occupation VARCHAR(80) NOT NULL,
				interests VARCHAR(500) NOT NULL,
				real_name VARCHAR(200) NOT NULL,
				fav_faction VARCHAR(80) NOT NULL DEFAULT 'random',
				country VARCHAR(200) NOT NULL DEFAULT 'None',
				birth_date DATE,
			    email VARCHAR(80) NOT NULL,
			    avatar VARCHAR(500) NOT NULL DEFAULT 'None',
			    register_date TIMESTAMP NOT NULL);";
            db::executeQuery($query);
            
            $query = "CREATE TABLE IF NOT EXISTS fav_item (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    user_id INTEGER NOT NULL,
			    table_name VARCHAR(80) NOT NULL,
			    table_id INTEGER NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
	    	db::executeQuery($query);

            $query = "CREATE TABLE IF NOT EXISTS maps (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    description VARCHAR(500) NOT NULL,
			    author VARCHAR(80) NOT NULL,
			    type VARCHAR(80) NOT NULL,
			    players INTEGER NOT NULL,
			    g_mod VARCHAR(80) NOT NULL,
			    maphash VARCHAR(80) NOT NULL,
			    width INTEGER NOT NULL,
			    height INTEGER NOT NULL,
			    tileset VARCHAR(80) NOT NULL,
			    path VARCHAR(80) NOT NULL,
			    user_id INTEGER NOT NULL,
			    screenshot_group_id INTEGER NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
            db::executeQuery($query);

            //Used on front page
            $query = "CREATE TABLE IF NOT EXISTS articles (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    content VARCHAR(9000) NOT NULL,
			    image VARCHAR(500) NOT NULL,
			    user_id INTEGER NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
            db::executeQuery($query);

            $query = "CREATE TABLE IF NOT EXISTS units (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    description VARCHAR(500) NOT NULL,
			    preview_image VARCHAR(500) NOT NULL,
			    user_id INTEGER NOT NULL,
			    screenshot_group_id INTEGER NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
            db::executeQuery($query);
            
            //guide_type (modding, mapping, pixel art, utilities,..) each should have different images
            $query = "CREATE TABLE IF NOT EXISTS guides (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    html_content VARCHAR(9000) NOT NULL,
			    guide_type VARCHAR(500) NOT NULL,
			    user_id INTEGER NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
            db::executeQuery($query);
            
            //Special table for just featured 
            $query = "CREATE TABLE IF NOT EXISTS featured (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    table_name VARCHAR(80) NOT NULL,
			    id INTEGER NOT NULL,
			    type VARCHAR(80) NOT NULL DEFAULT 'featured',
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
            db::executeQuery($query);
            
            $query = "CREATE TABLE IF NOT EXISTS country (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    name VARCHAR(200) NOT NULL,
			    title VARCHAR(500) NOT NULL DEFAULT 'none');";
            db::executeQuery($query);
            
            //Comments made by users on articles
            $query = "CREATE TABLE IF NOT EXISTS comments (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    content VARCHAR(500) NOT NULL,
			    user_id INTEGER NOT NULL,
			    table_id INTEGER NOT NULL,
			    table_name VARCHAR(80) NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
            db::executeQuery($query);
            
            $query = "CREATE TABLE IF NOT EXISTS recover (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    login VARCHAR(80) NOT NULL,
			    email VARCHAR(80) NOT NULL,
			    hash VARCHAR(500) NOT NULL,
			    date_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
	    db::executeQuery($query);
            
            $query = "CREATE TABLE IF NOT EXISTS image (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    path VARCHAR(500) NOT NULL,
			    path_thumb VARCHAR(500) NOT NULL,
			    description VARCHAR(500) NOT NULL);";
	    db::executeQuery($query);

            $query = "CREATE TABLE IF NOT EXISTS screenshot_group (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    group_id INTEGER NOT NULL,
			    image_id INTEGER NOT NULL);";
	    db::executeQuery($query);

			$query = "SELECT COUNT(*) as count FROM country";
	    	$result = db::executeQuery($query);
	    	$item = db::nextRowFromQuery($result);
	    	if($item["count"]==0)
	    	{
	    		//Get all countries
	    		$files = scandir("images/country_flags/");
				foreach($files as $key => $value)
					if($value != "" && $value != "." && $value != "..")
					{
						$name = $value;
						$title = str_replace("-"," ",substr($value,0,strlen($value)-4));
						$title = str_replace("(","",$title);
						$title = str_replace(")","",$title);
						db::executeQuery("INSERT INTO country (name, title) VALUES ('".$name."','".$title."')");
					}
	    	}

        }
        
        public static function executeQuery($q)
        {
            $result = mysql_query($q);
            if (!$result)
	    {
                $message  = "Invalid query: " . mysql_error() . "\n";
                $message .= "Whole query: " . $q;
                die($message);
            }
            return $result;
        }

        public static function fetch_array($result)
        {
	    return mysql_fetch_array($result);
	}
	
	public static function num_rows($result)
	{
	    return mysql_num_rows($result);
	}

        private static function table_exists($tablename, $emptyIsOK=true) 
        {
            $res = mysql_query("
                               SELECT COUNT(*) AS count 
                               FROM information_schema.tables 
                               WHERE table_schema = '".DB_DATABASE."' 
                               AND table_name = '$tablename'
                               ");
                               
        	if(!$emptyIsOK && mysql_result($res,0) == 1)
        	{
        		$query = "SELECT COUNT(*) as count FROM " . $tablename;
	    		$result = db::executeQuery($query);
	    		$item = db::nextRowFromQuery($result);
	    		if($item["count"]==0)
	    		{
	    			$allSystemsGo = false;
	    		}
        	}
                               
            return mysql_result($res, 0) == 1;
        }

        public static function check()
        {
            $allSystemsGo = true;
	    $tables = array("activation","users","maps","articles","units","guides","featured","comments","recover","image","screenshot_group","country","fav_item");
	    $checkNotEmpty = array("country");
	    foreach ($tables as $table)
	    {
	    	$checkHasContent = false;
	    	foreach ($checkNotEmpty as $empty)
	    	{
	    		if($empty == $table)
	    		{
	    			$checkHasContent = true;
	    			break;
	    		}
	    	}
	    	if($checkHasContent == true)
				if(!db::table_exists($table,false))
		    		$allSystemsGo = false;
		    else
		    	if(!db::table_exists($table))
		    		$allSystemsGo = false;
	    }

            return $allSystemsGo;
        }

        public static function disconnect()
        {
            mysql_close(db::$con);
            db::$con = null;
        }
    }
?>
