<?PHP
    
    class db
    {
		public static $con = null; //MySQL connection
		
        public static function connect()
        {
            define("DB_HOST","localhost");
            define("DB_USERNAME","oramod");
            define("DB_PASSWORD","iequeiR6");
            define("DB_DATABASE","oramod");
            
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
            return "maps";//mysql_tablename($row);
        }
        
        public static function setup()
        {
            $query = "CREATE TABLE users (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                          pass VARCHAR(80) NOT NULL,
                                          nick VARCHAR(80) NOT NULL,
                                          email VARCHAR(80) NOT NULL,
                                          avatar VARCHAR(500) NOT NULL,
                                          register_date DATE NOT NULL);";
            db::executeQuery($query);
            
            $query = "CREATE TABLE maps (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                         title VARCHAR(80) NOT NULL,
                                         description VARCHAR(500) NOT NULL,
                                         author VARCHAR(80) NOT NULL,
                                         type VARCHAR(80) NOT NULL,
                                         players INTEGER NOT NULL,
                                         width INTEGER NOT NULL,
                                         height INTEGER NOT NULL,
                                         tileset VARCHAR(80) NOT NULL,
                                         minimap VARCHAR(80) NOT NULL,
                                         user_id INTEGER NOT NULL,
                                         posted DATE NOT NULL);";
            db::executeQuery($query);
            
            //Used on front page
            $query = "CREATE TABLE articles (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                             title VARCHAR(80) NOT NULL,
                                             content VARCHAR(500) NOT NULL,
                                             image VARCHAR(500) NOT NULL,
                                             user_id INTEGER NOT NULL,
                                             posted DATE NOT NULL);";
            db::executeQuery($query);

            $query = "CREATE TABLE units (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                          title VARCHAR(80) NOT NULL,
                                          description VARCHAR(500) NOT NULL,
                                          preview_image VARCHAR(500) NOT NULL,
                                          user_id INTEGER NOT NULL,
                                          posted DATE NOT NULL);";
            db::executeQuery($query);
            
            //guide_type (modding, mapping, pixel art, utilities,..) each should have different images
            $query = "CREATE TABLE guides (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                          title VARCHAR(80) NOT NULL,
                                          html_content VARCHAR(500) NOT NULL,
                                          guide_type VARCHAR(500) NOT NULL,
                                          user_id INTEGER NOT NULL,
                                          posted DATE NOT NULL);";
            db::executeQuery($query);
            
            //Special table for just featured 
            $query = "CREATE TABLE featured (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                             table_name VARCHAR(80) NOT NULL,
                                             id INTEGER NOT NULL,
                                             posted DATE NOT NULL);";
            db::executeQuery($query);
            
            //Comments made by users on articles
            $query = "CREATE TABLE comments (uid INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                             title VARCHAR(80) NOT NULL,
                                             content VARCHAR(500) NOT NULL,
                                             user_id INTEGER NOT NULL,
                                             article_id INTEGER NOT NULL,
                                             posted DATE NOT NULL);";
            db::executeQuery($query);
        }
        
        public static function executeQuery($q)
        {
            $result = mysql_query($q);
            if (!$result) {
                $message  = 'Invalid query: ' . mysql_error() . "\n";
                $message .= 'Whole query: ' . $q;
                die($message);
            }
            return $result;
        }
        
        private static function table_exists($tablename) 
        {
            $res = mysql_query("
                               SELECT COUNT(*) AS count 
                               FROM information_schema.tables 
                               WHERE table_schema = '".DB_DATABASE."' 
                               AND table_name = '$tablename'
                               ");
            
            return mysql_result($res, 0) == 1;
        }

        public static function check()
        {
            $allSystemsGo = true;
            if(!db::table_exists("users"))
                $allSystemsGo = false;
            if(!db::table_exists("maps"))
                $allSystemsGo = false;
            if(!db::table_exists("news"))
                $allSystemsGo = false;
            if(!db::table_exists("units"))
                $allSystemsGo = false;
            if(!db::table_exists("guides"))
                $allSystemsGo = false;
            if(!db::table_exists("featured"))
                $allSystemsGo = false;
            if(!db::table_exists("comments"))
                $allSystemsGo = false;
            return $allSystemsGo;
        }
        
        public static function disconnect()
        {
            mysql_close(db::$con);
            db::$con = null;
        }
    }
?>

