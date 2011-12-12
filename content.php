<?PHP

class content
{
    public static function head()
    {
	echo "<html><head><title>";
	echo lang::$lang['website_name'];
	echo "</title>";

	//include highslide (image viewer)
	echo "<script type='text/javascript' src='highslide/highslide-with-gallery.js'></script>
	    <link rel='stylesheet' type='text/css' href='highslide/highslide.css' />
	    <script type='text/javascript'>
	    hs.graphicsDir = '../highslide/graphics/';
	    hs.align = 'center';
	    hs.transitions = ['expand', 'crossfade'];
	    hs.outlineType = 'glossy-dark';
	    hs.wrapperClassName = 'dark';
	    hs.fadeInOut = true;

	    //include multi upload file support
	    <script src='multi_upload/multifile_compressed.js'>

	    // Add the controlbar
	    if (hs.addSlideshow) hs.addSlideshow({
	    //slideshowGroup: 'group1',
	    interval: 5000,
	    repeat: false,
	    useControls: true,
	    fixedControls: 'fit',
	    overlayOptions: {
	    opacity: .6,
	    position: 'bottom center',
	    hideOnMouseOut: true
	    }
	    });
	    </script>
	";
	echo "<script type='text/javascript'>
	function confirmDelete()
	{
	    var agree=confirm('Are you sure you want to delete an item?');
	    if (agree)
	    return true ;
	    else
	    return false ;
	}
	</script>
	";
	echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/screen.css\" /></head>";
	
	if( isset($_POST['message']))
	{
	    if (user::online())
	    {
		if (trim($_POST['message']) != "")
		{
		    db::executeQuery("INSERT INTO comments (title, content, user_id, table_id, table_name) VALUES ('','".$_POST['message']."',".user::uid().",".$_GET['id'].",'".$_GET['table']."')");
		}
	    }
	}
	if ( isset($_GET['delete_comment']) and isset($_GET['user_comment']) )
	{
	    $id = $_GET['delete_comment'];
	    $user = $_GET['user_comment'];
	    misc::delete_comment($id, $user);
	    header("Location: {$_SERVER['HTTP_REFERER']}");
	}
	if( isset($_POST['upload_guide_title']) && isset($_POST['upload_guide_text']))
	{
	    if (user::online())
	    {
		if (trim($_POST['upload_guide_text']) != "" && trim($_POST['upload_guide_title']) != "")
		{
		    db::executeQuery("INSERT INTO guides (title, html_content, guide_type, user_id) VALUES ('".$_POST['upload_guide_title']."','".$_POST['upload_guide_text']."','',".user::uid().")");
		}
	    }
	}
	
	if ( isset($_GET['del_item']) and isset($_GET['del_item_table']) and isset($_GET['del_item_user']))
	{
	    $item_id = $_GET['del_item'];
	    $table_name = $_GET['del_item_table'];
	    $user_id = $_GET['del_item_user'];
	    misc::delete_item($item_id, $table_name, $user_id);	//delete item and comments related to it
	    header("Location: /");
	}
	
    }
		
    public static function body_head()
    {
	user::check_logout();
	user::login();
	echo "
	    <div id='header'>
		<a name='top'></a>
		<h1 id='logo-text'><a href='/' title=''>".lang::$lang['website_name']."</a></h1>		
		<p id='slogan'>".lang::$lang['website_slowgun']."</p>
		<div id='nav'>
		<ul>
	";
	content::createMenu();
	echo "</ul>
	    <!-- / id='nav' -->
	    </div>";
	if (!user::online())
	{
	    echo "<div id='login_form'>";
	    content::login_form();
	    echo "
		<!-- / id='login_form' -->
		</div>
		<div id=\"register_link\">
		    <input style=\"position:absolute; right: 130px; top: 15px;\" type=\"checkbox\" name=\"remember\" value=\"yes\" checked title=\"".lang::$lang['remember me']."\">
		    <a href=\"index.php?register\">".lang::$lang['register']."</a>
		</div>
		<div id=\"recover_link\">
		    <a href=\"index.php?recover\">".lang::$lang['recover']."</a>
		</div>
	    ";
	}
	if (isset($_GET['p']))
	{
	    if ($_GET['p'] == "profile")
	    {
		if (user::online())
		{
		    echo "<div id=\"profile_bar\">";
		    profile::profile_bar();
		    echo "</div>";
		}
	    }
	}
	echo "<form id='quick-search' action='index.php' method='GET' >
		<p>
		<label for='qsearch'>Search:</label>
		<input class='tbox' id='qsearch' type='text' name='qsearch' onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'".lang::$lang['search']."':this.value;\" value='".lang::$lang['search']."' />
		<input class='btn' alt='Search' type='image' name='searchsubmit' title='Search' src='images/search.png' />
		</p>
		</form>	
	    <!-- / id='header' -->
	    </div>
	";
    }

