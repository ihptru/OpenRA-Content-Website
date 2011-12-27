<?PHP

class content
{
    public static function head()
    {
	if( isset($_POST['message']))
	{
	    if (user::online())
	    {
		if (trim($_POST['message']) != "")
		{
		    db::executeQuery("INSERT INTO comments (title, content, user_id, table_id, table_name) VALUES ('','".$_POST['message']."',".user::uid().",".$_GET['id'].",'".$_GET['table']."')");
		    misc::increase_experiance(5);
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
	if( isset($_POST['upload_guide_title']) && isset($_POST['upload_guide_text']) && isset($_POST['upload_guide_type']))
	{
	    if (user::online())
	    {
		if (trim($_POST['upload_guide_text']) != "" && trim($_POST['upload_guide_title']) != "" && trim($_POST['upload_guide_type'] != ""))
		{
		    db::executeQuery("INSERT INTO guides (title, html_content, guide_type, user_id) VALUES ('".$_POST['upload_guide_title']."','".$_POST['upload_guide_text']."','".$_POST['upload_guide_type']."',".user::uid().")");
		    misc::increase_experiance(50);
		}
	    }
	}
	if ( isset($_GET["table"]) && isset($_GET["id"]) )
	{
	    if (user::online())
	    {
		if(isset($_GET["fav"]))
		{
		    if( db::nextRowFromQuery(db::executeQuery("SELECT * FROM fav_item WHERE table_name = '".$_GET["table"]."' AND table_id = ".$_GET["id"]." AND user_id = " . user::uid())) )
		    {
			db::executeQuery("DELETE FROM fav_item WHERE table_name = '".$_GET["table"]."' AND table_id = ".$_GET["id"]." AND user_id = ".user::uid());
		    }
		    else
		    {
			db::executeQuery("INSERT INTO fav_item (user_id,table_name,table_id) VALUES (".user::uid().",'".$_GET["table"]."','".$_GET["id"]."')");
		    }
		    header("Location: {$_SERVER['HTTP_REFERER']}");
		}
		else if(isset($_GET["report"]))
		{
		    if( db::nextRowFromQuery(db::executeQuery("SELECT * FROM reported WHERE table_name = '".$_GET["table"]."' AND table_id = ".$_GET["id"]." AND user_id = " . user::uid())) )
		    { } else {
			db::executeQuery("INSERT INTO reported (table_name, table_id, user_id) VALUES ('".$_GET["table"]."', ".$_GET["id"].", ".user::uid() . ")");
		    }
		}
	    }
	}
	
	if ( isset($_GET['del_item']) and isset($_GET['del_item_table']) and isset($_GET['del_item_user']))
	{
	    $item_id = $_GET['del_item'];
	    $table_name = $_GET['del_item_table'];
	    $user_id = $_GET['del_item_user'];
	    misc::delete_item($item_id, $table_name, $user_id);	//delete item and comments related to it
	    header("Location: /index.php?p=$table_name");
	}

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
	function confirmDelete(item)
	{
	    var agree=confirm('Are you sure you want to delete this '+item+'?');
	    if (agree)
	    return true ;
	    else
	    return false ;
	}
	</script>
	<script src='libs/multifile.js'>
	//inlucde multi upload form
	</script>
	<script src='libs/strip_tags.js'>
	//inlucde strip_tags function
	</script>
	";
	echo '<script type="text/javascript" src="libs/password/jquery.js"></script>
		  <script type="text/javascript" src="libs/password/mocha.js"></script>';
	echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/screen.css\" /></head>";
    }
		
    public static function body_head()
    {
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
	echo "<form id='quick-search' action='index.php' method='GET'>
		<p>
		<label for='qsearch'>Search:</label>
		<input class='tbox' id='qsearch' type='text' name='qsearch' onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'".lang::$lang['search']."':this.value;\" value='".lang::$lang['search']."' />
		<input class='btn' alt='Search' type='image' name='searchsubmit' title='Search' src='images/search.png' />
		<input type='hidden' name='p' value='search'>
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
	    <input style=\"position:absolute; right: -25px; top: 15px;\" type=\"checkbox\" name=\"remember\" value=\"yes\" checked title=\"".lang::$lang['remember me']."\">
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
	    ".lang::$lang['password']."</td><td><input type=\"password\" id=\"inputPassword\" name=\"rpass\">
	    <div id=\"complexity\" class=\"default\">Password security</div></td></tr><tr><td>
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
		    $imagePath = $row["path"] . "minimap.bmp";
		    break;
		case "units":
		    $imagePath = $row["preview_image"];
		    break;
		case "guides":
		    $imagePath = "";
		    break;
	    }

	    $content .= "<a href='index.php?p=detail&table=".$table."&id=".$row["uid"]."'><img src='" . $imagePath . "' width='40' height='40' alt='thumbnail' /></a>";
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
	    $res = db::executeQuery("SELECT COUNT(uid) as count FROM comments WHERE table_name='articles' AND table_id = " . $row["uid"]);
	    $comment = db::nextRowFromQuery($res);
		$comments = $comment['count'];

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
        $content .= "<h4><a href='index.php?p=detail&table=articles&id=".$row["uid"]."'>" . $title . "</a></h4>";
        $content .= "<p><span class='datetime'>" . $date . "</span><a href='index.php?p=detail&table=articles&id=".$row["uid"]."' class='comment'>" . $comments . " Comments</a></p>";
        $content .= "</div>";
                
        $content .= "<div class='blk-content'>";
        if(strlen($text) > 500)
        	$text = substr($text,0,500) . "...";
        $content .= "<p>" . $text . "</p>";			
        $content .= "<p><a href='index.php?p=detail&table=articles&id=".$row["uid"]."' class='more-link'>continue reading &raquo;</a></p>"; 
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
	    $t = "";
	    if($table == "featured")
	    {
		$table_item = $row["table_name"];
		$t = $row["type"];
		$res = db::executeQuery("SELECT * FROM " . $table_item . " WHERE uid = " . $row["id"]);
		$row = db::nextRowFromQuery($res);
		$res = db::executeQuery("SELECT login FROM users WHERE uid = " . $row["user_id"]);
		$username = db::nextRowFromQuery($res);
	    }
	    switch($table_item)
	    {
		case "maps":
		    $title = $row["title"];
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?p=profile&profile=".$row["user_id"]."'>" . $username["login"] . "</a>";
		    $text = $row["description"];
		    $imagePath =  $row["path"] . "minimap.bmp";
		    break;
		case "units":
		    $title = $row["title"];
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?p=profile&profile=".$row["user_id"]."'>" . $username["login"] . "</a>";
		    $text = "";
		    $imagePath = $row["preview_image"];
		    break;
		case "guides":
		    $title = $row["title"];
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?p=profile&profile=".$row["user_id"]."'>" . $username["login"] . "</a>";
		    $text = "";
		    $imagePath = "images/guide_" . $row["guide_type"] . ".png";
		    break;
	    }
	    //Should get these from db
	    $content .= "<div id='featured-block' class='clear'>";
		if($t=="featured")
                	$content .= "<div id='featured-ribbon'></div>";
		else if($t=="people")
			$content .= "<div id='peoples-ribbon'></div>";
		else if($t=="editors")
                	$content .= "<div id='editors-ribbon'></div>";
		else
                	$content .= "<div id='featured-ribbon'></div>";
	    $content .= "<a name='TemplateInfo'></a>";

	    if(strlen($imagePath) > 0)
	    {
		$content .= "<div class='image-block'>";
		$content .= "<a href='index.php?p=detail&id=" . $row["uid"] . "&table=" . $table_item . "' title=''><img src='" . $imagePath . "' alt='featured' style='max-height:350px;max-width:250px;'/></a>";
		$content .= "</div>";
	    }

	    $content .= "<div class='text-block'>";
	    $content .= "<h2>" . strip_tags($title) . "</h2>";
	    $content .= "<p class='post-info'>" . $subtitle . "</p>";
	    $content .= "<p>" . strip_tags($text) . "</p>";
	    $content .= "<p><a href='index.php?p=detail&id=" . $row["uid"] . "&table=" . $table_item . "' class='more-link'>Read More</a></p>";
											//All use read more button?
	    $content .= "</div>";
	    $content .= "</div>";
	}

	return $content;
    }

    public static function create_grid($result, $table = "maps")
    {
	//Setup\\
	$columns = 4;	//Amount of columns
	$rows = 4;	//Amount of rows (before starting paging)
	$counter = 0;
	$columns--;
	$maxItemsPerPage = ($columns+1) * $rows;
	$content = "<table>";
	$total = db::num_rows($result);
	$i = 0;
	if(isset($_GET["current_grid_page_".$table]))
		$current = $_GET["current_grid_page_".$table];
	else
		$current = 1;
	if (db::num_rows($result) == 0)
	    return "";
	while ($row = db::nextRowFromQuery($result))
	{
		if( !($i >= ($current-1) * $maxItemsPerPage && $i < $current * $maxItemsPerPage ) )
		{
			$i++;
			continue;
		}
		$i++;
		
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
		    $imagePath = "";//$row["preview_image"];
		    break;
		case "guides":
		    $title = $row["title"];
		    $imagePath = "images/guide_" . $row["guide_type"] . ".png";
		    break;
	    }

	    if($counter == 0)
		$content .= "<tr>";

	    $content .= "<td id='map_grid'><a href='index.php?p=detail&table=".$table."&id=".$row["uid"]."'>";
	    if($imagePath != "")
	    	$content .= "<img src='" . $imagePath . "' style='max-height:96px;max-width:96px;'>";
	    $content .= "</br>" . strip_tags($title) . "</a></td>";

	    if($counter > 2)
	    {
		$content .= "</tr>";
		$counter = -1;
	    }
	    $counter++;
	}
	if($counter <= 2)
	    $content .= "</tr>";
	    
	//Print pages
	$nrOfPages = floor(($total-0.01) / $maxItemsPerPage) + 1;
	$pages = "<table><tr>";
	
	$gets = "";
	$keys = array_keys($_GET);
	foreach($keys as $key)
	{
		if($key != "current_grid_page_".$table)
			$gets .= "&" . $key . "=" . $_GET[$key];
	}
	for($i = 1; $i < $nrOfPages+1; $i++)
	{
		if($current == $i)
			$pages .= "<td>" . $i . "</td>";
		else
			$pages .= "<td id='page_count'><a href='index.php?current_grid_page_".$table."=".$i.$gets."'>" . $i . "</a></td>";
	}
	$pages .= "</tr></table>";
	if ($nrOfPages == 1)
	{ $pages = ""; }

	$content .= "</table>";
	$content .= $pages;
	return $content;
    }

