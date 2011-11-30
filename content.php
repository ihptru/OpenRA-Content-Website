<?PHP
    class content
    {
        public static function head()
        {
			echo "<html><head><title>";
			echo lang::$lang['website_name'];
			echo "</title>";
            
            //include highslide (image viewer)
            echo "<script type='text/javascript' src='highslide/highslide-with-gallery.js'></script>";
            echo "<link rel='stylesheet' type='text/css' href='highslide/highslide.css' />";
            echo "<script type='text/javascript'>";
            echo "hs.graphicsDir = '../highslide/graphics/';";
            echo "hs.align = 'center';";
            echo "hs.transitions = ['expand', 'crossfade'];";
            echo "hs.outlineType = 'glossy-dark';";
            echo "hs.wrapperClassName = 'dark';";
            echo "hs.fadeInOut = true;";
            
            //include multi upload file support
            echo "<script src='multi_upload/multifile_compressed.js'>";
            
            // Add the controlbar
            echo "if (hs.addSlideshow) hs.addSlideshow({";
                //slideshowGroup: 'group1',
                echo "interval: 5000,";
                echo "repeat: false,";
                echo "useControls: true,";
                echo "fixedControls: 'fit',";
                echo "overlayOptions: {";
                    echo "opacity: .6,";
                    echo "position: 'bottom center',";
                    echo "hideOnMouseOut: true";
                echo "}";
            echo "});";
            echo "</script>";
            
            
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
						</div>
						<div id=\"recover_link\">
							<a href=\"index.php?recover\">recover</a>
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
            if (isset($_GET['p']))
            {
				$request = $_GET['p'];
			}
			else
			{
				$request = "";
			}
            echo "<li id='"; echo pages::current('', $request); echo"'><a href='/'>Home</a></li>";
            echo "<li id='"; echo pages::current('maps', $request); echo"'><a href='index.php?p=maps'>Maps</a></li>";
            echo "<li id='"; echo pages::current('units', $request); echo"'><a href='index.php?p=units'>Units</a></li>";
            echo "<li id='"; echo pages::current('guides', $request); echo"'><a href='index.php?p=guides'>Guides</a></li>";
            echo "<li id='"; echo pages::current('about', $request); echo"'><a href='index.php?p=about'>About</a></li>";
            
            if (user::online())
            {
				echo "<li style='float:right;' id=''><a href='index.php?logout'>Logout</a></li>";
				echo "<li style='float:right;' id='"; echo pages::current('profile', $request); echo"'><a href='index.php?p=profile'>Profile</a></li>";
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
			echo "Re-enter password:</td><td><input type=\"password\" name=\"verpass\"></td></tr><tr><td>";
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
                    $row = db::nextRowFromQuery($res);
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
        
        public static function create_grid($result, $table = "maps")
        {
            $columns = 3;
            $counter = 0;
            
            $content = "<table>";
            while ($row = db::nextRowFromQuery($result))
            {
                $title = "";
                $image = "";
                
                switch($table)
                {
                        //Set title, image
                    case "maps":
                        $title = $row["title"];
                        $imagePath = $row["minimap"];
                        break;
                    case "units":
                        $title = $row["title"];
                        $imagePath = $row["preview_image"];
                        break;
                    case "guide":
                        $title = $row["title"];
                        $imagePath = ""; //Get one depending on type of guide (There should be pre made icons for different types)
                        break;
                }
                
                if($counter == 0)
                    $content .= "<tr>";
                
                $content .= "<td><img src='" . $imagePath . "'>" . $title . "</td>";
                
                if($counter > 2)
                {
                    $content .= "</tr>";
                    $counter = -1;
                }
                $counter++;
            }
            if($counter <= 2)
                $content .= "</tr>";
            $content .= "</table>";
            return $content;
        }
        
        public static function create_list($result, $table)
        {
            $content = "<table>";
            while ($row = db::nextRowFromQuery($result))
            {
                $title = "";
                $image = "";
                $subtitle = "";
                $text = "";
                
                switch($table)
                {
                        //Set title, image
                    case "maps":
                        $title = $row["title"];
                        $imagePath = $row["minimap"];
                        $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
                        $text = $row["description"];
                        break;
                    case "units":
                        $title = $row["title"];
                        $imagePath = $row["preview_image"];
                        $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
                        $text = "";
                        break;
                    case "guide":
                        $title = $row["title"];
                        $imagePath = ""; //Get one depending on type of guide (There should be pre made icons for different types)
                        $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
                        $text = "";
                        break;
                }
                
                //TODO: Text should truncate if too large
                $content .= "<tr><td><img src='" . $imagePath . "'></td><td>" . $title . "</br>" . $subtitle . "</br>" . $text . "</td></tr>";
            }
            $content .= "</table>";
            return $content;
        }
        
        public static function create_comment_section($result)
        {
            $counter = 0;
            $content = "";
            
            $comments = mysql_num_rows($result);
            $content .= "<h3 id='comments'>" . $comments . " Responses</h3>";
            $content .= "<ol class='commentlist'>";
            
            
            while ($comment = db::nextRowFromQuery($result))
            {
                $counter++;
                $res = db::executeQuery("SELECT * FROM users WHERE uid = " . $comment["id"]);
                $author = db::nextRowFromQuery($res);
            
                if($counter > 0)
                {
                    $content .= "<li class='depth-1'>";
                    $counter = -1;
                }
                else
                    $content .= "<li class='thread-alt depth-1'>";
                
                $content .= "<div class='comment-info'>";			
                $content .= "<img alt='' src='" . $autor["avatar"] . "' class='avatar' height='40' width='40' />";
                $content .= "<cite>";
                $content .= "<a href='index.html'>" . $autor["login"] . "</a> Says: <br />"; //index.html?? << need correct page
                $content .= "<span class='comment-data'><a href='#comment-63' title=''>" . $comment["posted"] . "</a></span>";
                $content .= "</cite>";
                $content .= "</div>";
                
                $content .= "<div class='comment-text'>";
                $content .= "<p>" . $comment["content"] . "</p>";
                $content .= "<div class='reply'>";
                $content .= "<a rel='nofollow' class='comment-reply-link' href='index.html'>Reply</a>"; //index.html?? << need correct page
                $content .= "</div>";
                $content .= "</div>";
                
                $content .= "</li>";
            }
            
            $content .= "</ol>";
            
            return $content;
        }
        
        public static function create_comment_respond($table_name,$table_id)
        {
            $content = "";
            if(user::online())
            {
                $content .= "<div id='respond'>";
                $content .= "<h3>Leave a Reply</h3>";			
                $content .= "<form action='index.html' method='post' id='commentform'>"; // index.html ?? (Need a page to take form data and put into comments table)
                $content .= "<p>";
                $content .= "<label for='message'>Your Message</label><br />";
                $content .= "<textarea id='message' name='message' rows='10' cols='20' tabindex='4'></textarea>";
                $content .= "</p>";
                $content .= "<p class='no-border'>";
                $content .= "<input class='button' type='submit' value='Submit Comment' tabindex='5'/>";      		
                $content .= "</p>";
                
                $content .= "<input type='hidden' name='user_id' value='" . user::uid() . "'>";
                $content .= "<input type='hidden' name='table_name' value='" . $table_name . "'>";
                $content .= "<input type='hidden' name='table_id' value='" . $table_id . "'>";
                
                $content .= "</form>";
                $content .= "</div>";
            }
            return $content;
        }
        
        //Accept image table
        public static function create_highslide_gallery($result)
        {
            $content = "<div class='highslide-gallery'>";
            
            while ($img = db::nextRowFromQuery($result))
            {
            
                $content .= "<a href='" . $img['path'] . "' class='highslide' onclick='return hs.expand(this)'>";
                $content .= "<img src='" . $img['path_thumb'] . "' alt='Highslide JS'";
                $content .= "title='Click to enlarge' />";
                $content .= "</a>";
                if($img['description'].length > 0)
                {
                    $content .= "<div class='highslide-caption'>";
                    $content .= $img['description'];
                    $content .= "</div>";
                }
            }
            
            $content .= "</div";
            return $content;
        }
        
        public static function create_footer()
        {
            $content = "";
            
            $content .= '<div id="footer-outer" class="clear"><div id="footer-wrap">';
            
            $content .= '<div class="col-a">';
            
			$content .= '<h3>Contact Info</h3>';
			
			$content .= '<p>';
            
            if(in_array("contact_phone",lang::$lang) && strlen(lang::$lang["contact_phone"]) > 0)
            {
                $content .= '<strong>Phone: </strong>' . lang::$lang["contact_phone"] . '<br/>';
            }
            if(in_array("contact_fax",lang::$lang) && strlen(lang::$lang["contact_fax"]) > 0)
            {
                $content .= '<strong>Fax: </strong>' . lang::$lang["contact_fax"];
            }
			$content .= '</p>';
			
            if(in_array("contact_address",lang::$lang) && strlen(lang::$lang["contact_address"]) > 0)
            {
                $content .= '<p><strong>Address: </strong>' . lang::$lang["contact_address"] . '</p>';
            }
            if(in_array("contact_email",lang::$lang) && strlen(lang::$lang["contact_email"]) > 0)
            {
                $content .= '<p><strong>E-mail: </strong>' . lang::$lang["contact_email"] . '</p>';
            }
			//$content .= '<p>Want more info - go to our <a href="#">contact page</a></p>	';		
			
            /*
			$content .= '<h3>Follow Us</h3>';
			$content .= '<div class="footer-list">';
            $content .= '<ul>';
            $content .= '<li><a href="index.html" class="rssfeed">RSS Feed</a></li>';
            $content .= '<li><a href="index.html" class="email">Email</a></li>';
            $content .= '<li><a href="index.html" class="twitter">Twitter</a></li>';								
            $content .= '</ul>';
			$content .= '</div>';
            */
            
            $content .= '</div>';
            
            /*
            $content .= '<div class="col-a">';		

			$content .= '<h3>Site Links</h3>';
			$content .= '<div class="footer-list">';
            $content .= '<ul>';
            $content .= '<li><a href="index.html">Home</a></li>';
            $content .= '<li><a href="index.html">Style Demo</a></li>';
            $content .= '<li><a href="index.html">Blog</a></li>';
            $content .= '<li><a href="index.html">Archive</a></li>';
            $content .= '<li><a href="index.html">About</a></li>';
            $content .= '<li><a href="index.html">Template Info</a></li>';	
            $content .= '<li><a href="index.html">Site Map</a></li>';					
            $content .= '</ul>';
			$content .= '</div>';

            $content .= '</div>';
            
            $content .= '<div class="col-a">';
            
			$content .= '<h3>Web Resource</h3>';
			
			$content .= '<p>Morbi tincidunt, orci ac convallis aliquam, lectus turpis varius lorem, eu
			posuere nunc justo tempus leo. </p>';
			
			$content .= '<div class="footer-list">';
            $content .= '<ul>';
            $content .= '<li><a href="http://themeforest.net?ref=ealigam" title="Site Templates">ThemeForest</a></li>';
            $content .= '<li><a href="http://www.4templates.com/?go=228858961" title="Website Templates">4Templates</a></li>';
            $content .= '<li><a href="http://store.templatemonster.com?aff=ealigam" title="Web Templates">TemplateMonster</a></li>';
            $content .= '<li><a href="http://graphicriver.net?ref=ealigam" title="Stock Graphics">GraphicRiver</a></li>';
            $content .= '<li><a href="http://www.dreamhost.com/r.cgi?287326" title="Web Hosting">Dreamhost</a></li>';
            $content .= '</ul>';
			$content .= '</div>';			
            
            $content .= '</div>';		
            
            $content .= '<div class="col-b">';
            
			$content .= '<h3>About</h3>';			
			
			$content .= '<p>';
			$content .= '<a href="index.html"><img src="images/gravatar.jpg" width="40" height="40" alt="firefox" class="float-left" /></a>';
			$content .= 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec libero. Suspendisse bibendum. 
			Cras id urna. Morbi tincidunt, orci ac convallis aliquam, lectus turpis varius lorem, eu 
			posuere nunc justo tempus leo. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec libero. Suspendisse bibendum. 
			Cras id urna. <a href="index.html">Learn more...</a>';
			$content .= '</p>';	
			
            $content .= '</div>';
             
             */
            
            $content .= '<div class="fix"></div>';
            
            $content .= '<!-- footer-bottom -->';
            $content .= '<div id="footer-bottom">';
            
			$content .= '<div class="bottom-left">';
            if(in_array("copyright",lang::$lang) && strlen(lang::$lang["copyright"]) > 0)
            {
                $content .= '<p>' . lang::$lang["copyright"] . '</p>';
            }
			$content .= '</div>';
            
			$content .= '<div class="bottom-right">';
            $content .= '<p>';
            $content .= '<a href="/">Home</a> |';
            $content .= '<strong><a href="#top" class="back-to-top">Back to Top</a></strong>';						
            $content .= '</p>';
			$content .= '</div>';
            
            $content .= '<!-- /footer-bottom -->';		
            $content .= '</div>';
            
            $content .= '<!-- /footer-outer -->	';	
            $content .= '</div></div>';
            $content .= '<div class="lang">
						<a href="index.php?lang=en">English</a>
						<a href="index.php?lang=ru">Русский</a>
						<a href="index.php?lang=de">Deutsch</a>
						<a href="index.php?lang=sv">Swedish</a>
						</div>
						';
            
            return $content;
        }
    }
    
    class profile
    {
		public static function upload_map()
		{
			echo "<form id=\"form_class\" enctype=\"multipart/form-data\" method=\"POST\" action=\"\">
				<label>Choose a map file(.oramap) to upload: <input type=\"file\" name=\"map_upload\" /></label>
				<br />
				<input type=\"submit\" name=\"submit\" value=\"Upload\" />
            
                <input id='my_file_element' type='file' name='file_1' >
				</form>
            
                <div id='files_list'></div>
                <script>
                    var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 5 );
                    multi_selector.addElement( document.getElementById( 'my_file_element' ) );
                </script>
				";
            
            $username = user::username();
			$uploaded = upload::upload_oramap($username);
			if ($uploaded != "")
			{
				if ($uploaded == "exists")
				{
					echo "This map already exists";
				}
				else
				{
					echo "Uploaded map: " . $uploaded;
					$name = explode(".", $uploaded);
					$image = "users/" . $username . "/maps/" . $name[0] . "/minimap.bmp";
					echo "<img src='" . $image . "'>";
				}
			}
		}
	}
?>
