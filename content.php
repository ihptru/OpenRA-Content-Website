<?PHP

class content
{
    public static function head()
    {
	header::main();

	echo "<html><head><title>";
	echo lang::$lang['website_name'];
	echo "</title>";

	echo "<script type='text/javascript'>
	function confirmDelete(desc)
	{
	    var agree=confirm('Are you sure you want to '+desc+'?');
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
    public static function createImageGallery($result, $condition="")
    {
	$follow = 0;
	$content = "";
	while ($row = db::nextRowFromQuery($result))
	{
	    $imagePath = "";

	    $table = db::getTableNameFrom($result);
	    switch($table)
	    {
		//Set title, image
		case "maps":
		    $imagePath = misc::minimap($row["path"]);
		    break;
		case "units":
		    $imagePath = $row["preview_image"];
		    break;
		case "guides":
		    $imagePath = "";
		    break;
		case "following":
		    if ($condition == "follow")
		    {
			misc::avatar($row["whom"]);
			$imagePath = misc::avatar($row["whom"]);
		    }
		    elseif ($condition == "followed")
		    {
			misc::avatar($row["who"]);
			$imagePath = misc::avatar($row["who"]);
		    }
		    break;
	    }
	    if ($table == "following")
	    {
		if ($condition == "follow")
		{
		    $show = $row["whom"];
		    $end = "";
		}
		if ($condition == "followed")
		{
		    $show = $row["who"];
		    $end = "ed";
		}
		$follow++;
		if ($follow == 9)
		{
		    
		    $content .= "<br><a href='index.php?action=show_user_follow".$end."&id=".$row["who"]."' style='float:right;margin-right:10px;'>Show all</a>";
		    break;
		}
		$content .= "<a href='index.php?profile=".$show."&p=profile' title='".user::login_by_uid($show)."'><img src='" . $imagePath . "' width='40' height='40' alt='thumbnail' /></a>";
	    }
	    else
	    {
		$content .= "<a href='index.php?p=detail&table=".$table."&id=".$row["uid"]."'><img src='" . $imagePath . "' width='40' height='40' alt='thumbnail' /></a>";
	    }
	}
	return $content;
    }
    
    public static function following($result, $condition)
    {
	
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
		$res = db::executeQuery("SELECT * FROM " . $table_item . " WHERE uid = " . $row["table_id"]);
		$row = db::nextRowFromQuery($res);
		$res = db::executeQuery("SELECT login FROM users WHERE uid = " . $row["user_id"]);
		$username = db::nextRowFromQuery($res);
	    }
	    switch($table_item)
	    {
		case "maps":
		    $title = $row["title"];
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?profile=".$row["user_id"]."&p=profile'>" . $username["login"] . "</a>";
		    $text = $row["description"];
		    $imagePath =  $row["path"] . "minimap.bmp";
		    break;
		case "units":
		    $title = $row["title"];
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?profile=".$row["user_id"]."&p=profile'>" . $username["login"] . "</a>";
		    $text = "";
		    $imagePath = $row["preview_image"];
		    break;
		case "guides":
		    $title = $row["title"];
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?profile=".$row["user_id"]."&p=profile'>" . $username["login"] . "</a>";
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
		    $imagePath = misc::minimap($row["path"]);
		    break;
		case "units":
		    $title = $row["title"];
		    $imagePath = $row["preview_image"];
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
		    $imagePath = misc::minimap($row["path"]);
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?profile=".$row["user_id"]."&p=profile'>" . $username . "</a>";
		    $text = $row["description"];
		    break;
		case "units":
		    $title = $row["title"];
		    $imagePath = $row["preview_image"];
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?profile=".$row["user_id"]."&p=profile'>" . $username . "</a>";
		    $text = "";
		    break;
		case "guides":
		    $title = $row["title"];
		    $imagePath = "images/guide_" . $row["guide_type"] . ".png";
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?profile=".$row["user_id"]."&p=profile'>" . $username . "</a>";
		    $text = "";
		    break;
		case "articles":
		    $title = $row["title"];
		    $imagePath = "";
		    $subtitle = "posted at " . $row["posted"] . " by <a href='index.php?profile=".$row["user_id"]."&p=profile'>" . $username . "</a>";
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
	    if($key != "current_list_page_".$table)
		$gets .= "&" . $key . "=" . $_GET[$key];
	for($i = 1; $i < $nrOfPages+1; $i++)
	    if($current == $i)
		$pages .= "<td>" . $i . "</td>";
	    else
		$pages .= "<td id='page_count'><a href='index.php?current_list_page_".$table."=".$i.$gets."'>" . $i . "</a></td>";
	$pages .= "</tr></table>";
	
	if ($nrOfPages == 1)
	    $pages = "";
	
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
		{ } else { break; }
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
	    $edit = "";
	    if ($row["user_id"] == user::uid())
	    {
		if(isset($row["uid"]))
		{
		    $delete = "Delete ".rtrim($table,"s");
		    $delete = "<a href='index.php?del_item=".$row["uid"]."&del_item_table=".$table."&del_item_user=".$row["user_id"]."' onClick='return confirmDelete(\"delete this ".rtrim($table,"s")."\")'>".$delete."</a>";
		    $edit = " | <a href='index.php?p=edit_item&table=".$table."&id=".$row["uid"]."'>Edit</a>";
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
			$reported = "<a href='index.php?p=detail&table=".$table."&id=".$row["uid"]."&report' onClick='return confirmDelete(\"report this item\")'>Report Item</a>";
		}
	    }
	    $favIcon = "";
	    if(isset($row["uid"]) && user::online())
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
		    $imagePath = misc::minimap($row["path"]);
		    $subtitle = "posted at " . $row["posted"] . " by " . "<a href='index.php?profile=".$row["user_id"]."&p=profile'>". $user_name . "</a>";
		    $text = $row["description"];
		    break;
		case "units":
		    $title = $row["title"];
		    $imagePath = $row["preview_image"];
		    $subtitle = "posted at " . $row["posted"] . " by " . "<a href='index.php?profile=".$row["user_id"]."&p=profile'>". $user_name . "</a>";
		    $text = "";
		    break;
		case "guides":
		    $imagePath = "images/guide_" . $row["guide_type"] . ".png";
		    $allow = '<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>';
		    $text = strip_tags($row["html_content"], $allow);
		    
		    $content .= "<div class='post'>";
		    $content .= "<h2 id='id_display_title'>" . strip_tags($row["title"]) . "</h2>";
		    $content .= "<p class='post-info'>Posted by <a href='index.php?profile=".$row["user_id"]."&p=profile' id='id_display_username'>". $user_name . "</a></p>";
		    $content .= "<p><div id='id_display_text'>" . $text . "</div></p>";
		    $content .= "<p class='postmeta'>";
		    if($reported != "")
			$content .= $reported . " | ";
		    if($delete != "")
			$content .= $delete . " | ";
		    if($favIcon != "")
			$content .= "<a href='index.php?p=detail&table=".$table."&id=".$row["uid"]."&fav'><img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/".$favIcon."'></a> | ";
		    $content .= "<span class='date'>".$row["posted"]."</span>";
		    $content .= $edit;
		    $content .= "</p>";
		    $content .= "</div>";
		    return $content;
		    break;
		case "articles":
		    $imagePath = $row["image"];
		    $allow = '<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>';
		    $text = strip_tags($row["content"], $allow);
		    
		    $content .= "<div class='post'>";
		    $content .= "<h2 id='id_display_title'>" . strip_tags($row["title"]) . "</h2>";
		    $content .= "<p class='post-info'>Posted by <a href='index.php?profile=".$row["user_id"]."&p=profile'>". $user_name . "</a></p>";
		    $content .= "<p><div id='id_display_text'>" . $text . "</div></p>";
		    $content .= "<p class='postmeta'>";
		    if($reported != "")
			$content .= $reported . " | ";
		    if($delete != "")
			$content .= $delete . " | ";
		    if($favIcon != "")
			$content .= "<a href='index.php?p=detail&table=".$table."&id=".$row["uid"]."&fav'><img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/".$favIcon."'></a> | ";
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
		$mapfile = explode("-", basename($row["path"]), 2);
		$mapfile = $mapfile[1] . ".oramap";
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
	    elseif ($reported != "")
		$content .= "<tr><td>".$reported."</td></tr>";
	    
	     
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
	$table = db::getTableNameFrom($result);
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

	    $avatarImg = misc::avatar($author["uid"]);
		
	    $content .= "<div class='comment-info'>";			
	    $content .= "<a href='index.php?profile=".$comment["user_id"]."&p=profile'><img alt='' src='" . $avatarImg . "' style='margin-top:10px; max-width:50' /></a>";
	    $content .= "<cite>";
	    $content .= "<a href='index.php?profile=".$comment["user_id"]."&p=profile'>" . $author["login"] . "</a> Says: <br />";
	    $content .= "<span class='comment-data'><a href='#comment-63' title=''>" . $comment["posted"] . "</a></span>";
	    $content .= "</cite>";
	    $content .= "</div>";
                
	    $content .= "<div class='comment-text'>";
	    $content .= "<p>" . strip_tags($comment["content"]) . "</p>";
	    if (misc::comment_owner($comment["user_id"]))
	    {
		$content .= "<a style='float: right; margin: -129px -35px 0 0; border: 0px solid #2C1F18;color:#ff0000;' href='index.php?delete_comment=".$comment["uid"]."&user_comment=".user::uid()."&table_name=".$comment["table_name"]."&table_id=".$comment["table_id"]."' onClick='return confirmDelete(\"delete comment\")'><img src='images/delete.png' style='border: 0px solid #261b15; padding: 0px; max-width:50%;' border='0' alt='delete' /></a>";
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
		$modifiedName = str_replace(" ","_",$name);
		if(isset($_GET["current_dynamic_page_".$modifiedName]))
		    $current = $_GET["current_dynamic_page_".$modifiedName];
		else
		    $current = 1;
		$start = ($current-1) * $maxItemsPerPage * $columns;
		$maxItemsPerPageOrg = $maxItemsPerPage; //original value
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
		$nrOfPages = floor(($total-0.01) / $maxItemsPerPage) + 1;
		if($nrOfPages > 1 && $use_pages == false)
		{
		    $params = "\"data\":\"".pages::serialize_array($data)."\"";
		    $params .= ",\"columns\":\"".$columns."\"";
		    $params .= ",\"name\":\"".$modifiedName."\"";
		    $params .= ",\"maxItemsPerPage\":\"".$maxItemsPerPageOrg."\"";
		    $params .= ",\"header\":\"".$header."\"";
		    $params .= ",\"use_pages\":\"1\"";
		    $content .= "<tr><td colspan='".$columns."'><a href='javascript:post_to_url(\"index.php?p=dynamic\",{".$params."});'>Show more ".$name."</a></td></tr>";
		    
		}
		$content .= "</table>";
		$gets = "";
		$pages = "<table>";
		$keys = array_keys($_GET);
		foreach($keys as $key)
		{
		    if($key != "current_dynamic_page_".$modifiedName)
			$gets .= "&" . $key . "=" . $_GET[$key];
		}
		for($i = 1; $i < $nrOfPages+1; $i++)
		{
		    if($current == $i)
			$pages .= "<td>" . $i . "</td>";
		    else
			if($_GET["p"] == "dynamic")
			{
			    $params = "\"data\":\"".pages::serialize_array($data)."\"";
			    $params .= ",\"columns\":\"".$columns."\"";
			    $params .= ",\"name\":\"".$modifiedName."\"";
			    $params .= ",\"maxItemsPerPage\":\"".$maxItemsPerPageOrg."\"";
			    $params .= ",\"header\":\"".$header."\"";
			    $params .= ",\"use_pages\":\"1\"";
			    $pages .= "<td id='page_count'><a href='javascript:post_to_url(\"index.php?current_dynamic_page_".$modifiedName."=".$i.$gets."\",{".$params."});'>" . $i . "</a></td>";
			}
			else
			    $pages .= "<td id='page_count'><a href='index.php?current_dynamic_page_".$modifiedName."=".$i.$gets."'>" . $i . "</a></td>";
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
	$content .= "<div class='lang'>
		    <a id='".pages::cur_lang("en")."' href='index.php?lang=en'>English</a>
		    <a id='".pages::cur_lang("ru")."' href='index.php?lang=ru'>Русский</a>
		    <a id='".pages::cur_lang("de")."' href='index.php?lang=de'>Deutsch</a>
		    <a id='".pages::cur_lang("sv")."' href='index.php?lang=sv'>Swedish</a>
		    </div>
	";

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
	elseif ($page == "edit_item")
	{
	    objects::edit();
	}
	elseif ($page == "dynamic")
	{
	    objects::dynamic();
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
		    $avatar = misc::avatar($row["uid"]);
		    array_push($data,"<a href='index.php?profile=".$row["uid"]."&p=profile'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='index.php?profile=".$row["uid"]."&p=profile'>".$row["login"]."</a>");
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
	    list($order_by, $request_mod, $request_tileset) = content::map_filters();
	    $result = db::executeQuery("SELECT * FROM maps WHERE user_id = ".user::uid()." AND g_mod LIKE ('%".$request_mod."%') AND tileset LIKE ('%".$request_tileset."%') GROUP BY maphash ORDER BY ".$order_by);
	    $output = content::create_grid($result);
	    if ($output == "")
	    {
		echo "<table><tr><th>No maps uploaded yet</th></tr></table>";
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
		echo "<table><tr><th>No guides uploaded yet</th></tr></table>";
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
		echo "<table><tr><th>No units uploaded yet</th></tr></table>";
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
		    array_push($data,"","This people like <u>".$faction."</u> faction:");
		    while ($row = db::nextRowFromQuery($result))
		    {
			$avatar = misc::avatar($row["uid"]);
			array_push($data,"<a href='index.php?profile=".$row["uid"]."&p=profile'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='index.php?profile=".$row["uid"]."&p=profile'>".$row["login"]."</a>");
		    }
		    echo content::create_dynamic_list($data,2,"dyn",10,true,true);
    		}
		else
		{
		    $data = array();
		    array_push($data,"No one likes <u>".$faction."</u> faction");
		    echo content::create_dynamic_list($data,1,"dyn",1,true,true);
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
		    array_push($data,"","<a href='index.php?profile=".$_GET["favorited_id"]."&p=profile'>".$usr["login"]."</a>'s latest favorited items:");
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
	if ($request == "users_items")
	{
	    if (isset($_GET["table"]) and isset($_GET["id"]))
	    {
		$table = $_GET["table"];
		$id = $_GET["id"];
		$query = "SELECT * FROM ".$table." WHERE user_id = ".$id;
		$result = db::executeQuery($query);
		echo content::create_list($result, $table);
	    }
	}
	if ($request == "show_user_follow")
	{
	    if (isset($_GET["id"]))
	    {
		$id = $_GET["id"];
		if ($id == user::uid())
		{
		    $name = "You";
		    $who = $name." follow:";
		}
		else
		{
		    $name = user::login_by_uid($id);
		    $who = "<a href='index.php?profile=".$id."&p=profile'>".$name."</a> follows:";
		}
		$query = "SELECT * FROM following WHERE who = ".$id;
		$result = db::executeQuery($query);
		if (db::num_rows($result) > 0)
		{
		    $data = array();
		    array_push($data, "", $who);
		    while ($row = db::nextRowFromQuery($result))
		    {
			$avatar = misc::avatar($row["whom"]);
			array_push($data,"<a href='index.php?profile=".$row["whom"]."&p=profile'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='index.php?profile=".$row["whom"]."&p=profile'>".user::login_by_uid($row["whom"])."</a>");
		    }
		    echo content::create_dynamic_list($data,2,"dyn",10,true,true);
		}
		else
		{
		    $verb = "does";
		    if ($name == "You")
			$verb = "do";
		    echo "<table>
			      <tr>
				  <th>".$name." ".$verb." not follow anyone</th>
			      </tr>
			  </table>
		    ";
		}
	    }
	}
	if ($request == "show_user_followed")
	{
	    if (isset($_GET["id"]))
	    {
		$id = $_GET["id"];
		if ($id == user::uid())
		{
		    $name = "You";
		    $who = $name." are followed by:";
		}
		else
		{
		    $name = user::login_by_uid($id);
		    $who = "<a href='index.php?profile=".$id."&p=profile'>".$name."</a> is followed by:";
		}
		$query = "SELECT * FROM following WHERE whom = ".$id;
		$result = db::executeQuery($query);
		if (db::num_rows($result) > 0)
		{
		    $data = array();
		    array_push($data, "", $who);
		    while ($row = db::nextRowFromQuery($result))
		    {
			$avatar = misc::avatar($row["who"]);
			array_push($data,"<a href='index.php?profile=".$row["who"]."&p=profile'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='index.php?profile=".$row["who"]."&p=profile'>".user::login_by_uid($row["who"])."</a>");
		    }
		    echo content::create_dynamic_list($data,2,"dyn",10,true,true);
		}
		else
		{
		    $verb = "is";
		    if ($name == "You")
			$verb = "are";
		    echo "<table>
			      <tr>
				  <th>".$name." ".$verb." not followed by anyone</th>
			      </tr>
			  </table>
		    ";
		}
	    }
	}
    }
    
    public static function map_filters()
    {
	$sort_by = "latest";
	$mod = "";
	$tileset = "";
	if (isset($_POST["apply_filter"]))
	{
	    $sort_by = $_POST["sort"];
	    $mod = $_POST["mod"];
	    $tileset = $_POST["tileset"];
	}
	elseif (isset($_COOKIE["map_sort_by"]))
	{
	    $sort_by = $_COOKIE["map_sort_by"];
	    $mod = $_COOKIE["map_mod"];
	    $tileset = $_COOKIE["map_tileset"];
	}

	//filters
	echo "<form onLoad='mod_tileset();' name='map_filters' method=POST action=''><table style='width:560px;'><tr><th>sort by:</th><th>mod:</th><th>tileset:</th></tr><tr>";
	echo "<td>";
	echo "<select name='sort' id='sort'>";
	echo "<option value='latest' ".misc::option_selected("latest",$sort_by).">latest first</option>";
	echo "<option value='date' ".misc::option_selected("date",$sort_by).">date</option>";
	echo "<option value='alpha' ".misc::option_selected("alpha",$sort_by).">title</option>";
	echo "<option value='alpha_reverse' ".misc::option_selected("alpha_reverse",$sort_by).">title in reverse order</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "<td>";
	echo "<select onChange='mod_tileset();' name='mod' id='mod'>";
	echo "<option value='any_mod' ".misc::option_selected("any_mod",$mod).">Any</option>";
	echo "<option value='ra' ".misc::option_selected("ra",$mod).">RA</option>";
	echo "<option value='cnc' ".misc::option_selected("cnc",$mod).">CNC</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "<td>";
	echo "<select name='tileset' id='tileset'>";
	echo "<option value='any_tileset' ".misc::option_selected("any_tileset",$tileset).">Any</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "</tr></table><div style='width:578px;'><input style='float:right;' type='submit' name='apply_filter' value='Apply filters'></div></form>";
	
	//next JS script is for generating Tileset list on fly depending on selected mod
	echo "<script type='text/javascript'>
	    function mod_tileset()
	    {
		var chosen_option=document.getElementById('mod').options[document.getElementById('mod').selectedIndex]
		if (chosen_option.value == 'any_mod')
		{
		    document.map_filters.tileset.options.length=0
		    document.map_filters.tileset.options[0] = new Option('Any','any_tileset')
		}
		if (chosen_option.value == 'ra')
		{
		    document.map_filters.tileset.options.length=0
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('Any','any_tileset',false,".misc::option_selected_bool("any_tileset",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('temperat','temperat',false,".misc::option_selected_bool("temperat",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('snow','snow',false,".misc::option_selected_bool("snow",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('interior','interior',false,".misc::option_selected_bool("interior",$tileset).")
		}
		if (chosen_option.value == 'cnc')
		{
		    document.map_filters.tileset.options.length=0
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('Any','any_tileset',false,".misc::option_selected_bool("any_tileset",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('temperat','temperat',false,".misc::option_selected_bool("temperat",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('desert','desert',false,".misc::option_selected_bool("desert",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('winter','winter',false,".misc::option_selected_bool("winter",$tileset).")
		}
	    }
	    mod_tileset()

	</script>";
	// order by
	if ($sort_by == "latest")
	{
	    $order_by = "posted DESC";
	}
	elseif ($sort_by == "date")
	{
	    $order_by = "posted";
	}
	elseif ($sort_by == "alpha")
	{
	    $order_by = "title";
	}
	elseif ($sort_by == "alpha_reverse")
	{
	    $order_by = "title DESC";
	}
	//mod
	if ($mod == "any_mod")
	{
	    $request_mod = "";
	}
	else
	{
	    $request_mod = $mod;
	}
	//tileset
	if ($tileset == "any_tileset")
	{
	    $request_tileset = "";
	}
	else
	{
	    $request_tileset = $tileset;
	}
	
	return array($order_by, $request_mod, $request_tileset);
    }
}

class objects
{
    public static function maps()
    {
	echo "<h3>".lang::$lang['maps']."!</h3>";
	list($order_by, $request_mod, $request_tileset) = content::map_filters();
	$result = db::executeQuery("SELECT * FROM maps WHERE g_mod LIKE ('%".$request_mod."%') AND tileset LIKE ('%".$request_tileset."%') GROUP BY maphash ORDER BY ".$order_by);
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
    
    public static function dynamic()
    {
	$arr = array();
	array_push($arr,"data");
	array_push($arr,"columns");
	array_push($arr,"name");
	array_push($arr,"maxItemsPerPage");
	array_push($arr,"header");
	array_push($arr,"use_pages");
	if( pages::allISSet($arr) )
	{
	    $data = pages::deserialize_array($_POST["data"]);
	    echo content::create_dynamic_list($data, $_POST["columns"], $_POST["name"], $_POST["maxItemsPerPage"], $_POST["header"], $_POST["use_pages"]);
	}
	else
	{
	    echo "Missing data";
	}
    }
    
    public static function edit()
    {
	$table = "";
	$id = "";
	if(isset($_GET["table"]))
	    $table = $_GET["table"];
	if(isset($_GET["id"]))
	    $id = $_GET["id"];
	if($table != "" && $id != "")
	{
	    if($table == "guides")
	    {
		$result = db::executeQuery("SELECT * FROM " . $_GET['table'] . " WHERE uid = " . $_GET['id'] . "");
		$row = db::nextRowFromQuery($result);
		if($row["user_id"] == user::uid())
		{
		    echo "Preview (Will only be updated if JavaScript is enabled):";
		    date_default_timezone_set('Europe/Dublin');
		    $arr = array("title" => $row["title"], "html_content" => $row["html_content"], "posted" => date("F d, Y"), "guide_type" => "", "user_id" => user::uid());
		    echo content::displayItem($arr,"guides",true);
		    
		    echo "<form id=\"form_class\" enctype=\"multipart/form-data\" method=\"POST\" action=\"\">
			    <label>Upload guide:</label>
			    <br />
			    <label>Title: <input id='id_guide_title' type='text' value='".$row["title"]."' name='edit_guide_title' onkeyup='updateContent(\"id_display_title\",\"id_guide_title\");' onchange='updateContent(\"id_display_title\",\"id_guide_title\");' onkeypress='updateContent(\"id_display_title\",\"id_guide_title\");' /></label>
			    <br />
			    <label>Text: <textarea id='id_guide_text' name='edit_guide_text' cols='40' rows='5' onkeyup='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");' onchange='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");' onkeypress='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");'>".$row["html_content"]."</textarea></label>
			    <br />
			    <select name='edit_guide_type'>";
		    echo "<option value='other' ".misc::option_selected("other", $row["guide_type"]).">Other</option>";
		    echo "<option value='design' ".misc::option_selected("design", $row["guide_type"]).">Design (2D/3D)</option>";
		    echo "<option value='mapping' ".misc::option_selected("mapping", $row["guide_type"]).">Mapping</option>";
		    echo "<option value='modding' ".misc::option_selected("modding", $row["guide_type"]).">Modding</option>";
		    echo "<option value='coding' ".misc::option_selected("coding", $row["guide_type"]).">Coding</option>";

		    echo "</select>
			    <br />
			    <input type=\"hidden\" name=\"edit_guide_uid\" value=\"".$row["uid"]."\" />
			    <input type=\"submit\" name=\"submit\" value=\"".lang::$lang['edit']."\" />
			    </form>
		    ";
		}
	    }
	}
    }
    
    public static function detail()
    {
	$result = db::executeQuery("SELECT * FROM " . $_GET['table'] . " WHERE uid = " . $_GET['id'] . "");
	echo content::displayItem($result, $_GET['table']);
    
	$result = db::executeQuery("SELECT * FROM comments WHERE table_name = '" . $_GET['table'] . "' AND table_id = '" . $_GET['id'] . "' ORDER by posted");
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
			      <th>Empty request</th>
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
		    	array_push($data,"<a href='index.php?profile=".$row["uid"]."&p=profile'>".$row["login"]."</a>");
		    $content .= content::create_dynamic_list($data,1);
	    }
	    if ($content == "")
	    {
		echo "<table>
			  <tr>
			      <th>Nothing found</th>
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
    public static function ifFollow($profile)
    {
	//-1	do not show
	//0	follow
	//1	unfollow
	if ($profile != user::uid())
	{
	    $query = "SELECT * FROM following WHERE who = ".user::uid()." AND whom = ".$profile;
	    $result = db::executeQuery($query);
	    while (db::nextRowFromQuery($result))
	    {
		if (user::online())
		{
		    return 1;
		}
		return -1;
	    }
	    if (user::online())
	    {
		return 0;
	    }
	    return -1;
	}
	return -1;
    }

    public static function sidebar_data($profile,$id)
    {
	$data = array();
	if ( $profile == "You" )
	{
	    $to_head = "You avatar:";
	}
	else
	{
	    $to_head = $profile."'s avatar:";
	}
	array_push($data,$to_head);
	array_push($data,"<img src='".misc::avatar($id)."'>");
	if (profile::ifFollow($id)==0)
	{
	    array_push($data,"<a href='index.php?follow=".$id."'>Follow user</a>");
	}
	elseif (profile::ifFollow($id)==1)
	{
	    array_push($data,"<a href='index.php?unfollow=".$id."'>Unfollow user</a>");
	}
	echo content::create_dynamic_list($data,1,"dyn",3,true,true);
	$query = "SELECT * FROM following WHERE who = ".$id;
	$result = db::executeQuery($query);
	if (db::num_rows($result) > 0)
	{
	    if ( $profile == "You" )
	    {
		$to_head = "You follow ".db::num_rows($result)." people:";
	    }
	    else
	    {
		$to_head = $profile." follows ".db::num_rows($result)." people:";
	    }
	    echo "<table style='margin-left:9px;width:274px;'>
		      <tr>
			  <th>".$to_head."</th>
		      </tr>
		  </table>";
	    echo "<p style='margin-left:5px;' class='thumbs'>";
	    echo content::createImageGallery($result,"follow");
	    echo "</p>";
	}
	
	// followed by is not shown for non logged in users
	if (user::online())
	{
	    $query = "SELECT * FROM following WHERE whom = ".$id;
	    $result = db::executeQuery($query);
	    if (db::num_rows($result) > 0)
	    {
		if ( $profile == "You" )
		{
		    $to_head = "You are followed by ".db::num_rows($result)." people:";
		}
		else
		{
		    $to_head = $profile." is followed by ".db::num_rows($result)." people:";
		}
		echo "<table style='margin-left:9px;width:274px;'>
		      <tr>
			  <th>".$to_head."</th>
		      </tr>
		  </table>";
		echo "<p style='margin-left:5px;' class='thumbs'>";
		echo content::createImageGallery($result,"followed");
		echo "</p>";
	    }
	}
    }

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

	    $avatar = upload::avatar();
	    if ($avatar == "type error")
	    {
		echo "Image type is not supported!<br>";
	    }
	    elseif ($avatar == "done")
	    {
		echo "Avatar is uploaded<br>";
		$didUpdate = true;
	    }

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

	    echo "<table><tr><td><form action='index.php?p=profile&edit=on' method='post' enctype=\"multipart/form-data\" id='commentform'>";
	    echo "<p>";
	    echo "<label>Change avatar</label><br />";
	    echo "<input type='file' name='avatar_upload'><br />";
	    echo "<label for='message'>Your occupation</label><br />";
	    echo "<input type='text' name='occupation' value='".$usr["occupation"]."'><br />";
	    echo "<label for='message'>Your real name</label><br />";
	    echo "<input type='text' name='real_name' value='".$usr["real_name"]."'><br />";
	    echo "<label for='message'>Your gender</label><br />";
	    echo "<select name='gender'>";
	    echo "<option value='1' ".misc::option_selected(1,$usr["gender"]).">Male</option>";
	    echo "<option value='0' ".misc::option_selected(0,$usr["gender"]).">Female</option>";
	    echo "</select><br />";
	    
	    echo "<label for='message'>Your favorite faction</label><br />";
	    echo "<select name='fav_faction'>";
	    echo "<option value='random' ".misc::option_selected("random",$usr["fav_faction"]).">Random</option>";
	    echo "<option value='soviet' ".misc::option_selected("soviet",$usr["fav_faction"]).">Soviet</option>";
	    echo "<option value='allies' ".misc::option_selected("allies",$usr["fav_faction"]).">Allies</option>";
	    echo "<option value='nod' ".misc::option_selected("nod",$usr["fav_faction"]).">NOD</option>";
	    echo "<option value='gda' ".misc::option_selected("gda",$usr["fav_faction"]).">GDA</option>";

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
	    echo "<input class='button' type='submit' name'submit' value='Edit' tabindex='5'/>";      		
	    echo "</p>";
	    echo "</form></td></tr></table>";
    	}
    	else
    	{
	    if ($usr["uid"] == user::uid())
	    {
		$whos = "Your";
	    }
	    else
	    {
		$whos = $usr["login"]."'s";
	    }
	    //Display common info
	    echo "<table>";
	    echo "<tr><td><h1>".$whos." profile</h1></td>";
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
	    $result = db::executeQuery("SELECT * FROM fav_item WHERE user_id = " . $usr["uid"] . " ORDER BY posted DESC");
	    $fav_data = array();
	    if (db::num_rows($result) > 0) {
		array_push($fav_data,"",$whos." latest favorited items:");
		while ($row = db::nextRowFromQuery($result)) {
		    $item = db::nextRowFromQuery(db::executeQuery("SELECT * FROM " . $row["table_name"] . " WHERE uid = " . $row["table_id"]));
		    if($item) {
			array_push($fav_data,"<img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/isFav.png'>");
			array_push($fav_data,"favorited the ". substr($row["table_name"],0,strlen($row["table_name"])-1) ." \"<a href='index.php?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".$item["title"]."</a>\" at ".$row["posted"]."");
		    }
		}
		if ($show_more != "")
		    array_push($fav_data, "", $show_more);
		echo content::create_dynamic_list($fav_data,2,"favorite items",10,true,false);
	    }

	    $result = db::executeQuery("
		SELECT 'Total amount of maps' as item, count(*) AS amount, 'maps' AS table_name FROM maps WHERE user_id = " . $usr["uid"] . "
		UNION
		SELECT 'Total amount of units' as item, count(*) AS amount, 'units' AS table_name FROM units WHERE user_id = ". $usr["uid"] . "
		UNION
		SELECT 'Total amount of guides' as item, count(*) AS amount, 'guides' AS table_name FROM guides WHERE user_id = ". $usr["uid"] . "
		UNION
		SELECT 'Total favorited items' as item, count(*) AS amount, 'fav_item' AS table_name FROM fav_item WHERE user_id = ". $usr["uid"] . "
		UNION
		SELECT 'Total amount of comments' as item, count(*) AS amount, 'comments' AS table_name FROM comments WHERE user_id = ". $usr["uid"] . "
	    ");
	    if (db::num_rows($result) > 0) {
		$data = array();
		array_push($data,$whos." progress:","");
		while ($row = db::nextRowFromQuery($result)) {
		    if ($row["amount"] == 0)
		    {
			$amount = $row["amount"];
		    }
		    else
		    {
			if($row["table_name"] == "fav_item")
			{
			    $params = "\"data\":\"".pages::serialize_array($fav_data)."\"";
			    $params .= ",\"columns\":\"2\"";
			    $params .= ",\"name\":\"favorite items\"";
			    $params .= ",\"maxItemsPerPage\":\"10\"";
			    $params .= ",\"header\":\"1\"";
			    $params .= ",\"use_pages\":\"1\"";
			    $amount = "<a href='javascript:post_to_url(\"index.php?p=dynamic\",{".$params."});'>".$row["amount"]."</a>";
			}
			else if($row["table_name"] == "comments")
			{
			    $comment_result = db::executeQuery("SELECT * FROM comments WHERE user_id = ".$usr["uid"]);
			    $comment_data = array();
			    array_push($comment_data,$usr["login"]."'s comments:");
			    while($comment = db::nextRowFromQuery($comment_result))
			    {
				array_push($comment_data,$comment["content"]);
			    }
			    $params = "\"data\":\"".pages::serialize_array($comment_data)."\"";
			    $params .= ",\"columns\":\"1\"";
			    $params .= ",\"name\":\"comment items\"";
			    $params .= ",\"maxItemsPerPage\":\"10\"";
			    $params .= ",\"header\":\"1\"";
			    $params .= ",\"use_pages\":\"1\"";
			    $amount = "<a href='javascript:post_to_url(\"index.php?p=dynamic\",{".$params."});'>".$row["amount"]."</a>";
			}
			else
			    $amount = "<a href='index.php?action=users_items&table=".$row["table_name"]."&id=".$self."'>".$row["amount"]."</a>";
		    }
		    array_push($data,$row["item"],$amount);
		}
		echo content::create_dynamic_list($data,2,"dyn",15,true,false);
	    }
    	}
    }
    
    public static function profile_bar()
    {
	$query = "SELECT uid,avatar,login FROM users WHERE uid = " . user::uid();
	$result = db::executeQuery($query);
	$row = db::nextRowFromQuery($result);
	$avatar = misc::avatar($row["uid"]);
	echo "<img src='".$avatar."' style='max-width:120px'>";
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
	    if ($uploaded == "0")
	    {
		echo "<table><tr><th>Map is successfully uploaded</th></tr></table>";
		$query = "SELECT uid,path FROM maps WHERE user_id = ".user::uid()."
			    ORDER BY posted DESC LIMIT 1
		";
		$row = db::nextRowFromQuery(db::executeQuery($query));
		$imagePath = misc::minimap($row["path"]);
		echo "<p><a href='index.php?p=detail&table=maps&id=".$row["uid"]."'><img src='".$imagePath."'></a></p>";
		return;
	    }
	    echo "<table><tr><th>".$uploaded."</th></tr></table>";
	}
    }
    
    public static function upload_guide()
    {
    	if(!user::online())
	    return;
    	
	echo "Preview (Will only be updated if JavaScript is enabled):";
	date_default_timezone_set('Europe/Dublin');
	$arr = array("title" => "", "html_content" => "", "posted" => date("F d, Y"), "guide_type" => "", "user_id" => user::uid());
	echo content::displayItem($arr,"guides",true);
	
    	echo "<form id=\"form_class\" enctype=\"multipart/form-data\" method=\"POST\" action=\"\">
		<label>Upload guide:</label>
		<br />
		<label>Title: <input id='id_guide_title' type='text' name='upload_guide_title' onkeyup='updateContent(\"id_display_title\",\"id_guide_title\");' onchange='updateContent(\"id_display_title\",\"id_guide_title\");' onkeypress='updateContent(\"id_display_title\",\"id_guide_title\");' /></label>
		<br />
		<label>Text: <textarea id='id_guide_text' name='upload_guide_text' cols='40' rows='5' onkeyup='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");' onchange='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");' onkeypress='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");'></textarea></label>
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
	    <label>Unit type:<br></label>
		<select name='unit_type'>
		<option value='structure'>Structure</option>
		<option value='infantry'>Infantry</option>
		<option value='vehicle'>Vehicle</option>
		<option value='air-borne'>Air-borne</option>
		<option value='nature'>Nature</option>
		<option value='other'>Other</option>
		</select><br>
	    <label>Frame (What frame of the unit you want to be displayed as a preview image):</label>
		<input type='text' name='unit_frame'><br>
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