    public static function create_list($result, $table)
    {
	if (db::num_rows($result) == 0)
	    return "";
	if(isset($_GET["current_list_page_".$table]))
		$current = $_GET["current_list_page_".$table];
	else
		$current = 1;
	$maxItemsPerPage = 10; //dynamic depending on table?
	$total = db::num_rows($result);
	$content = "<table>";
	$i = 0;
	while ($row = db::nextRowFromQuery($result))
	{
		if( !($i >= ($current-1) * $maxItemsPerPage && $i < $current * $maxItemsPerPage ) )
		{
			$i++;
			continue;
		}
		$i++;
	
	    $title = "";
	    $imagePath = "";
	    $subtitle = "";
	    $text = "";

	    $usr = db::nextRowFromQuery(db::executeQuery("SELECT login FROM users WHERE uid = " . $row["user_id"]));
	    $username = $usr["login"];

	    switch($table)
	    {
		//Set title, image
		case "maps":
		    $title = $row["title"];
		    $imagePath = $row["path"] . "minimap.bmp";
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?p=profile&profile=".$row["user_id"]."'>" . $username . "</a>";
		    $text = $row["description"];
		    break;
		case "units":
		    $title = $row["title"];
		    $imagePath = "";//$row["preview_image"];
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?p=profile&profile=".$row["user_id"]."'>" . $username . "</a>";
		    $text = "";
		    break;
		case "guides":
		    $title = $row["title"];
		    $imagePath = "images/guide_" . $row["guide_type"] . ".png";
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?p=profile&profile=".$row["user_id"]."'>" . $username . "</a>";
		    $text = "";
		    break;
		case "articles":
		    $title = $row["title"];
		    $imagePath = "";
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?p=profile&profile=".$row["user_id"]."'>" . $username . "</a>";
		    $text = "";
		    break;
	    }
	    
	    //TODO: Text should truncate if too large
	    $content .= "<tr>";
	    if($imagePath != "")
	    	$content .= "<td><img src='" . $imagePath . "'></td>";
	    $content .= "<td><a href='index.php?p=detail&table=".$table."&id=".$row["uid"]."'>" . strip_tags($title) . "</a></br>" . $subtitle . "</br>" . strip_tags($text) . "</td></tr>";
	}
	
	$nrOfPages = floor(($total-0.01) / $maxItemsPerPage) + 1;
	$gets = "";
	$pages = "<table>";
	$keys = array_keys($_GET);
	foreach($keys as $key)
	{
		if($key != "current_list_page_".$table)
			$gets .= "&" . $key . "=" . $_GET[$key];
	}
	for($i = 1; $i < $nrOfPages+1; $i++)
	{
		if($current == $i)
			$pages .= "<td>" . $i . "</td>";
		else
			$pages .= "<td id='page_count'><a href='index.php?current_list_page_".$table."=".$i.$gets."'>" . $i . "</a></td>";
	}
	$pages .= "</tr></table>";
	if ($nrOfPages == 1)
	{ $pages = ""; }
	
	$content .= "</table>";
	$content .= $pages;
	return $content;
    }
    
