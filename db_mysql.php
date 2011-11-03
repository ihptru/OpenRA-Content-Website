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
        
        public statuc function setup()
        {
            $query = "CREATE TABLE users (uid PRIMARY KEY NOT NULL,
                                          pass VARCHAR NOT NULL,
                                          nick VARCHAR NOT NULL,
                                          email VARCHAR NOT NULL,
                                          register_date DATE NOT NULL);";
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

        
        public statuc function check()
        {
            $allSystemsGo = true;
            if(!table_exists("users"))
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