    public static function login_form()
    {
	echo "<form method=\"POST\" action=\"\">
	    ".lang::$lang['login'].": <input type=\"text\" name=\"login\">
	    ".lang::$lang['password'].": <input type=\"password\" name=\"pass\">
	    <input type=\"submit\" value=\"".lang::$lang['sign in']."\">
	    <br>
	    </form>
	";
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
	echo "<li id='"; echo pages::current('', $request); echo"'><a href='/'>".lang::$lang['home']."</a></li>";
	echo "<li id='"; echo pages::current('maps', $request); echo"'><a href='index.php?p=maps'>".lang::$lang['maps']."</a></li>";
	echo "<li id='"; echo pages::current('units', $request); echo"'><a href='index.php?p=units'>".lang::$lang['units']."</a></li>";
	echo "<li id='"; echo pages::current('guides', $request); echo"'><a href='index.php?p=guides'>".lang::$lang['guides']."</a></li>";
	echo "<li id='"; echo pages::current('about', $request); echo"'><a href='index.php?p=about'>".lang::$lang['about']."</a></li>";
            
	if (user::online())
	{
	    echo "<li style='float:right;' id=''><a href='index.php?logout'>".lang::$lang['logout']."</a></li>";
	    echo "<li style='float:right;' id='"; echo pages::current('profile', $request); echo"'><a href='index.php?p=profile'>".lang::$lang['profile']."</a></li>";
	}
    }

    public static function create_register_form()
    {
	echo "<form id=\"register_form\" method=\"POST\" action=\"\">
	    <table style=\"text-align:right;\"><tr><td collspan=\"2\"><b>
	    ".lang::$lang['registration']."
	    </b></td></tr><tr><td>
	    ".lang::$lang['login']."</td><td><input type=\"text\" name=\"rlogin\"></td></tr><tr><td>
	    ".lang::$lang['password']."</td><td><input type=\"password\" name=\"rpass\"></td></tr><tr><td>
	    ".lang::$lang['reenter pw']."</td><td><input type=\"password\" name=\"verpass\"></td></tr><tr><td>
	    E-mail</td><td><input type=\"text\" name=\"email\"></td></tr><tr><td>
	    <input type=\"hidden\" name=\"act\">
	    <td>
	";
	require_once('libs/recaptchalib.php');
	$publickey = "6Ldq-soSAAAAADuu6iGZoCiTSOzBcoKXBwlhjM5u";
	echo recaptcha_get_html($publickey);
	
	echo "</td></tr><tr><td><input type=\"submit\" value=\"".lang::$lang['confirm']."\"
	</td></tr></table></form>
	";
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
		    $imagePath = WEBSITE_PATH . $row["path"] . "minimap.bmp";
		    break;
		case "units":
		    $imagePath = $row["preview_image"];
		    break;
		case "guides":
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
		$res = db::executeQuery("SELECT login FROM users WHERE uid = " . $row["user_id"]);
		$username = db::nextRowFromQuery($res);
	    }
	    switch($table)
	    {
		//Set title, image
		case "maps":
		    $title = $row["title"];
		    $subtitle = "posted at " . $row["posted"] . " by " . $username["login"];
		    $text = $row["description"];
		    $imagePath = WEBSITE_PATH . $row["path"] . "minimap.bmp";
		    $imagePath = explode(WEBSITE_PATH, $imagePath);
		    $imagePath = $imagePath[1];
		    break;
		case "units":
		    $title = $row["title"];
		    $subtitle = "posted at " . $row["posted"] . " by " . $username["login"];
		    $text = "";
		    $imagePath = $row["preview_image"];
		    break;
		case "guides":
		    $title = $row["title"];
		    $subtitle = "posted at " . $row["posted"] . " by " . $username["login"];
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
		$content .= "<a href='index.php?p=detail&id=" . $row["uid"] . "&table=" . $table . "' title=''><img src='" . $imagePath . "' alt='featured' style='max-height:350px;max-width:250px;'/></a>";
		$content .= "</div>";
	    }