    public static function displayItem($result, $table, $resultNotQuery = false)
    {
    	$content = "";
	$flag = false;
	
    	while (1 == 1)
	{
	    if($resultNotQuery == false)
	    {
		if($row = db::nextRowFromQuery($result))
		{ } else { break;}
	    }
	    else
	    {
		if($flag)
		    break;
		else
		    $flag = true;
		$row = $result;
	    }

	    $title = "";
	    $imagePath = "";
	    $subtitle = "";
	    $text = "";
	    $delete = "";
	    
	    $usr = db::nextRowFromQuery(db::executeQuery("SELECT login FROM users WHERE uid = " . $row["user_id"]));
	    $user_name = $usr["login"];
	    
	    $reported = "";
	    if ($row["user_id"] == user::uid())
	    {
		if(isset($row["uid"]))
		{
		    $delete = "Delete ".rtrim($table,"s");
		    $delete = "<a href='index.php?del_item=".$row["uid"]."&del_item_table=".$table."&del_item_user=".$row["user_id"]."' onClick='return confirmDelete(\"".rtrim($table,"s")."\")'>".$delete."</a>";
		}
	    }
	    else
	    {
		if(db::nextRowFromQuery(db::executeQuery("SELECT * FROM reported WHERE table_name = '".$table."' AND table_id = ".$row["uid"]." AND user_id = " . user::uid())))
		{
		    $reported = "You already reported this item";
		}
		else
		{
		    if(user::online())
			$reported = "<a href='index.php?p=detail&table=".$table."&id=".$row["uid"]."&report'>Report Item</a>";
		}
	    }
	    if(isset($row["uid"]))
	    {
		$favIcon = "notFav.png";
		if( db::nextRowFromQuery(db::executeQuery("SELECT * FROM fav_item WHERE table_name = '".$table."' AND table_id = ".$row["uid"]." AND user_id = " . user::uid())) ) {
		    $favIcon = "isFav.png";
		}
	    }
	    switch($table)
	    {
		case "maps":
		    $title = $row["title"];
		    $imagePath = $row["path"] . "minimap.bmp";
		    $subtitle = "posted at " . $row["posted"] . " by " . "<a href='index.php?p=profile&profile=".$row["user_id"]."'>". $user_name . "</a>";
		    $text = $row["description"];
		    break;
		case "units":
		    $title = $row["title"];
		    $imagePath = "";//$row["preview_image"];
		    $subtitle = "posted at " . $row["posted"] . " by " . "<a href='index.php?p=profile&profile=".$row["user_id"]."'>". $user_name . "</a>";
		    $text = "";
		    break;
		case "guides":
		    $imagePath = "images/guide_" . $row["guide_type"] . ".png";
		    $allow = '<table><tr><td><img><a><b><i><u><p>';
		    $text = strip_tags($row["html_content"], $allow);
		    
		    $content .= "<div class='post'>";
		    $content .= "<h2 id='id_display_title'>" . strip_tags($row["title"]) . "</h2>";
		    $content .= "<p class='post-info'>Posted by <a href='index.php?p=profile&profile=".$row["user_id"]."' id='id_display_username'>". $user_name . "</a></p>";
		    $content .= "<p><div id='id_display_text'>" . $text . "</div></p>";
		    $content .= "<p class='postmeta'>";
		    if($reported != "")
			$content .= $reported . " | ";
		    if($delete != "")
			$content .= $delete . " | ";
		    $content .= "<span class='date'>".$row["posted"]."</span>";
		    $content .= "</p>";
		    $content .= "</div>";
		    return $content;
		    break;
		case "articles":
		    $imagePath = $row["image"];
		    $allow = '<table><tr><td><img><a><b><i><u><p>';
		    $text = strip_tags($row["content"], $allow);
		    
		    $content .= "<div class='post'>";
		    $content .= "<h2 id='id_display_title'>" . strip_tags($row["title"]) . "</h2>";
		    $content .= "<p class='post-info'>Posted by <a href='index.php?p=profile&profile=".$row["user_id"]."'>". $user_name . "</a></p>";
		    $content .= "<p><div id='id_display_text'>" . $text . "</div></p>";
		    $content .= "<p class='postmeta'>";
		    if($delete != "")
			$content .= $delete . " | ";
		    $content .= "<span class='date'>".$row["posted"]."</span>";
		    $content .= "</p>";
		    $content .= "</div>";
		    return $content;
		    break;
	     }
	     
	     $content .= "<table>";
	     
	     if($imagePath != "")
	     {
	     	$content .= "<tr><td><center><img src='".$imagePath."'></center></td></tr>";
	     }
	     
	     $content .= "<tr><td>" . strip_tags($title);
	     if($subtitle != "")
	     {
	     	$content .= " " . $subtitle;
	     }
	     $content .= "</td>";
	     
	     if(user::online())
	     {
	     	$content .= "<td style='padding: .5em .5em;'><a href='index.php?p=detail&table=".$table."&id=".$row["uid"]."&fav'><img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/".$favIcon."'></a></td>";
	     }
	     $content .= "</tr>";
	     
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
	     else if($table == "units")
	     {
	     	$content .= "<tr><td>Description: " . strip_tags($row["description"]) . "</td></tr>";
	     	$content .= "<tr><td><br>Files (click to download):";
	     	$directory = "users/".$user_name."/units/".$title."/";
		$shapes = glob($directory . "*.*");
		foreach($shapes as $shape)
		{
		    $content .= "<br><a href='".$shape."'>".basename($shape)."</a>";
		}
		$content .= "</td></tr>";
	     }
	     
	     if ($delete != "")
		$content .= "<tr><td>".$delete."</td></tr>";
	     
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
        $content .= "<a href='index.php?p=profile&profile=".$comment["user_id"]."'><img alt='' src='" . $avatarImg . "' class='avatar' height='45' width='45' /></a>";
        $content .= "<cite>";
        $content .= "<a href='index.php?p=profile&profile=".$comment["user_id"]."'>" . $author["login"] . "</a> Says: <br />";
        $content .= "<span class='comment-data'><a href='#comment-63' title=''>" . $comment["posted"] . "</a></span>";
    	$content .= "</cite>";
        $content .= "</div>";
                
        $content .= "<div class='comment-text'>";
        $content .= "<p>" . strip_tags($comment["content"]) . "</p>";
		if (misc::comment_owner($comment["user_id"]))
		{
		    $content .= "<a style='float: right; margin: -25px 12px 0 0; border: 1px solid #2C1F18;color:#ff0000;' href='index.php?delete_comment=".$comment["uid"]."&user_comment=".user::uid()."' onClick='return confirmDelete(\"comment\")'><img src='images/delete.png' style='border: 0px solid #261b15; padding: 0px;' border='0' /></a>";
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
    
    public static function create_dynamic_list($data, $columns, $name = "dyn", $maxItemsPerPage = 10, $header = false, $use_pages = true)
    {
    	$content = "";
    	if($data && $columns > 0)
    	{
    		if(count($data)%$columns == 0)
    		{
			$total = count($data);
			if(isset($_GET["current_dynamic_page_".$name]))
				$current = $_GET["current_dynamic_page_".$name];
			else
				$current = 1;
			$start = ($current-1) * $maxItemsPerPage * $columns;
			$maxItemsPerPage *= $columns;
			$content .= "<table>";
			if($header)
			{
				if($start < $columns)
					$start = $columns;
				$content .= "<tr>";
				for($row = 0; $row < $columns; $row++)
    				{
    					$content .= "<th>" . $data[$row] . "</th>";
    				}
				$content .= "</tr>";
			}
    			for($i = $start; $i < count($data)+1-$columns && $i < $start+$maxItemsPerPage; $i=$i+$columns)
    			{
    				$content .= "<tr>";
    				for($row = 0; $row < $columns; $row++)
    				{
    					$content .= "<td>" . $data[$i+$row] . "</td>";
    				}
    				$content .= "</tr>";
    			}
    			$content .= "</table>";
			$nrOfPages = floor(($total-0.01) / $maxItemsPerPage) + 1;
			$gets = "";
			$pages = "<table>";
			$keys = array_keys($_GET);
			foreach($keys as $key)
			{
				if($key != "current_dynamic_page_".$name)
					$gets .= "&" . $key . "=" . $_GET[$key];
			}
			for($i = 1; $i < $nrOfPages+1; $i++)
			{
				if($current == $i)
					$pages .= "<td>" . $i . "</td>";
				else
					$pages .= "<td id='page_count'><a href='index.php?current_dynamic_page_".$name."=".$i.$gets."'>" . $i . "</a></td>";
			}
			$pages .= "</tr></table>";
			if ($nrOfPages == 1)
				$pages = "";
			if($use_pages)
				$content .= $pages;
    		}
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
	$content .= '<strong><a href="index.php?p=members">Members</a></strong> |';
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
	elseif ($page == "search")
	{
		objects::search();
	}
	elseif ($page == "members")
	{
	    $result = db::executeQuery("SELECT * FROM users");
	    if (db::num_rows($result) > 0)
	    {
		echo "<label>Members:</label>";
	    	$data = array();
		while ($row = db::nextRowFromQuery($result))
		{
		    $avatar = $row["avatar"];
		    if ($avatar == "None")
			$avatar = "images/noavatar.jpg";
		    array_push($data,"<a href='index.php?p=profile&profile=".$row["uid"]."'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='index.php?p=profile&profile=".$row["uid"]."'>".$row["login"]."</a>");
		}
		echo content::create_dynamic_list($data,2);
	    }
	}
    }
    
    public static function action($request)
    {
	if ($request == "upload_map")
	{
	    if (!user::online())
		return;
	    profile::upload_map();
	}
	if ($request == "mymaps")
	{
	    if (!user::online())
		return;
	    profile::upload_map();
	    echo "<h3>Your maps</h3>";
	    $result = db::executeQuery("SELECT * FROM maps WHERE user_id = ".user::uid());
	    $output = content::create_grid($result);
	    if ($output == "")
	    {
		echo "No maps uploaded yet";
	    }
	    echo $output;
	}
	if ($request == "myguides")
	{
	    if (!user::online())
		return;
	    profile::upload_guide();
	    echo "<h3>Your guides</h3>";
	    $result = db::executeQuery("SELECT * FROM guides WHERE user_id = ".user::uid());
	    $output = content::create_grid($result, "guides");
	    if ($output == "")
	    {
		echo "No guides uploaded yet";
	    }
	    echo $output;
	}
	if ($request == "myunits")
	{
	    if (!user::online())
		return;
	    profile::upload_unit();
	    echo "<h3>Your units</h3>";
	    $result = db::executeQuery("SELECT * FROM units WHERE user_id = ".user::uid());
	    $output = content::create_grid($result, "units");
	    if ($output == "")
	    {
		echo "No units uploaded yet";
	    }
	    echo $output;
	}
	if ($request == "display_faction")
	{
	    if (isset($_GET["faction"]))
	    {
		$faction = $_GET["faction"];
		$result = db::executeQuery("SELECT uid,login,avatar FROM users WHERE fav_faction = '".$faction."'");
		if (db::num_rows($result) > 0)
		{
		    $data = array();
		    array_push($data,"","This people like ".$faction." faction:");
		    while ($row = db::nextRowFromQuery($result))
		    {
			$avatar = $row["avatar"];
			if ($avatar == "None")
			    $avatar = "images/noavatar.jpg";
			array_push($data,"<a href='index.php?p=profile&profile=".$row["uid"]."'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='index.php?p=profile&profile=".$row["uid"]."'>".$row["login"]."</a>");
		    }
		    echo content::create_dynamic_list($data,2);
    		}
		else
		{
		    echo "<table>
			      <tr>
				  <td>No one likes ".$faction."</td>
			      </tr>
			  </table>
		    ";
		}
	    }
	}
	if ($request == "show_favorited")
	{
	    if (isset($_GET["favorited_id"]))
	    {
		$result = db::executeQuery("SELECT * FROM fav_item WHERE user_id = " . $_GET["favorited_id"] . " ORDER BY posted DESC");
		$usr = db::nextRowFromQuery(db::executeQuery("SELECT login FROM users WHERE uid = ".$_GET["favorited_id"]));
    		if (db::num_rows($result) > 0) {
		    $data = array();
		    array_push($data,"","<a href='index.php?p=profile&profile=".$_GET["favorited_id"]."'>".$usr["login"]."</a>'s latest favorited items:");
		    while ($row = db::nextRowFromQuery($result)) {
			$item = db::nextRowFromQuery(db::executeQuery("SELECT * FROM " . $row["table_name"] . " WHERE uid = " . $row["table_id"]));
			if($item) {
			    array_push($data,"<img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/isFav.png'>");
			    array_push($data,"favorited the ". substr($row["table_name"],0,strlen($row["table_name"])-1) ." \"<a href='index.php?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".$item["title"]."</a>\" at ".$row["posted"]."");
			}
		    }
		    echo content::create_dynamic_list($data,2,"favorites",10,true,true);
    		}
	    }
	}
    }
}

class objects
{
    public static function maps()
    {
	echo "<h3>".lang::$lang['maps']."!</h3>";
	$result = db::executeQuery("SELECT * FROM maps GROUP BY maphash");
	echo content::create_grid($result);
    }
    
    public static function units()
    {
	echo "<h3>".lang::$lang['units']."!</h3>";
	$result = db::executeQuery("SELECT * FROM units");
	echo content::create_grid($result,"units");
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
    
    public static function search()
    {
    	if(isset($_GET["qsearch"]))
    	{
	    if (trim($_GET["qsearch"]) == "")
	    {
		echo "<table>
			  <tr>
			      <td>Empty request</td>
			  </tr>
		      </table>
		";
		return;
	    }
	    
	    $content = "";
	    $search = $_GET["qsearch"];
	    $found = false;

	    $searchArray = array("maps","guides","articles","units");
	    foreach($searchArray as $value)
	    {
		
		$result = db::executeQuery("SELECT * FROM ".$value." WHERE title LIKE '%".$search."%'");
		$output = content::create_list($result, $value);
		if ($output != "")
		{
		    $content .= "<br><label>".$value." found:</label>";
		    $content .= $output;
		}
	    }

	    $result = db::executeQuery("SELECT * FROM users WHERE login LIKE '%".$search."%'");
	    if (db::num_rows($result) > 0)
	    {
		$content .= "<br><label>users found:</label>";
	    	$data = array();
			while ($row = db::nextRowFromQuery($result))
		    	array_push($data,"<a href='index.php?p=profile&profile=".$row["uid"]."'>".$row["login"]."</a>");
		    $content .= content::create_dynamic_list($data,1);
	    }
	    if ($content == "")
	    {
		echo "<table>
			  <tr>
			      <td>Nothing found</td>
			  </tr>
		      </table>
		";
		return;
	    }
	    echo $content;
    	}
    }
}

class profile
{
    public static function show_profile()
    {
    	//Get user id
    	$self = -1;
    	if(isset($_GET["profile"]))
    		$self = $_GET["profile"];
    	if($self == -1)
    		$self = user::uid();
    	
    	$query = "SELECT * FROM users WHERE uid = " . $self;
    	$result = db::executeQuery($query);
    	$usr = db::nextRowFromQuery($result);
    	
    	$gender = "";
    	if($usr["gender"]==1)
    		$gender = "male";
    	else
    		$gender = "female";
    	
    	if(user::uid() == $self && user::online() && isset($_GET["edit"]))
    	{
    		$didUpdate = false;
    		if(isset($_POST["occupation"])) {
    			db::executeQuery("UPDATE users SET occupation = '".$_POST["occupation"]."' WHERE uid = " . user::uid());
    			$didUpdate = true;
    		}
    		if(isset($_POST["real_name"])) {
    			db::executeQuery("UPDATE users SET real_name = '".$_POST["real_name"]."' WHERE uid = " . user::uid());
    			$didUpdate = true;
    		}
    		if(isset($_POST["gender"])) {
    			db::executeQuery("UPDATE users SET gender = ".$_POST["gender"]." WHERE uid = " . user::uid());
    			$didUpdate = true;
    		}
    		if(isset($_POST["fav_faction"])) {
    			db::executeQuery("UPDATE users SET fav_faction = '".$_POST["fav_faction"]."' WHERE uid = " . user::uid());
    			$didUpdate = true;
    		}
    		if(isset($_POST["interests"])) {
    			db::executeQuery("UPDATE users SET interests = '".$_POST["interests"]."' WHERE uid = " . user::uid());
    			$didUpdate = true;
    		}
    		if(isset($_POST["country"])) {
    			db::executeQuery("UPDATE users SET country = '".$_POST["country"]."' WHERE uid = " . user::uid());
    			$didUpdate = true;
    		}
    		
    		if($didUpdate)
    			echo "<u>profile updated!</u><br />";
    		
    		$query = "SELECT * FROM users WHERE uid = " . user::uid();
    		$result = db::executeQuery($query);
    		$usr = db::nextRowFromQuery($result);
    			
    		echo "<table><tr><td><form action='index.php?p=profile&edit=on' method='post' id='commentform'>";
	    	echo "<p>";
		echo "<label>Change avatar</label><br />";
		echo "<input type='file' name='avatar'><br />";
		misc::avatar_actions();
	    	echo "<label for='message'>Your occupation</label><br />";
	    	echo "<input type='text' name='occupation' value='".$usr["occupation"]."'><br />";
	    	echo "<label for='message'>Your real name</label><br />";
	    	echo "<input type='text' name='real_name' value='".$usr["real_name"]."'><br />";
	    	echo "<label for='message'>Your gender</label><br />";
	    	echo "<select name='gender'>";
	    	if($usr["gender"]==1)
	    		echo "<option value='1' selected='selected'>Male</option>";
	    	else
	    		echo "<option value='1'>Male</option>";
	    	if($usr["gender"]==0)
	    		echo "<option value='0' selected='selected'>Female</option>";
	    	else
	    		echo "<option value='0'>Female</option>";
	    	echo "</select><br />";
	    	
	    	echo "<label for='message'>Your favorite faction</label><br />";
	    	echo "<select name='fav_faction'>";
	    	if($usr["fav_faction"]=="random")
	    		echo "<option value='random' selected='selected'>Random</option>";
	    	else
	    		echo "<option value='random'>Random</option>";
	    	if($usr["fav_faction"]=="soviet")
	    		echo "<option value='soviet' selected='selected'>Soviet</option>";
	    	else
	    		echo "<option value='soviet'>Soviet</option>";
	    	if($usr["fav_faction"]=="allies")
	    		echo "<option value='allies' selected='selected'>Allies</option>";
	    	else
	    		echo "<option value='allies'>Allies</option>";
	    	if($usr["fav_faction"]=="nod")
	    		echo "<option value='nod' selected='selected'>NOD</option>";
	    	else
	    		echo "<option value='nod'>NOD</option>";
	    	if($usr["fav_faction"]=="gda")
	    		echo "<option value='gda' selected='selected'>GDA</option>";
	    	else
	    		echo "<option value='gda'>GDA</option>";
	    	echo "</select><br />";
	    	
	    	echo "<label for='message'>Where do you come from?</label><br />";
	    	echo "<select name='country'>";
	    	echo "<option value='None'>None</option>";
	    	$query = "SELECT * FROM country";
    		$result = db::executeQuery($query);
    		while($country = db::nextRowFromQuery($result))
    		{
    			if($country["name"] == $usr["country"])
    				echo "<option value='".$country["name"]."' selected='selected'>".$country["title"]."</option>";
    			else
    				echo "<option value='".$country["name"]."'>".$country["title"]."</option>";
    		}
    		echo "</select><br />";
	    	
	    	echo "<label for='message'>Your interests</label><br />";
	    	echo "<textarea id='interests' name='interests' rows='10' cols='20' tabindex='4'>".$usr["interests"]."</textarea>";
	    	echo "</p>";
	    	echo "<p class='no-border'>";
	    	echo "<input class='button' type='submit' value='Edit' tabindex='5'/>";      		
	    	echo "</p>";
	    	echo "</form></td></tr></table>";
    	}
    	else
    	{
    		//Display common info
    		echo "<table>";
    		echo "<tr><td><h1>".$usr["login"]."'s profile</h1></td>";
    		$img = "";
    		if($usr["country"] != "None" && $usr["country"] != "")
    			$img = "<img style='float:center;border: 0px solid #261b15; padding: 0px;' src='images/country_flags/".$usr["country"]."'>";
    		if(user::uid() == $usr["uid"] && user::online())
    			echo "<td><a href='index.php?p=profile&edit=on'><h2>edit</h2></a>".$img."</td>";
    		else
    			echo "<td style='padding: .0em 0em;'><center>".$img."</center></td>";
    		echo "</tr>";
    		echo "<tr><td>Gender</td><td>".$gender."</td></tr>";
    		echo "<tr><td>Occupation</td><td>".$usr["occupation"]."</td></tr>";
    		echo "<tr><td>Interests</td><td>".$usr["interests"]."</td></tr>";
    		echo "<tr><td>Real name</td><td>".$usr["real_name"]."</td></tr>";
    		echo "<tr><td>Favorite faction</td><td><a href='index.php?action=display_faction&faction=".$usr["fav_faction"]."'><img style='border: 0px solid #261b15; padding: 0px;' src='images/flag-".$usr["fav_faction"].".png'></a></td></tr>";
    		
    		$query = "SELECT * FROM country WHERE name = '".$usr["country"]."'";
    		$result = db::executeQuery($query);
    		if($country = db::nextRowFromQuery($result))
    			echo "<tr><td>Country</td><td>".$country["title"]."</td></tr>";
    		else
    			echo "<tr><td>Country</td><td>None</td></tr>";
    		
    		$x = $usr["experiance"];
			$level = floor((25 + sqrt(625 + 100 * $x)) / 50);
			$nextLevel = $level+1;
			$expNeeded = 25 * $nextLevel * $nextLevel - 25 * $nextLevel;
    		echo "<tr><td>Level</td><td>".$level."</td></tr>";
    		echo "<tr><td>Experiance left to ".$nextLevel."</td><td>".($expNeeded - $x)."</td></tr>";
    		echo "</table>";
    		
    		
    		//Display latest favorited items
		$show_more = "";
		if (misc::amount_rows(db::executeQuery("SELECT * FROM fav_item WHERE user_id = " . $usr["uid"]), 8))
		    $show_more = "<a href='index.php?action=show_favorited&favorited_id=".$usr["uid"]."'>Show more favorited items</a>";
    		$result = db::executeQuery("SELECT * FROM fav_item WHERE user_id = " . $usr["uid"] . " ORDER BY posted DESC LIMIT 8");
    		if (db::num_rows($result) > 0) {
	    		$data = array();
	    		array_push($data,"",$usr["login"]."'s latest favorited items:");
			while ($row = db::nextRowFromQuery($result)) {
			    $item = db::nextRowFromQuery(db::executeQuery("SELECT * FROM " . $row["table_name"] . " WHERE uid = " . $row["table_id"]));
			    if($item) {
				array_push($data,"<img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/isFav.png'>");
				array_push($data,"favorited the ". substr($row["table_name"],0,strlen($row["table_name"])-1) ." \"<a href='index.php?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".$item["title"]."</a>\" at ".$row["posted"]."");
			    }
			}
			if ($show_more != "")
			    array_push($data, "", $show_more);
    			echo content::create_dynamic_list($data,2,"favorites",10,true,true);
    		}
    		
    		$result = db::executeQuery("
		    SELECT 'Total amount of maps' as item, count(*) AS amount FROM maps WHERE user_id = " . $usr["uid"] . "
		    UNION
		    SELECT 'Total amount of units' as item, count(*) AS amount FROM units WHERE user_id = ". $usr["uid"] . "
		    UNION
		    SELECT 'Total amount of guides' as item, count(*) AS amount FROM guides WHERE user_id = ". $usr["uid"] . "
		    UNION
		    SELECT 'Total favorited items' as item, count(*) AS amount FROM fav_item WHERE user_id = ". $usr["uid"] . "
		    UNION
		    SELECT 'Total amount of comments' as item, count(*) as amount FROM comments WHERE user_id = ". $usr["uid"] . "
		    ");
    		if (db::num_rows($result) > 0) {
		    $data = array();
		    array_push($data,$usr["login"]."'s progress:","");
		    while ($row = db::nextRowFromQuery($result)) {
			if ($row["amount"] == 0)
			{
			    $amount = $row["amount"];
			}
			else
			{
			    $amount = "<a href='index.php'>".$row["amount"]."</a>";
			}
			array_push($data,$row["item"],$amount);
		    }
		    echo content::create_dynamic_list($data,2,"dyn",15,true,false);
		}
    	}
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
	    if ($uploaded == "error")
	    {
		echo lang::$lang['map not uploaded'];
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
    	
	echo "Preview (Will only be updated if JavaScript is enabled):";
	$arr = array("title" => "", "html_content" => "", "posted" => date("F d, Y"), "guide_type" => "", "user_id" => user::uid());
	echo content::displayItem($arr,"guides",true);
	
    	echo "<form id=\"form_class\" enctype=\"multipart/form-data\" method=\"POST\" action=\"\">
		<label>Upload guide:</label>
		<br />
		<label>Title: <input id='id_guide_title' type='text' name='upload_guide_title' onkeyup='updateContent(\"id_display_title\",\"id_guide_title\");' onchange='updateContent(\"id_display_title\",\"id_guide_title\");' onkeypress='updateContent(\"id_display_title\",\"id_guide_title\");' /></label>
		<br />
		<label>Text: <textarea id='id_guide_text' name='upload_guide_text' cols='40' rows='5' onkeyup='updateContent(\"id_display_text\",\"id_guide_text\",\"<p><table><br><i><b><tr><td><img><a>\");' onchange='updateContent(\"id_display_text\",\"id_guide_text\",\"<p><table><br><i><b><tr><td><img><a>\");' onkeypress='updateContent(\"id_display_text\",\"id_guide_text\",\"<p><table><br><i><b><tr><td><img><a>\");'></textarea></label>
		<br />
		<select name='upload_guide_type'>
		<option value='other' selected='selected'>Other</option>
		<option value='design'>Design (2D/3D)</option>
		<option value='mapping'>Mapping</option>
		<option value='modding'>Modding</option>
		<option value='coding'>Coding</option>
		</select>
		<br />
		<input type=\"submit\" name=\"submit\" value=\"".lang::$lang['upload']."\" />
		</form>
	";
    }
    
    public static function upload_unit()
    {
    	if(!user::online())
	    return;
    	
    	echo "<form id='form_class' enctype='multipart/form-data' action='' method=POST>
	    <label>Uploading unit</label><br><br>
	    <label>Unit name: </label>
		<input type='text' name='unit_name'><br>
	    <label>Unit description:<br> </label>
		<textarea name='unit_description' cols='40' rows='5'></textarea><br>
	    <label>Choose unit file:</label><br>
		<input id='my_file_element' size='30' type='file' name='file_0' >
	";
		
	echo "<div id='files_list'></div>
		<script>
		    var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 8 );
		    multi_selector.addElement( document.getElementById( 'my_file_element' ) );
		</script>
	    <input type='submit' value='".lang::$lang['upload']."'>
	    </form>
	";
	$username = user::username();
	$uploaded = upload::upload_unit($username);
	echo $uploaded;
    }
    
}

?>
