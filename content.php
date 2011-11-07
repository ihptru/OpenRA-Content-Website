<?PHP
 
    class content
    {
        public static function head()
        {
			echo "<html><head><title>";
			echo lang::$lang['website_name'];
			echo "</title>";
			echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/screen.css\" /></head>";
		}
		
		public static function body_head()
		{
			user::check_logout();
			user::login();
			echo "<div id='header'>
					<a name='top'></a>
					<h1 id='logo-text'><a href='/' title=''>".lang::$lang['website_name']."</a></h1>		
					<p id='slogan'>".lang::$lang['website_slowgun']."</p>

					<div id='nav'>
						<ul>";
							content::createMenu();
			echo "		</ul>		
					</div>";
					if (!user::online())
					{
						echo "<div id='login_form'>";
						content::login_form();
						echo "</div>

						<div id=\"register_link\">
							<a href=\"index.php?register\">register</a>
						</div>";
					}

					echo "<form id='quick-search' action='index.php' method='GET' >
						<p>
						<label for='qsearch'>Search:</label>
						<input class='tbox' id='qsearch' type='text' name='qsearch' value='Search...' title='Start typing and hit ENTER' />
						<input class='btn' alt='Search' type='image' name='searchsubmit' title='Search' src='images/search.png' />
						</p>
					</form>	
				</div>";
		}
		
		public static function login_form()
		{
			echo "<form method=\"POST\" action=\"\">
				".lang::$lang['login'].": <input type=\"text\" name=\"login\">
				".lang::$lang['password'].": <input type=\"password\" name=\"pass\">
				<input type=\"submit\" value=\"".lang::$lang['sign in']."\">
				<br>
				</form>";
		}
		
        public static function createMenu()
        {
            //Should get these from db (dynamic)
            echo "<li id='current'><a href='/'>Home</a></li>";
            echo "<li><a href=''>Style Demo</a></li>";
            echo "<li><a href='blog.html'>Blog</a></li>";
            echo "<li><a href='archives.html'>Archives</a></li>";
            
            if (user::online())
            {
				echo "<li style='float:right;'><a href='index.php?logout'>Logout</a></li>";
				echo "<li style='float:right;'><a href='profile'>Profile</a></li>";
			}
        }
        
        public static function create_register_form()
        {
			echo "<form id=\"register_form\" method=\"POST\" action=\"\">";
			echo "<table style=\"text-align:right;\"><tr><td collspan=\"2\"><b>";
			echo "Registration";
			echo "</b></td></tr><tr><td>";
			echo "Login</td><td><input type=\"text\" name=\"rlogin\"></td></tr><tr><td>";
			echo "Password</td><td><input type=\"password\" name=\"rpass\"></td></tr><tr><td>";
			echo "E-mail</td><td><input type=\"text\" name=\"email\"></td></tr><tr><td>";
		
			echo "<input type=\"hidden\" name=\"act\">";
		
			echo "<input type=\"submit\" value=\"Confirm\">
			</td></tr></table></form>";
		}
        
        //Create image gallery items based on result
        public static function createImageGallery($result)
        {
            $content = "";
            while ($row = db::nextRowFromQuery($result))
            {
                $imagePath = "";
                
                $table = db::getTableNameFrom($row); //not sure at all if this works (not tested)
                switch($table)
                {
                        //Set title, image
                    case "maps":
                        $imagePath = $row["minimap"];
                        break;
                    case "units":
                        $imagePath = $row["preview_image"];
                        break;
                    case "guide":
                        $imagePath = "";
                        break;
                }
                
                $content .= "<a href='index.html'><img src='" . $imagePath . "' width='40' height='40' alt='thumbnail' /></a>";
            }
            return $content;
        }
        
        //Create article items based on result (only accept articles)
        public static function createArticleItems($result)
        {
            $counter = 0;
            $content = "";
            
            while ($row = db::nextRowFromQuery($result))
            {
                $title = $row["title"];
                $text = $row["content"];
                $imagePath = $row["image"];
                $date = $row["posted"];
                $comments = 0;
                
                //Calculates number of comments for that article
                $res = db::executeQuery("SELECT COUNT(uid) FROM comments WHERE article_id = " . $row["uid"]);
                $comments = db::nextRowFromQuery($res);
                
                $counter++;
                if($counter == 1)
                {
                    $content .= "<div class='block odd'>";
                    $counter = -1;
                }
                else
                {
                    $content .= "<div class='block even'>";
                    $content .= "<div class='fix'></div>";
                }
                
                if(strlen($imagePath) > 0)
                    $content .= "<a title='' href='index.html'><img src='" . $imagePath . "' class='thumbnail' alt='img' width='240px' height='100px'/></a>";
                
                $content .= "<div class='blk-top'>";
                $content .= "<h4><a href='index.html'>" . $title . "</a></h4>";
                $content .= "<p><span class='datetime'>" . $date . "</span><a href='index.html' class='comment'>" . $comments . " Comments</a></p>";
                $content .= "</div>";
                
                $content .= "<div class='blk-content'>";
                $content .= "<p>" . $text . "</p>";			
                $content .= "<p><a class='more' href='index.html'>continue reading &raquo;</a></p>"; 
                //index.html need to be fixed (should be link to article)
                $content .= "</div>";
                $content .= "</div>";
            }
            if($counter != 0)
                $content .= "<div class='fix'></div>";
            return $content;
        }
        
        //Creates featured items based on result
        public static function createFeaturedItems($result, $table = "featured")
        {
            $content = "";
            while ($row = db::nextRowFromQuery($result))
            {
                $title = "";
                $subtitle = "";
                $text = "";
                $imagePath = "";
                
                //$table = db::getTableNameFrom($row); //not sure at all if this works (not tested)
                if($table == "featured")
                {
                    //Get row for featured post
                    // Why have a featured table when you can use maps/units/guides/.. ?
                    // Answer: In featured you can combine different elements if you wish (maps and units)
                    $table = $row["table_name"];
                    $res = db::executeQuery("SELECT * FROM " . $table . " WHERE uid = " . $row["id"]);
                    $row = db::nextRowFromQuery($result);
                }
                switch($table)
                {
                    //Set title, image
                    case "maps":
                        $title = $row["title"];
                        $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
                        $text = $row["description"];
                        $imagePath = $row["minimap"];
                        break;
                    case "units":
                        $title = $row["title"];
                        $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
                        $text = "";
                        $imagePath = $row["preview_image"];
                        break;
                    case "guide":
                        $title = $row["title"];
                        $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
                        $text = "";
                        $imagePath = "";
                        break;
                }
                //Should get these from db
                $content .= "<div id='featured-block' class='clear'>";
                $content .= "<div id='featured-ribbon'></div>";//<< Maybe have different ribbons? ex: featured, editors choice, peoples choice,...
                $content .= "<a name='TemplateInfo'></a>";
                
                if(strlen($imagePath) > 0)
                {
                    $content .= "<div class='image-block'>";
                    $content .= "<a href='index.html' title=''><img src='" . $imagePath . "' alt='featured' width='350px' height='250px'/></a>";
                    $content .= "</div>";
                }
                
                $content .= "<div class='text-block'>";
                $content .= "<h2><a href='index.html'>" . $title . "</a></h2>"; //index.html? could it be something else..
                $content .= "<p class='post-info'>" . $subtitle . "</p>";
                $content .= "<p>" . $text . "</p>";
                $content .= "<p><a href='index.html' class='more-link'>Read More</a></p>"; //index.html? could it be something else..
                                                                                           //All use read more button?
                $content .= "</div>";
                $content .= "</div>";
            }
            
            return $content;
        }
    }
    
    class profile
    {
		public static function upload_map()
		{
			echo "<form enctype=\"multipart/form-data\" method=\"POST\" action=\"\">
				<label>Choose a map file(.oramap) to upload: <input type=\"file\" name=\"oramap_f\" /></label>
				<br />
				<input type=\"submit\" name=\"submit\" value=\"Upload\" />
				</form>
				";
			$uploaded = upload::upload_oramap("/home/oramod/www/", "ihptru", "oramap_f");
			echo $uploaded;
		}
	}
?>