	    $content .= "<div class='text-block'>";
	    $content .= "<h2><a href='index.html'>" . $title . "</a></h2>"; //index.html? could it be something else..
	    $content .= "<p class='post-info'>" . $subtitle . "</p>";
	    $content .= "<p>" . $text . "</p>";
	    $content .= "<p><a href='index.php?p=detail&id=" . $row["uid"] . "&table=" . $table . "' class='more-link'>Read More</a></p>"; //index.html? could it be something else..
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
	    $imagePath = "";

	    switch($table)
	    {
		//Set title, image
		case "maps":
		    $title = $row["title"];
		    $imagePath = $row["path"] . "minimap.bmp";
		    break;
		case "units":
		    $title = $row["title"];
		    $imagePath = $row["preview_image"];
		    break;
		case "guides":
		    $title = $row["title"];
		    $imagePath = ""; //Get one depending on type of guide (There should be pre made icons for different types)
		    break;
	    }

	    if($counter == 0)
		$content .= "<tr>";

	    $content .= "<td id='map_grid'><a href='index.php?p=detail&table=".$table."&id=".$row["uid"]."'>";
	    if($imagePath != "")
	    	$content .= "<img src='" . $imagePath . "' style='max-height:96px;max-width:96px;'>";
	    $content .= "</br>" . $title . "</a></td>";

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
		    $imagePath = WEBSITE_PATH . $row["path"] . "minimap.bmp";
		    $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
		    $text = $row["description"];
		    break;
		case "units":
		    $title = $row["title"];
		    $imagePath = $row["preview_image"];
		    $subtitle = "posted at " . $row["posted"] . " by " . $row["user_id"];
		    $text = "";
		    break;
		case "guides":
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
    
    public static function displayItem($result, $table)
    {
    	$content = "";
    	while ($row = db::nextRowFromQuery($result))
		{
		
		$content .= "<table>";
		
	    $title = "";
	    $imagePath = "";
	    $subtitle = "";
	    $text = "";
	    $delete = "";
	    
	    $usr = db::nextRowFromQuery(db::executeQuery("SELECT login FROM users WHERE uid = " . $row["user_id"]));
	    $user_name = $usr["login"];
	    
    	switch($table)
	    {
		case "maps":
		    $title = $row["title"];
		    $imagePath = $row["path"] . "minimap.bmp";
		    $subtitle = "posted at " . $row["posted"] . " by " . $user_name;
		    $text = $row["description"];
		    break;
		case "units":
		    $title = $row["title"];
		    $imagePath = $row["preview_image"];
		    $subtitle = "posted at " . $row["posted"] . " by " . $user_name;
		    $text = "";
		    break;
		case "guides":
		    $title = $row["title"];
		    $imagePath = ""; //Get one depending on type of guide (There should be pre made icons for different types)
		    $subtitle = "posted at " . $row["posted"] . " by " . $user_name;
		    $text = $row["html_content"];
		    break;
	     }
	     
	     if ($row["user_id"] == user::uid())
		$delete = "Delete ".rtrim($table,"s");

	     if($imagePath != "")
	     {
	     	$content .= "<tr><td><center><img src='".$imagePath."'></center></td></tr>";
	     }
	     
	     if($title != "")
	     {
	     	$content .= "<tr><td>" . $title;
	     	if($subtitle != "")
	     	{
	     		$content .= " " . $subtitle;
	     	}
	     	$content .= "</td></tr>";
	     }
	     
	     if($text != "")
	     {
	     	$allow = '<table><tr><td><img><a><b><i><u><p>';
	     	$text = strip_tags($text, $allow);
	     	$content .= "<tr><td>".$text."</td></tr>";
	     }
	     
	     if($table == "maps")
	     {
		$mapfile = basename($row["path"]) . ".oramap";
	     	$download = $row["path"] . $mapfile;
	     	$content .= '<tr><td><a href="'.$download.'">Download</a></tr></td>';
	     }
	     
	     if ($delete != "")
		$content .= "<tr><td><a href='index.php?del_item=".$row["uid"]."&del_item_table=".$table."&del_item_user=".$row["user_id"]."' onClick='return confirmDelete()'>".$delete."</a></td></tr>";
	     
	     $content .= "</table>";
	     }
	return $content;
    }

