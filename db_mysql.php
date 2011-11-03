<?PHP
    $con = null; //MySQL connection
    
    class db
    {
        public static function connect()
        {
            $host = "localhost";
            $username = "oramod";
            $password = "iequeiR6";
            $database = "oramod";
            
            $con = mysql_connect($host,$username,$password);
            if(!$con)
            {
                die("Could not connect: " . mysql_error());
            }
            mysql_select_db($database, $con);
        }
        
        public static function is_connected()
        {
            return ($con != null);
        }
        
        public static function setup()
        {
            $query = "CREATE TABLE users (uid PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                          pass VARCHAR NOT NULL,
                                          nick VARCHAR NOT NULL,
                                          email VARCHAR NOT NULL,
                                          register_date DATE NOT NULL);";
            db_mysql::executeQuery($query);
            
            $query = "CREATE TABLE maps (uid PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                         title VARCHAR NOT NULL,
                                         description VARCHAR NOT NULL,
                                         author VARCHAR NOT NULL,
                                         type VARCHAR NOT NULL,
                                         players INTEGER NOT NULL,
                                         width INTEGER NOT NULL,
                                         height INTEGER NOT NULL,
                                         tileset VARCHAR NOT NULL,
                                         minimap VARCHAR NOT NULL,
                                         user_id INTEGER NOT NULL,
                                         posted DATE NOT NULL);";
            db_mysql::executeQuery($query);
            
            $query = "CREATE TABLE news (uid PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                         title VARCHAR NOT NULL,
                                         content VARCHAR NOT NULL,
                                         user_id VARCHAR NOT NULL,
                                         posted DATE NOT NULL);";
            db_mysql::executeQuery($query);

            $query = "CREATE TABLE units (uid PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                          title VARCHAR NOT NULL,
                                          description VARCHAR NOT NULL,
                                          user_id VARCHAR NOT NULL,
                                          posted DATE NOT NULL);";
            db_mysql::executeQuery($query);
            
            $query = "CREATE TABLE guide (uid PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                          title VARCHAR NOT NULL,
                                          html_content VARCHAR NOT NULL,
                                          user_id VARCHAR NOT NULL,
                                          posted DATE NOT NULL);";
            db_mysql::executeQuery($query);
        }
        
        private static executeQuery($q)
        {
            $result = mysql_query($q);
            if (!$result) {
                $message  = 'Invalid query: ' . mysql_error() . "\n";
                $message .= 'Whole query: ' . $query;
                die($message);
            }
            return $result;
        }
        
        private static function table_exists($tablename) 
        {
            $res = mysql_query("
                               SELECT COUNT(*) AS count 
                               FROM information_schema.tables 
                               WHERE table_schema = '$database' 
                               AND table_name = '$tablename'
                               ");
            
            return mysql_result($res, 0) == 1;
        }

        
        public static function check()
        {
            $allSystemsGo = true;
            if(!table_exists("users"))
                $allSystemsGo = false;
            if(!table_exists("maps"))
                $allSystemsGo = false;
            if(!table_exists("news"))
                $allSystemsGo = false;
            if(!table_exists("units"))
                $allSystemsGo = false;
            if(!table_exists("guide"))
                $allSystemsGo = false;
            return $allSystemsGo;
        }
        
        public static function disconnect()
        {
            mysql_close($con);
            $con = null;
        }
    }
?>