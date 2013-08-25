<?PHP
    
    class db
    {
	public static $con = null; //Database connection

	// connect to database
        public static function connect()
        {
	    try
	    {
		db::$con = new PDO("mysql:host=".DB_HOST.";dbname=".DB_DATABASE, DB_USERNAME, DB_PASSWORD);
	    }
	    catch(PDOException $e)
	    {
		echo $e->getMessage();
	    }
        }

	// check if we are connected to database
        public static function is_connected()
        {
            return (db::$con != null);
        }

	// gets query SELECT result and returns 1 row per 1 loop iteration
        public static function nextRowFromQuery($result)
        {
            return $result->fetch(PDO::FETCH_ASSOC);
        }

	// close Cursor
	public static function closeCursor($result)
	{
	    $result->closeCursor();
	}

	// is for cron
        public static function clearOldRecords()
        {
	    $query = "DELETE FROM recover WHERE TIMESTAMPDIFF(DAY, date_time, CURRENT_TIMESTAMP) > 30";
            db::executeQuery($query);
	    //user set `remember me` but did not return to the website during the reported period, everything is expired, remove record from DB
	    $query = "DELETE FROM signed_in WHERE TIMESTAMPDIFF(DAY, set_date, CURRENT_TIMESTAMP) > 100";
	    db::executeQuery($query);
        }

	// run function if at least one of the tables do not exist
        public static function setup()
        {
	    $query = "CREATE TABLE IF NOT EXISTS users (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    pass VARCHAR(80) NOT NULL,
			    login VARCHAR(80) NOT NULL,
			    experience INTEGER NOT NULL DEFAULT 0,
			    gender INTEGER NOT NULL DEFAULT 1,
			    permission INTEGER NOT NULL DEFAULT 0,
			    occupation VARCHAR(80),
			    interests VARCHAR(500),
			    real_name VARCHAR(200),
			    fav_faction VARCHAR(80) NOT NULL DEFAULT 'random',
			    country VARCHAR(200) NOT NULL DEFAULT 'None',
			    birth_date DATE,
			    email VARCHAR(80) NOT NULL,
			    avatar VARCHAR(500) NOT NULL DEFAULT 'None',
			    register_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
            db::executeQuery($query);
            
	    $query = "CREATE TABLE IF NOT EXISTS signed_in (user_id INTEGER PRIMARY KEY NOT NULL,
			    sess_hash VARCHAR(80) NOT NULL,
			    set_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
	    db::executeQuery($query);

            $query = "CREATE TABLE IF NOT EXISTS fav_item (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    user_id INTEGER NOT NULL,
			    table_name VARCHAR(80) NOT NULL,
			    table_id INTEGER NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
	    db::executeQuery($query);

	    //conversation_id -1 is a wildcard
	    $query = "CREATE TABLE IF NOT EXISTS pm (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    from_user_id INTEGER NOT NULL,
			    to_user_id INTEGER NOT NULL,
			    title VARCHAR(200) NOT NULL,
			    content VARCHAR(5000) NOT NULL,
			    isread INTEGER NOT NULL DEFAULT 0,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
	    db::executeQuery($query);
	    
	    $query = "CREATE TABLE IF NOT EXISTS pm_trash (uid INTEGER NOT NULL,
			    from_user_id INTEGER NOT NULL,
			    to_user_id INTEGER NOT NULL,
			    title VARCHAR(200) NOT NULL,
			    content VARCHAR(5000) NOT NULL,
			    isread INTEGER NOT NULL DEFAULT 0,
			    posted TIMESTAMP NOT NULL);";
	    db::executeQuery($query);
	    	
            $query = "CREATE TABLE IF NOT EXISTS maps (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    description VARCHAR(500) NOT NULL,
			    additional_desc VARCHAR(500) NOT NULL DEFAULT '',
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
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    tag VARCHAR(10) NOT NULL DEFAULT 'r1',
			    p_ver INTEGER NOT NULL DEFAULT 0,
			    n_ver INTEGER NOT NULL DEFAULT 0,
			    viewed INTEGER NOT NULL DEFAULT 0);";
            db::executeQuery($query);
	    
	    //todo: date and time of start and duration
	    $query = "CREATE TABLE IF NOT EXISTS replays (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    description VARCHAR(500),
			    path VARCHAR(80) NOT NULL,
			    user_id INTEGER NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    r_hash VARCHAR(80) NOT NULL,
			    date_time TIMESTAMP NOT NULL,
			    duration VARCHAR(50) NOT NULL,
			    version VARCHAR(80) NOT NULL,
			    server_name VARCHAR(80) NOT NULL,
			    maphash VARCHAR(80) NOT NULL,
			    mods VARCHAR(30) NOT NULL,
			    viewed INTEGER NOT NULL DEFAULT 0,
			    tournament INTEGER NOT NULL DEFAULT 0);";
	    db::executeQuery($query);
	    
	    $query = "CREATE TABLE IF NOT EXISTS replay_players (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    id_replays INTEGER NOT NULL,

			    name VARCHAR(80) NOT NULL,
			    colorramp VARCHAR(80) NOT NULL,
			    country VARCHAR(80) NOT NULL,
			    team SMALLINT NOT NULL);";
	    db::executeQuery($query);

            $query = "CREATE TABLE IF NOT EXISTS articles (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    content VARCHAR(9000) NOT NULL,
			    image VARCHAR(500) NOT NULL,
			    user_id INTEGER NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    viewed INTEGER NOT NULL DEFAULT 0);";
            db::executeQuery($query);
            
	    //units (modding)
            $query = "CREATE TABLE IF NOT EXISTS units (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    description VARCHAR(500) NOT NULL,
			    preview_image VARCHAR(500) NOT NULL,
			    type VARCHAR(80) NOT NULL DEFAULT 'other',
			    palette VARCHAR(80) NOT NULL DEFAULT '',
			    user_id INTEGER NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    viewed INTEGER NOT NULL DEFAULT 0);";
            db::executeQuery($query);
            
            //types: other,mapping,modding,design,coding
            $query = "CREATE TABLE IF NOT EXISTS guides (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    html_content VARCHAR(9000) NOT NULL,
			    guide_type VARCHAR(500) NOT NULL DEFAULT 'other',
			    user_id INTEGER NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			    viewed INTEGER NOT NULL DEFAULT 0);";
            db::executeQuery($query);

	    // featured, editor's choice, etc.
            $query = "CREATE TABLE IF NOT EXISTS featured (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    table_name VARCHAR(80) NOT NULL,
			    table_id INTEGER NOT NULL,
			    type VARCHAR(80) NOT NULL DEFAULT 'featured',
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
            db::executeQuery($query);
            
	    //report `bad` item
	    $query = "CREATE TABLE IF NOT EXISTS reported (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    table_name VARCHAR(80) NOT NULL,
			    table_id INTEGER NOT NULL,
			    user_id INTEGER NOT NULL,
			    reason VARCHAR(80) NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
            db::executeQuery($query);
            
	    //country flags
            $query = "CREATE TABLE IF NOT EXISTS country (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    name VARCHAR(200) NOT NULL,
			    title VARCHAR(500) NOT NULL DEFAULT 'none');";
            db::executeQuery($query);
            
	    //comments
            $query = "CREATE TABLE IF NOT EXISTS comments (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    title VARCHAR(80) NOT NULL,
			    content VARCHAR(500) NOT NULL,
			    user_id INTEGER NOT NULL,
			    table_id INTEGER NOT NULL,
			    table_name VARCHAR(80) NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
            db::executeQuery($query);
            
	    //recover password/username
            $query = "CREATE TABLE IF NOT EXISTS recover (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    login VARCHAR(80) NOT NULL,
			    email VARCHAR(80) NOT NULL,
			    hash VARCHAR(500) NOT NULL,
			    date_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
	    db::executeQuery($query);
            
            $query = "CREATE TABLE IF NOT EXISTS screenshot_group (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    table_id INTEGER NOT NULL,
			    table_name VARCHAR(80) NOT NULL,
			    user_id INTEGER NOT NULL,
			    image_path VARCHAR(800) NOT NULL,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);";
	    db::executeQuery($query);
	    
	    // types: add,delete_item,delete_comment,report,fav,unfav,edit,login,logout,comment,follow,unfollow
	    $query = "CREATE TABLE IF NOT EXISTS event_log (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    type VARCHAR(80) NOT NULL,
			    user_id INTEGER NOT NULL,
			    table_name VARCHAR(80),
			    table_id INTEGER,
			    posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);			    
	    ";
	    db::executeQuery($query);
	    
	    //follow user
	    $query = "CREATE TABLE IF NOT EXISTS following (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
			    who INTEGER NOT NULL,
			    whom INTEGER NOT NULL,
			    date_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);
	    ";
	    db::executeQuery($query);

	    $query = "SELECT COUNT(*) AS count FROM country";
	    $result = db::executeQuery($query);
	    $row = db::nextRowFromQuery($result);
	    if($row["count"] == 0)
	    {
		//Get all countries
		$files = scandir("images/country_flags/");
		foreach($files as $key => $value)
		    if($value != "" && $value != "." && $value != "..")
		    {
			$name = $value;
			$title = str_replace("-"," ",substr($value,0,strlen($value)-4));
			db::executeQuery("INSERT INTO country (name, title) VALUES (:1,:2)", array($name, $title));
		    }
	    }
	    
	    // procedure to get a list of all map's versions by it's uid
	    $query = "
		    CREATE PROCEDURE map_versions (IN id INT)
		    BEGIN
			DECLARE save_id INT DEFAULT id;
			DECLARE p_list VARCHAR(1000) DEFAULT id;
			DECLARE n_list VARCHAR(1000) DEFAULT \"\";
			DECLARE amount INT DEFAULT 0;

			loop_n: WHILE TRUE DO
			    SET amount = (SELECT COUNT(n_ver) FROM maps WHERE uid = id);
			    IF amount=0 THEN
				SELECT \"\" AS list;
			    END IF;
			    SET id=(SELECT n_ver FROM maps WHERE uid = id);
			    IF id=0 THEN
				LEAVE loop_n;
			    END IF;
			    SET n_list = CONCAT(n_list, \",\", id);
			END WHILE;

			loop_p: WHILE TRUE DO
			    SET amount = (SELECT COUNT(p_ver) FROM maps WHERE uid = save_id);
			    IF amount=0 THEN
				SELECT \"\" AS list;
			    END IF;
			    SET save_id=(SELECT p_ver FROM maps WHERE uid = save_id);
			    IF save_id=0 THEN
				LEAVE loop_p;
			    END IF;
			    SET p_list = CONCAT(save_id, \",\", p_list);
			END WHILE;

			SET p_list = CONCAT(p_list, n_list);

			SELECT p_list AS list;
		    END
		    ;
	    ";
	    db::executeQuery($query);
        }

        public static function executeQuery($q, $values=array())
        {
	    if (count($values)==0)
	    {
		$result = db::$con->prepare($q);
		$result->execute();
	    }
	    else
	    {
		$result = db::$con->prepare($q);
		$i = 1;
		foreach ($values as $key => $value)
		{
		    $result->bindValue(':'.$i, $value, PDO::PARAM_STR);
		    $i++;
		}
		$result->execute();
	    }
            if (!$result)
	    {
		$arr = $result->errorInfo();
		print_r($arr);
		die();
	    }
            return $result;
        }

	// amount of rows from SELECT result
	public static function num_rows($result)
	{
	    return $result->rowCount();
	}

	// private function to check if table exists in database - unless: execute setup() function
        private static function table_exists($tablename) 
        {
	    $result = db::executeQuery("
                               SELECT COUNT(*) AS count 
                               FROM information_schema.tables 
                               WHERE table_schema = '".DB_DATABASE."' 
                               AND table_name = '$tablename'
                               ");
            $row = db::nextRowFromQuery($result);
	    if ($row["count"] == 0)
	    {
		return false;	//no such table in DB
	    }
            return true;
        }

	// check if all tables exist and they are not empty
        public static function check()
        {
            $allSystemsGo = true;
	    $tables = array("reported","users","maps","articles","units","guides","featured","comments","recover","screenshot_group","country","fav_item","signed_in","event_log","following","map_stats","pm");
	    
	    foreach ($tables as $table)
	    {
		if(!db::table_exists($table))
		    $allSystemsGo = false;
	    }
            return $allSystemsGo;
        }

        public static function disconnect()
        {
            db::$con = null;
        }
    }
?>