    public static function create_comment_section($result)
    {
	$counter = 0;
	$content = "";

	$comments = db::num_rows($result);
	$content .= "<h3 id='comments'>" . $comments . " Responses</h3>";
	$content .= "<ol class='commentlist'>";

	while ($comment = db::nextRowFromQuery($result))
	{
	    $counter++;
	    $res = db::executeQuery("SELECT * FROM users WHERE uid = " . $comment["user_id"]);
	    $author = db::nextRowFromQuery($res);

	    if($counter > 0)
	    {
		$content .= "<li class='depth-1'>";
		$counter = -1;
	    }
	    else
		$content .= "<li class='thread-alt depth-1'>";

		$avatarImg = misc::avatar($author["avatar"]);
		
                $content .= "<div class='comment-info'>";			
                $content .= "<img alt='' src='" . $avatarImg . "' class='avatar' height='40' width='40' />";
                $content .= "<cite>";
                $content .= "<a href='index.php?profile=".$comment["user_id"]."'>" . $author["login"] . "</a> Says: <br />";
                $content .= "<span class='comment-data'><a href='#comment-63' title=''>" . $comment["posted"] . "</a></span>";
                $content .= "</cite>";
                $content .= "</div>";
                
                $content .= "<div class='comment-text'>";
                $content .= "<p>" . $comment["content"] . "</p>";
		if (misc::comment_owner($comment["user_id"]))
		{
		    $content .= "<a style='float: right; margin: 3px -20px 0 0; border: 1px solid #2C1F18;color:#ff0000;' href='index.php?delete_comment=".$comment["uid"]."&user_comment=".user::uid()."'>delete</a>";
		}
                $content .= "<div class='reply'>";
                //$content .= "<a rel='nofollow' class='comment-reply-link' href='index.html'>Reply</a>"; //index.html?? << need correct page
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
	    $content .= "<form action='index.php?p=detail&table=".$table_name."&id=".$table_id."' method='post' id='commentform'>"; // index.html ?? (Need a page to take form data and put into comments table)
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

	$content .= '<h3>'.lang::$lang['contact_info'].'</h3>';

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
	$content .= '<a href="/">'.lang::$lang['home'].'</a> |';
	$content .= '<strong><a href="#top" class="back-to-top">'.lang::$lang['to top'].'</a></strong>';						
	$content .= '</p>';
	$content .= '</div>';
	
	$content .= '<!-- /footer-bottom -->';		
	$content .= '</div>';

	$content .= '<!-- /footer-outer -->	';	
	$content .= '</div></div>';
	$content .= '<div class="lang">
		    <a id="'.pages::cur_lang("en").'" href="index.php?lang=en">English</a>
		    <a id="'.pages::cur_lang("ru").'" href="index.php?lang=ru">Русский</a>
		    <a id="'.pages::cur_lang("de").'" href="index.php?lang=de">Deutsch</a>
		    <a id="'.pages::cur_lang("sv").'" href="index.php?lang=sv">Swedish</a>
		    </div>
	';

	return $content;
    }
    
    public static function page($page)
    {
	// $page contains function name from `objects` class
	if ($page == "maps")
	{
	    objects::maps();
	}
	elseif ($page == "units")
	{
	    objects::units();
	}
	elseif ($page == "guides")
	{
	    objects::guides();
	}
	elseif ($page == "about")
	{
	    objects::about();
	}
	elseif ($page == "detail")
	{
	    objects::detail();
	}
    }
    
    public static function action($request)
    {
	if ($request == "upload_map")
	{
	    profile::upload_map();
	}
    }
}

class objects
{
    public static function maps()
    {
	echo "<h3>".lang::$lang['maps']."!</h3>";
	if (user::online())
	{
	    echo "<a href='/index.php?action=upload_map&p=maps'>".lang::$lang['upload maps']."</a>";
	}
	$result = db::executeQuery("SELECT * FROM maps GROUP BY maphash");
	echo content::create_grid($result);
    }
    
