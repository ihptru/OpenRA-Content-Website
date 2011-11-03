<?PHP
	$con = null; //PostgreSQL connection

	class db
	{
		public static function connect()
		{
			$host = "localhost";
            $username = "oramod";
            $password = "iequeiR6";
            $database = "oramod";
            
            $con = pg_connect("host=".$host." port=5432 dbname=".$database." user=".$username." password=".$password);
            if(!$con)
            {
                die("Could not connect: " . pg_last_error());
            }
		}
		
		public static function is_connected()
        {
            return ($con != null);
        }
        
        public static function setup()
        {
            $query = "CREATE TABLE users (uid serial PRIMARY KEY NOT NULL,
                                          pass VARCHAR NOT NULL,
                                          nick VARCHAR NOT NULL,
                                          email VARCHAR NOT NULL,
                                          register_date TIMESTAMP NOT NULL);";
            db_pgsql::executeQuery($query);
            
            $query = "CREATE TABLE maps (uid serial PRIMARY KEY NOT NULL,
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
                                         posted TIMESTAMP NOT NULL);";
            db_pgsql::executeQuery($query);
            
            $query = "CREATE TABLE news (uid serial PRIMARY KEY NOT NULL,
                                         title VARCHAR NOT NULL,
                                         content VARCHAR NOT NULL,
                                         user_id VARCHAR NOT NULL,
                                         posted TIMESTAMP NOT NULL);";
            db_pgsql::executeQuery($query);

            $query = "CREATE TABLE units (uid serial PRIMARY KEY NOT NULL,
                                          title VARCHAR NOT NULL,
                                          description VARCHAR NOT NULL,
                                          user_id VARCHAR NOT NULL,
                                          posted TIMESTAMP NOT NULL);";
            db_pgsql::executeQuery($query);
            
            $query = "CREATE TABLE guide (uid serial PRIMARY KEY NOT NULL,
                                          title VARCHAR NOT NULL,
                                          html_content VARCHAR NOT NULL,
                                          user_id VARCHAR NOT NULL,
                                          posted TIMESTAMP NOT NULL);";
            db_pgsql::executeQuery($query);
        }
        
        private static executeQuery($q)
        {
            $result = pg_query($q);
            if (!$result) {
                $message  = 'Invalid query: ' . pg_last_error() . "\n";
                $message .= 'Whole query: ' . $q;
                die($message);
            }
            return $result;
        }
        
        private static function table_exists($tablename) 
        {
            $res = pg_query("
							SELECT count(*) AS count
							FROM pg_tables
							WHERE schemaname='public' AND tablename = '$tablename';
                            ");
            
            return pg_get_result($res, 0) == 1;
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
            pg_close($con);
            $con = null;
        }
	}

?>
