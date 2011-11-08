<?PHP

	class db
	{
		public static $con = null; //PostgreSQL connection
		
		public static function connect()
		{
            db::$con = pg_connect("host=".DB_HOST." port=5432 dbname=".DB_DATABASE." user=".DB_USERNAME." password=".DB_PASSWORD);
            if(!db::$con)
            {
                die("Could not connect: " . pg_last_error());
            }
		}
		
		public static function is_connected()
        {
            return (db::$con != null);
        }
        
        public static function nextRowFromQuery($result)
        {
            return pg_fetch_assoc($result);
        }
        
        public static function getTableNameFrom($result, $row)
        {
            return pg_field_table($result, $row);
        }
        
        public static function setup()
        {
            $query = "CREATE TABLE users (uid serial PRIMARY KEY NOT NULL,
                                          pass VARCHAR NOT NULL,
                                          nick VARCHAR NOT NULL,
                                          email VARCHAR NOT NULL,
                                          avatar VARCHAR NOT NULL,
                                          register_date TIMESTAMP NOT NULL);";
            db::executeQuery($query);
            
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
            db::executeQuery($query);
            
            //Used on front page
            $query = "CREATE TABLE articles (uid serial PRIMARY KEY NOT NULL,
                                         title VARCHAR NOT NULL,
                                         content VARCHAR NOT NULL,
                                         image VARCHAR NOT NULL,
                                         user_id VARCHAR NOT NULL,
                                         posted TIMESTAMP NOT NULL);";
            db::executeQuery($query);

            $query = "CREATE TABLE units (uid serial PRIMARY KEY NOT NULL,
                                          title VARCHAR NOT NULL,
                                          description VARCHAR NOT NULL,
                                          preview_image VARCHAR NOT NULL,
                                          user_id VARCHAR NOT NULL,
                                          posted TIMESTAMP NOT NULL);";
            db::executeQuery($query);
            
            //guide_type (modding, mapping, pixel art, utilities,..) each should have different images
            $query = "CREATE TABLE guides (uid serial PRIMARY KEY NOT NULL,
                                          title VARCHAR NOT NULL,
                                          html_content VARCHAR NOT NULL,
                                          guide_type VARCHAR NOT NULL,
                                          user_id VARCHAR NOT NULL,
                                          posted TIMESTAMP NOT NULL);";
            db::executeQuery($query);
            
            //Special table for just featured 
            $query = "CREATE TABLE featured (uid serial PRIMARY KEY NOT NULL,
                                             table VARCHAR NOT NULL,
                                             id INTEGER NOT NULL,
                                             posted TIMESTAMP NOT NULL);";
            db::executeQuery($query);
            
            //Comments made by users on articles
            $query = "CREATE TABLE comments (uid serial PRIMARY KEY NOT NULL,
                                             title VARCHAR NOT NULL,
                                             content VARCHAR NOT NULL,
                                             user_id INTEGER NOT NULL,
                                             article_id INTEGER NOT NULL,
                                             posted TIMESTAMP NOT NULL);";
            db::executeQuery($query);
        }
        
        private static function executeQuery($q)
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
            
            return pg_fetch_result($res, 0) == 1;
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
            pg_close(db::$con);
            db::$con = null;
        }
	}

?>