    public static function units()
    {
	echo "<h3>".lang::$lang['units']."!</h3>";
    }
    
    public static function guides()
    {
	echo "<h3>".lang::$lang['guides']."!</h3>";
	$result = db::executeQuery("SELECT * FROM guides");
	echo content::create_grid($result,"guides");
    }
    
    public static function about()
    {
	echo "<h3>".lang::$lang['about']."!</h3>";
    }
    
    public static function detail()
    {
	$result = db::executeQuery("SELECT * FROM " . $_GET['table'] . " WHERE uid = " . $_GET['id'] . "");
	echo content::displayItem($result, $_GET['table']);
    
	$result = db::executeQuery("SELECT * FROM comments WHERE table_name = '" . $_GET['table'] . "' AND table_id = '" . $_GET['id'] . "'");
	echo content::create_comment_section($result);
	
	echo content::create_comment_respond($_GET['table'],$_GET['id']);
    }
}

class profile
{
    public static function show_profile()
    {
	echo "<h3>".lang::$lang['recent events']."</h3>";
	
	profile::upload_map();
	profile::upload_guide();
	
    }
    
    public static function profile_bar()
    {
	$query = "SELECT avatar FROM users WHERE uid = " . user::uid();
	$result = db::executeQuery($query);
	while ($db_data = db::fetch_array($result))
	{
	    $avatar = $db_data['avatar'];
	}
	if ($avatar == "None")
	{
	    echo "<img src='images/noavatar.jpg' width='120px'>";
	}
    }

    public static function upload_map()
    {
	if (!user::online())
	{
	    return;
	}
	echo "<form id=\"form_class\" enctype=\"multipart/form-data\" method=\"POST\" action=\"\">
		<label>".lang::$lang['choose map upload']." (.oramap): <input type=\"file\" size='30' name=\"map_upload\" /></label>
		<br />
		<input type=\"submit\" name=\"submit\" value=\"".lang::$lang['upload']."\" />
		</form>
	";
            
	$username = user::username();
	$uploaded = upload::upload_oramap($username);
	if ($uploaded != "")
	{
	    if ($uploaded == "exists")
	    {
		echo lang::$lang['map exists'];
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
    
    public static function upload_guide()
    {
    	if(!user::online())
	    return;
    	
    	echo "<form id=\"form_class\" enctype=\"multipart/form-data\" method=\"POST\" action=\"\">
		<label>Upload guide:</label>
		<br />
		<label>Title: <input type='text' name='upload_guide_title' /></label>
		<br />
		<label>Text: <textarea name='upload_guide_text' cols='40' rows='5'></textarea></label>
		<br />
		<input type=\"submit\" name=\"submit\" value=\"".lang::$lang['upload']."\" />
		</form>
	";
    	
    }
}

?>
