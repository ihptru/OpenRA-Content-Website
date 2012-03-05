<?PHP

class content
{
    public static function head()
    {
	header::main();

	echo "<html><head><title>";
	echo header::pageTitle();
	echo "</title>";

	echo "<script type='text/javascript'>
	function confirmDelete(desc)
	{
	    var agree=confirm('".misc::lang("js confirm")." '+desc+'?');
	    if (agree)
	    return true ;
	    else
	    return false ;
	}
	</script>
	<script src='libs/multifile.js'>
	    //include multi upload form
	</script>
	<script src='libs/functions.js'>
	    //include other javascript functions
	</script>
	";
	echo "<script type='text/javascript' src='libs/password/jquery.js'></script>
		  <script type='text/javascript' src='libs/password/mocha.js'></script>";
	echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"css/screen.css\" />
	</head>";
    }
		
    public static function body_head()
    {
	echo "
	    <div id='header'>
		<a name='top'></a>
		<h1 id='logo-text'><a href='/' title=''>".misc::lang("website_name")."</a></h1>		
		<p id='slogan'>".misc::lang("website_slowgun")."</p>
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
		<div id='register_link'>
		    <a href='?register'>".misc::lang("register")."</a>
		</div>
		<div id='recover_link'>
		    <a href=\"?recover\">".misc::lang("recover")."</a>
		</div>
	    ";
	}
	echo "<form id='quick-search' action='index.php' method='GET'>
		<p>
		<input class='tbox' id='qsearch' type='text' name='qsearch' onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'".misc::lang("search")."...':this.value;\" value='".misc::lang("search")."...' />
		<input class='btn' alt='".misc::lang("search")."' type='image' name='searchsubmit' title='".misc::lang("search")."' src='images/search.png' />
		<input type='hidden' name='p' value='search'>
		</p>
		</form>	
	    <!-- / id='header' -->
	    </div>
	";
    }

    public static function login_form()
    {
	echo "<form method='POST' action=''>
	    ".misc::lang("login").": <input type='text' name='login'>
	    ".misc::lang("password").": <input type='password' name='pass'>
	    <input style='position:absolute; right: -25px; top: 15px;' type='checkbox' name='remember' value='yes' checked title='".misc::lang("remember me")."'>
	    <input type='submit' value='".misc::lang("sign in")."'>
	    <br>
	    </form>
	";
    }

    public static function createMenu()
    {
	if (isset($_GET['p']))
	    $request = $_GET['p'];
	else
	    $request = "";
	if (isset($_GET['profile']))
	    $request = "profile";
	if (isset($_GET['table']))
	    $request = $_GET['table'];
	echo "<li id='"; echo pages::current('', $request); echo"'><a href='/'>".misc::lang("home")."</a></li>";
	echo "<li id='"; echo pages::current('maps', $request); echo"'><a href='?p=maps'>".misc::lang("maps")."</a></li>";
	echo "<li id='"; echo pages::current('units', $request); echo"'><a href='?p=units'>".misc::lang("units")."</a></li>";
	echo "<li id='"; echo pages::current('guides', $request); echo"'><a href='?p=guides'>".misc::lang("guides")."</a></li>";
	echo "<li id='"; echo pages::current('about', $request); echo"'><a href='?p=about'>".misc::lang("about")."</a></li>";
            
	if (user::online())
	{
	    echo "<li style='float:right;' id=''><a href='?logout'>".misc::lang("logout")."</a></li>";
	    echo "<li style='float:right;' id='"; echo pages::current('profile', $request); echo"'><a href='?profile=".user::uid()."'>".misc::lang("profile")."</a></li>";
	}
    }

    public static function create_register_form()
    {
	echo "<form id='register_form' method='POST' action=''>
	    <table style='text-align:right;'><tr><td collspan='2'><b>
	    ".misc::lang("registration")."
	    </b></td></tr><tr><td>
	    ".misc::lang("login")."</td><td><input type='text' name='rlogin'></td></tr><tr><td>
	    ".misc::lang("password")."</td><td><input type='password' id='inputPassword' name='rpass'>
	    <div id='complexity' class='default'>".misc::lang("password security")."</div></td></tr><tr><td>
	    ".misc::lang("reenter pw")."</td><td><input type='password' name='verpass'></td></tr><tr><td>
	    ".misc::lang("e-mail")."</td><td><input type='text' name='email'></td></tr><tr><td>
	    <input type='hidden' name='act'>
	    <td>
	";
	require_once('libs/recaptchalib.php');
	$publickey = "6Ldq-soSAAAAADuu6iGZoCiTSOzBcoKXBwlhjM5u";
	echo recaptcha_get_html($publickey);
	
	echo "</td></tr><tr><td><input type='submit' value='".misc::lang("confirm")."'
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

	    $table = $row["table_name"];
	    switch($table)
	    {
		//Set title, image
		case "maps":
		    $imagePath = misc::minimap($row["image"]);
		    break;
		case "units":
		    $imagePath = $row["image"];
		    break;
		case "guides":
		    $imagePath = "";
		    break;
		case "following":
		    if ($condition == "follow")
		    {
			$imagePath = misc::avatar($row["whom"]);
		    }
		    elseif ($condition == "followed")
		    {
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
		    
		    $content .= "<br><a href='?action=show_user_follow".$end."&id=".$row["who"]."' style='float:right;margin-right:10px;'>".misc::lang("show all")."</a>";
		    break;
		}
		$content .= "<a href='?profile=".$show."' title='".user::login_by_uid($show)."'><img src='" . $imagePath . "' width='40' height='40' /></a>";
	    }
	    else
	    {
		$content .= "<a href='?p=detail&table=".$table."&id=".$row["uid"]."'><img src='" . $imagePath . "' width='40' height='40' /></a>";
	    }
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
		$content .= "<a title='' href='index.php'><img src='" . $imagePath . "' class='thumbnail' alt='img' width='240px' height='100px'/></a>";
                
    	$content .= "<div class='blk-top'>";
        $content .= "<h4><a href='?p=detail&table=articles&id=".$row["uid"]."'>" . $title . "</a></h4>";
        $content .= "<p><span class='datetime'>" . $date . "</span><a href='?p=detail&table=articles&id=".$row["uid"]."' class='comment'>" . $comments . " ".ucfirst(misc::lang("comments"))."</a></p>";
        $content .= "</div>";
                
        $content .= "<div class='blk-content'>";
        if(strlen($text) > 500)
        	$text = substr($text,0,500) . "...";
        $content .= "<p>" . $text . "</p>";			
        $content .= "<p><a href='?p=detail&table=articles&id=".$row["uid"]."' class='more-link'>".misc::lang("continue reading")." &raquo;</a></p>"; 
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
	//types: featured, people, editors
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
	    $comments = "";
	    $res_comments = db::num_rows(db::executeQuery("SELECT uid FROM comments WHERE table_id = ".$row["uid"]." AND table_name = '".$table_item."'"));
	    if ($res_comments != 0)
		$comments = "<br />" . misc::lang("amount comments", array($res_comments));
	    switch($table_item)
	    {
		case "maps":
		    $title = $row["title"];
		    $subtitle = misc::lang("featured posted", array("map", $row["posted"], "<a href='?profile=".$row["user_id"]."'>" . $username["login"] . "</a>")) . $comments;
		    $text = str_replace("\r\n", "<br />", $row["description"]);
		    $imagePath =  $row["path"] . "minimap.bmp";
		    break;
		case "units":
		    $title = $row["title"];
		    $subtitle = misc::lang("featured posted", array("unit", $row["posted"], "<a href='?profile=".$row["user_id"]."'>" . $username["login"] . "</a>")) . $comments;
		    $text = str_replace("\r\n", "<br />", $row["description"]);
		    $imagePath = $row["preview_image"];
		    break;
		case "guides":
		    $title = $row["title"];
		    $subtitle = misc::lang("featured posted", array("guide", $row["posted"], "<a href='?profile=".$row["user_id"]."'>" . $username["login"] . "</a>")) . $comments;
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
	    else if($t=="played")
		$content .= "<div id='played-ribbon'></div>";
	    else if($t=="discussed")
		$content .= "<div id='discussed-ribbon'></div>";
	    else if($t=="new_map")
		$content .= "<div id='new_map-ribbon'></div>";
	    else if($t=="new_guide")
		$content .= "<div id='new_guide-ribbon'></div>";
	    else if($t=="new_unit")
		$content .= "<div id='new_unit-ribbon'></div>";
	    else
               	$content .= "<div id='featured-ribbon'></div>";

	    if(strlen($imagePath) > 0)
	    {
		$content .= "<div class='image-block'>";
		$content .= "<a href='?p=detail&id=" . $row["uid"] . "&table=" . $table_item . "' title=''><img src='" . $imagePath . "' alt='featured' style='max-height:350px;max-width:250px;'/></a>";
		$content .= "</div>";
	    }

	    $content .= "<div class='text-block'>";
	    $content .= "<h2>" . strip_tags($title) . "</h2>";
	    $content .= "<p class='post-info'>" . $subtitle . "</p>";
	    $content .= "<p>" . strip_tags($text) . "</p>";
	    $content .= "<p><a href='?p=detail&id=" . $row["uid"] . "&table=" . $table_item . "' class='more-link'>".misc::lang("read more")."</a></p>";
	    $content .= "</div>";
	    $content .= "</div>";
	}

	return $content;
    }

    public static function create_grid($result, $table = "maps", $current_id = 0)
    {
	//Setup
	$columns = 4;	//Amount of columns
	$rows = 4;	//Amount of rows (before starting paging)
	$counter = 0;
	$columns--;
	$maxItemsPerPage = ($columns+1) * $rows;
	$pointer = "#".$table;
	$content = "<a name='".$table."'></a><table>";
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

	    if($table == "maps")
		$span_additional_info = "Rev: ".substr($row["tag"],1)."<br />";
	    else
		$span_additional_info = "";
	    $res_comments = db::num_rows(db::executeQuery("SELECT uid FROM comments WHERE table_id = ".$row["uid"]." AND table_name = '".$table."'"));
	    if ($res_comments != 0)
		$span_additional_info .= misc::lang("amount comments", array($res_comments)) . "<br />";
	    $res_fav = db::num_rows(db::executeQuery("SELECT uid FROM fav_item WHERE table_id = ".$row["uid"]." AND table_name = '".$table."'"));
	    if ($res_fav != 0)
		$span_additional_info .= misc::lang("people favorited", array($res_fav));
	    if ($span_additional_info != "")
		$span_additional_info = "<span>".$span_additional_info."</span>";
	    $content .= "<td id='".misc::current_map_version($row["uid"], $current_id)."'><a class='tooltip' href='?p=detail&table=".$table."&id=".$row["uid"]."'>";
	    if($imagePath != "")
	    	$content .= "<img src='" . $imagePath . "' style='max-height:96px;max-width:96px;'>";
	    $content .= "</br>" . strip_tags($title) . $span_additional_info . "</a></td>";

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
	$pages = "<table><tr><td>";
	
	$gets = "";
	$keys = array_keys($_GET);
	foreach($keys as $key)
	{
	    if($key != "current_grid_page_".$table)
		$gets .= "&" . $key . "=" . $_GET[$key];
	}
	for($i = 1; $i < $nrOfPages+1; $i++)
	{
	    $pages .= misc::paging($nrOfPages, $i, $current, $gets, $table, "", "grid", $pointer);
	}
	$pages .= "</td></tr></table>";
	if ($nrOfPages == 1)
	{ $pages = ""; }

	$content .= "</table>";
	$pages = preg_replace("/(\.\.\.)+/", " ... ", $pages);
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
	$pointer = "#".$table;
	$content = "<a name='".$table."'></a><table>";
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
		    $subtitle = misc::lang("item posted", array($row["posted"], "<a href='?profile=".$row["user_id"]."'>" . $username . "</a>"));
		    $text = $row["description"];
		    break;
		case "units":
		    $title = $row["title"];
		    $imagePath = $row["preview_image"];
		    $subtitle = misc::lang("item posted", array($row["posted"], "<a href='?profile=".$row["user_id"]."'>" . $username . "</a>"));
		    $text = "";
		    break;
		case "guides":
		    $title = $row["title"];
		    $imagePath = "images/guide_" . $row["guide_type"] . ".png";
		    $subtitle = misc::lang("item posted", array($row["posted"], "<a href='?profile=".$row["user_id"]."'>" . $username . "</a>"));
		    $text = "";
		    break;
		case "articles":
		    $title = $row["title"];
		    $imagePath = "";
		    $subtitle = misc::lang("item posted", array($row["posted"], "<a href='?profile=".$row["user_id"]."'>" . $username . "</a>"));
		    $text = "";
		    break;
	    }
	    
	    //TODO: Text should truncate if too large
	    $content .= "<tr>";
	    if($imagePath != "")
	    	$content .= "<td><img src='" . $imagePath . "'></td>";
	    $content .= "<td><a href='?p=detail&table=".$table."&id=".$row["uid"]."'>" . strip_tags($title) . "</a></br>" . $subtitle . "</br>" . strip_tags($text) . "</td></tr>";
	}
	
	$nrOfPages = floor(($total-0.01) / $maxItemsPerPage) + 1;
	$gets = "";
	$pages = "<table><tr><td>";
	$keys = array_keys($_GET);
	foreach($keys as $key)
	    if($key != "current_list_page_".$table)
		$gets .= "&" . $key . "=" . $_GET[$key];
	for($i = 1; $i < $nrOfPages+1; $i++)
	{
	    $pages .= misc::paging($nrOfPages, $i, $current, $gets, $table, "", "list", $pointer);
	}
	$pages .= "</td></tr></table>";
	
	if ($nrOfPages == 1)
	    $pages = "";
	
	$content .= "</table>";
	$pages = preg_replace("/(\.\.\.)+/", " ... ", $pages);
	$content .= $pages;
	return $content;
    }
    
    public static function displayItem($result, $table, $resultNotQuery = false)
    {
    	$content = "";
	$map_version_content = "";
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
		    $delete = misc::lang("delete") . " " . misc::lang(rtrim($table,"s"));
		    $delete = "<a href='?del_item=".$row["uid"]."&del_item_table=".$table."&del_item_user=".$row["user_id"]."' onClick='return confirmDelete(\"".misc::lang("delete this")." ".misc::lang(rtrim($table,"s"))."\")'>".$delete."</a>";
		    $edit = " | <a href='?p=edit_item&table=".$table."&id=".$row["uid"]."'>".misc::lang("edit")."</a>";
		}
	    }
	    else
	    {
		if(db::nextRowFromQuery(db::executeQuery("SELECT * FROM reported WHERE table_name = '".$table."' AND table_id = ".$row["uid"]." AND user_id = " . user::uid())))
		{
		    $reported = misc::lang("already reported");
		}
		else
		{
		    if(user::online())
			$reported = "<a href='?p=detail&table=".$table."&id=".$row["uid"]."&report' onClick='return confirmDelete(\"".misc::lang("report this item")."\")'>".misc::lang("report item")."</a>";
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
		    $title = misc::lang("game map", array(strtoupper($row["g_mod"]))) . ": <font color='#d8ff00'>" . strip_tags($row["title"]) . "</font>";
		    $imagePath = misc::minimap($row["path"]);
		    $subtitle = $title . " " . misc::lang("item posted", array($row["posted"], "<a href='?profile=".$row["user_id"]."'>". $user_name . "</a>"));
		    $text = str_replace("\r\n", "<br />", $row["description"]);
		    break;
		case "units":
		    $title = strip_tags($row["title"]);
		    $imagePath = $row["preview_image"];
		    $subtitle = "<font color='#d8ff00'>".$title."</font> " . misc::lang("item posted", array($row["posted"], "<a href='?profile=".$row["user_id"]."'>". $user_name . "</a>"));
		    $text = "";
		    break;
		case "guides":
		    $imagePath = "images/guide_" . str_replace("\\\\\\", "", $row["guide_type"]) . ".png";
		    $allow = "<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>";
		    $text = strip_tags(str_replace("\\\\\\", "", "<p>". str_replace('\r\n', "", $row["html_content"])."</p>"), $allow);
		    
		    $content .= "<div class='post'>";
		    $content .= "<h2 id='id_display_title' style='margin-left: -2px;'><p>" . strip_tags($row["title"]) . "</p></h2>";
		    
		    $edited_by = "";
		    if (isset($row["uid"]))
		    {
			$res_e = db::executeQuery("SELECT * FROM event_log WHERE table_id = ".$row["uid"]." AND table_name = 'guides' AND type = 'edit'");
			while ($res_e_r = db::nextRowFromQuery($res_e))
			{
			    $edited_name = user::login_by_uid($res_e_r["user_id"]);
			    $edited_by = " | ".misc::lang("last edited by", array("<a href='?profile=".$res_e_r["user_id"]."' id='id_display_username'>".$edited_name."</a>"));
			}
		    }
		    if (!isset($row["no_additional_info"]))
			$content .= "<p class='post-info'>".misc::lang("posted by", array("<a href='?profile=".$row["user_id"]."' id='id_display_username'>". $user_name . "</a>")) . $edited_by . "</p>";
		    $content .= "<p><div id='id_display_text'>" . $text . "</div></p>";
		    $content .= "<p class='postmeta'>";
		    if($reported != "")
			$content .= $reported . " | ";
		    if($delete != "")
			$content .= $delete . " | ";
		    if($favIcon != "")
			$content .= "<a href='?p=detail&table=".$table."&id=".$row["uid"]."&fav'><img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/".$favIcon."'></a> | ";
		    if (isset($row["posted"]))
			$content .= "<span class='date'>".$row["posted"]."</span>";
		    $content .= $edit;
		    $content .= "</p>";
		    $content .= "</div>";
		    return $content;
		    break;
		case "articles":
		    $imagePath = $row["image"];
		    $allow = "<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>";
		    $text = strip_tags(str_replace("\\\\\\", "", "<p>". str_replace('\r\n', "", $row["content"])."</p>"), $allow);
		    
		    $content .= "<div class='post'>";
		    $content .= "<h2 id='id_display_title'>" . strip_tags($row["title"]) . "</h2>";
		    $content .= "<p class='post-info'>".misc::lang("posted by", array("<a href='?profile=".$row["user_id"]."'>". $user_name . "</a>")) . "</p>";
		    $content .= "<p><div id='id_display_text'>" . $text . "</div></p>";
		    $content .= "<p class='postmeta'>";
		    if($reported != "")
			$content .= $reported . " | ";
		    if($delete != "")
			$content .= $delete . " | ";
		    if($favIcon != "")
			$content .= "<a href='?p=detail&table=".$table."&id=".$row["uid"]."&fav'><img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/".$favIcon."'></a> | ";
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
	     
	    $content .= "<tr><td>" . $subtitle;
	    $content .= "</td>";

	    if(user::online())
	    {
		$content .= "<td style='padding: .5em .5em;'><a href='?p=detail&table=".$table."&id=".$row["uid"]."&fav'><img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/".$favIcon."'></a></td>";
	    }
	    $content .= "</tr>";

	    if($text != "")
	    {
		$allow = "<table><tr><td><img><a><b><i><u><p>";
		$text = strip_tags($text, $allow);
		$content .= "<tr><td>".$text."</td></tr>";
	    }
	     
	    if($table == "maps")
	    {
		$content .= "<tr><td><table style='padding:auto;margin:auto;'><tr><td>".misc::lang("author").": ".$row["author"]."</td><td>".misc::lang("size").": ".$row["width"]."x".$row["height"]."</td><td>".misc::lang("tileset").": ".$row["tileset"]."</td></tr></table></td></tr>";
		$players = "";
		$res_p = db::executeQuery("SELECT * FROM map_stats WHERE map_hash = '".$row["maphash"]."'");
		while ($res_p_r = db::nextRowFromQuery($res_p))
		{
		    $players = "; ".misc::lang("mostly played", array(round($res_p_r["avg_players"])));
		}
		$content .= "<tr><td>".misc::lang("map for players", array($row["players"])).$players."</td></tr>";
		$mapfile = explode("-", basename($row["path"]), 3);
		$mapfile = $mapfile[2] . ".oramap";
	     	$download = $row["path"] . $mapfile;
	     	$content .= "<tr><td><a href='".$download."'>".misc::lang("download")."</a></tr></td>";
		
		if ($row["p_ver"] == 0 and $row["n_ver"] == 0)
		    $vers = "<td>".misc::lang("only version")."</td>";
		else
		    $vers = "<td><a href='?action=versions&table=maps&id=".$row["uid"]."'>".misc::lang("check versions")."</a></td>";
		if ($row["user_id"] == user::uid())
		    if ($row["n_ver"] == 0)
			$vers .= "<td><a href='?action=new_version&id=".$row["uid"]."'>".misc::lang("upload new")."</a></td>";
		$map_version_content = "<table><tr><td>".misc::lang("rev").": ".ltrim($row["tag"], "r")."</td>".$vers."</tr></table>";
	    }
	    else if($table == "units")
	    {
	     	$content .= "<tr><td>".misc::lang("description").": " . strip_tags($row["description"]) . "</td></tr>";
	     	$content .= "<tr><td><br>".misc::lang("download files").":";
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
	    $content .= $map_version_content;
	}
	return $content;
    }

    public static function displayEvents($result)
    {
	$data = array();
	array_push($data, misc::lang("latest activity").":");
	while ($row = db::nextRowFromQuery($result))
	{
	    $name = "<a href='?profile=".$row["user_id"]."'>".user::login_by_uid($row["user_id"])."</a>";
	    $type = $row["type"];
	    switch($type)
	    {
		case "add":
		    $desc = " ".misc::lang("added new")." <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".misc::lang(rtrim($row["table_name"],'s'))."</a>";
		    break;
		case "delete_comment":
		    $desc = " ".misc::lang("deleted comment")." <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".misc::lang(rtrim($row["table_name"],'s'))."</a>";
		    break;
		case "report":
		    $desc = " ".misc::lang("reported")." <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".misc::lang(rtrim($row["table_name"],'s'))."</a>";
		    break;
		case "fav":
		    $desc = " ".misc::lang("favorited")." <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".misc::lang(rtrim($row["table_name"],'s'))."</a>";
		    break;
		case "unfav":
		    $desc = " ".misc::lang("unfavorited")." <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".misc::lang(rtrim($row["table_name"],'s'))."</a>";
		    break;
		case "comment":
		    $desc = " ".misc::lang("commented")." <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".misc::lang(rtrim($row["table_name"],'s'))."</a>";
		    break;
		case "login":
		    $desc = " ".misc::lang("logged in");
		    break;
		case "logout":
		    $desc = " ".misc::lang("logged out");
		    break;
		case "edit":
		    $desc = " ".misc::lang("edited")." <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".misc::lang(rtrim($row["table_name"],'s'))."</a>";
		    break;
		case "follow":
		    $desc = " ".misc::lang("started to follow")." <a href='?&profile=".$row["table_id"]."'>".user::login_by_uid($row["table_id"])."</a>";
		    break;
		case "unfollow":
		    $desc = " ".misc::lang("stopped following")." <a href='?&profile=".$row["table_id"]."'>".user::login_by_uid($row["table_id"])."</a>";
		    break;
		case "delete_item":
		    $desc = false;
		    break;
	    }
	    if ($desc)
		array_push($data, $name . $desc . " ".misc::lang("at")." " . $row["posted"]);
	}
	return content::create_dynamic_list($data, 1, "event_log",  11, true, true);
    }

    public static function create_comment_section($result)
    {
	$counter = 0;
	$content = "";
	$comment_page_id = 0;

	$comments = db::num_rows($result);
	$content .= "<h3 id='comments'>" . $comments . " ".misc::lang("responses")."</h3>";
	$content .= "<ol class='commentlist'>";
	while ($comment = db::nextRowFromQuery($result))
	{
	    $counter++;
	    $comment_page_id++;
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
		
	    $content .= "<a name=".$comment_page_id."></a><div class='comment-info'>";			
	    $content .= "<a href='?profile=".$comment["user_id"]."'><img alt='' src='" . $avatarImg . "' style='margin-top:10px; max-width:50' /></a>";
	    $content .= "<cite>";
	    $content .= "<a href='?profile=".$comment["user_id"]."'>" . $author["login"] . "</a> ".misc::lang("says").": <br />";
	    $content .= "<span class='comment-data'><a href='#".$comment_page_id."' title=''>" . $comment["posted"] . "</a></span>";
	    $content .= "</cite>";
	    $content .= "</div>";
                
	    $content .= "<div class='comment-text'>";
	    $content .= "<p>" . stripslashes(stripslashes(str_replace('\r\n', "<br />", strip_tags($comment["content"])))) . "</p>";
	    if (misc::comment_owner($comment["user_id"]))
	    {
		$content .= "<a style='float: right; margin: -130px -35px 0 0; border: 0px solid #2C1F18;color:#ff0000;' href='?delete_comment=".$comment["uid"]."&user_comment=".user::uid()."&table_name=".$comment["table_name"]."&table_id=".$comment["table_id"]."' onClick='return confirmDelete(\"".misc::lang("delete comment")."\")' title='".misc::lang("delete")."'><img src='images/delete.png' style='border: 0px solid #261b15; padding: 0px; max-width:50%;' border='0' alt='delete' /></a>";
	    }
	    $content .= "<div class='reply'>";
	    //$content .= "<a rel='nofollow' class='comment-reply-link' href='index.html'>Reply</a>"; // << need correct page
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
	    $content .= "<h3>".misc::lang("leave a reply")."</h3>";			
	    $content .= "<form action='?p=detail&table=".$table_name."&id=".$table_id."' method='post' id='commentform'>";
	    $content .= "<p>";
	    $content .= "<label for='message'>".misc::lang("your message")."</label><br />";
	    $content .= "<textarea id='message' name='message' rows='10' cols='20' tabindex='4'></textarea>";
	    $content .= "</p>";
	    $content .= "<p class='no-border'>";
	    $content .= "<input class='button' type='submit' value='".misc::lang("submit comment")."' tabindex='5'/>";      		
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
		$pointer = "#".$modifiedName;
		if(isset($_GET["current_dynamic_page_".$modifiedName]))
		    $current = $_GET["current_dynamic_page_".$modifiedName];
		else
		    $current = 1;
		$start = ($current-1) * $maxItemsPerPage * $columns;
		$maxItemsPerPageOrg = $maxItemsPerPage; //original value
		$maxItemsPerPage *= $columns;
		$content .= "<a name='".$modifiedName."'></a><table>";
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
		    $content .= "<tr><td colspan='".$columns."'><a href='javascript:post_to_url(\"?p=dynamic\",{".$params."});'>".misc::lang("show more")." ".$name."</a></td></tr>";
		}
		$content .= "</table>";
		$params = "";
		$gets = "";
		$pages = "<table><tr><td>";
		$keys = array_keys($_GET);
		foreach($keys as $key)
		{
		    if($key != "current_dynamic_page_".$modifiedName)
			$gets .= "&" . $key . "=" . $_GET[$key];
		}
		if (isset($_GET["p"]))
		{
		    if($_GET["p"] == "dynamic")
		    {
			$params = "\"data\":\"".pages::serialize_array($data)."\"";
			$params .= ",\"columns\":\"".$columns."\"";
			$params .= ",\"name\":\"".$modifiedName."\"";
			$params .= ",\"maxItemsPerPage\":\"".$maxItemsPerPageOrg."\"";
			$params .= ",\"header\":\"".$header."\"";
			$params .= ",\"use_pages\":\"1\"";
		    }
		}
		for($i = 1; $i < $nrOfPages+1; $i++)
		{
		    $pages .= misc::paging($nrOfPages, $i, $current, $gets, $modifiedName, $params, "dynamic", $pointer);
		}
		$pages .= "</td></tr></table>";
		if ($nrOfPages == 1)
		    $pages = "";
		if($use_pages)
		{
		    $pages = preg_replace("/(\.\.\.)+/", " ... ", $pages);
		    $content .= $pages;
		}
	    }
    	}
    	return $content;
    }

    public static function create_footer()
    {
	$content = '<div id="footer-outer" class="clear"><div id="footer-wrap">';

	$content .= '<div id="footer-bottom">';

	$content .= '<strong><a href="?p=members">'.misc::lang("members").'</a></strong> |';
	$content .= '<a href="/">'.misc::lang("home").'</a> |';
	$content .= '<strong><a href="#top" class="back-to-top">'.misc::lang("to top").'</a></strong>';

	$content .= '</div>';
	$content .= "<div class='lang' style='padding-bottom:2px;'>
		    <a id='".pages::cur_lang("en")."' href='?lang=en' style='padding-right:3px;'>English</a>
		    <a id='".pages::cur_lang("ru")."' href='?lang=ru' style='padding-right:3px;'>Русский</a>
		    <a id='".pages::cur_lang("sv")."' href='?lang=sv' style='padding-right:3px;'>Swedish</a>
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
		echo "<div class='sidemenu'><ul><li>".misc::lang("members").":</li></ul></div>";
	    	$data = array();
		while ($row = db::nextRowFromQuery($result))
		{
		    $avatar = misc::avatar($row["uid"]);
		    array_push($data,"<a href='?profile=".$row["uid"]."'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='?profile=".$row["uid"]."'>".$row["login"]."</a>");
		}
		echo content::create_dynamic_list($data,2);
	    }
	}
    }
    
    public static function action($request)
    {
	if ($request == "mymaps")
	{
	    if (!user::online())
		return;
	    profile::upload_map();
	    echo "<br /><br /><div class='sidemenu'><ul><li>".misc::lang("your maps").":</li></ul></div>";
	    list($order_by, $request_mod, $request_tileset, $my_items) = content::map_filters("no_show_my_content_filter");
	    $result = db::executeQuery("SELECT * FROM maps WHERE user_id = ".user::uid()." AND g_mod LIKE ('%".$request_mod."%') AND tileset LIKE ('%".$request_tileset."%') GROUP BY maphash ORDER BY ".$order_by);
	    $output = content::create_grid($result);
	    if ($output == "")
	    {
		echo "<table><tr><th>".misc::lang("no maps uploaded")."</th></tr></table>";
	    }
	    echo "<br /><br />" . $output;
	}
	if ($request == "myguides")
	{
	    if (!user::online())
		return;
	    profile::upload_guide();
	    echo "<br /><br /><div class='sidemenu'><ul><li>".misc::lang("your guides").":</li></ul></div>";
	    $result = db::executeQuery( "SELECT * FROM guides WHERE user_id = ".user::uid() );
	    $output = content::create_grid($result, "guides");
	    if ($output == "")
	    {
		echo "<table><tr><th>".misc::lang("no guides uploaded")."</th></tr></table>";
	    }
	    echo $output;
	}
	if ($request == "myunits")
	{
	    if (!user::online())
		return;
	    profile::upload_unit();
	    echo "<br /><br /><div class='sidemenu'><ul><li>".misc::lang("your units").":</li></ul></div>";
	    $result = db::executeQuery("SELECT * FROM units WHERE user_id = ".user::uid());
	    $output = content::create_grid($result, "units");
	    if ($output == "")
	    {
		echo "<table><tr><th>".misc::lang("no units uploaded")."</th></tr></table>";
	    }
	    echo $output;
	}
	if ($request == "versions")
	{
	    if (isset($_GET["table"]) and isset($_GET["id"]))
	    {
		$ok = false;
		$query = "CALL map_versions(".$_GET["id"].")";
		$result = db::executeQuery($query);
		while ($row = db::nextRowFromQuery($result))
		{
		    $list = $row["list"];
		    if ($row["list"] == "")
			return;
		    $query = "SELECT * FROM maps WHERE uid IN (".$list.")";
		    $ok = true;
		    db::next_result();
		}
		if ($ok == true)
		{
		    $result = db::executeQuery($query);
		    echo content::create_grid($result, "maps", $_GET["id"]);
		}
	    }
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
		    array_push($data,"",misc::lang("people like faction", array("<u>".$faction."</u>")).":");
		    while ($row = db::nextRowFromQuery($result))
		    {
			$avatar = misc::avatar($row["uid"]);
			array_push($data,"<a href='?profile=".$row["uid"]."'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='?profile=".$row["uid"]."'>".$row["login"]."</a>");
		    }
		    echo content::create_dynamic_list($data,2,"dyn",10,true,true);
    		}
		else
		{
		    $data = array();
		    array_push($data,misc::lang("no one likes faction", array("<u>".$faction."</u>")));
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
		    array_push($data,"",misc::lang("latest favorited", array("<a href='?profile=".$_GET["favorited_id"]."'>".$usr["login"]."</a>'s")).":");
		    while ($row = db::nextRowFromQuery($result)) {
			$item = db::nextRowFromQuery(db::executeQuery("SELECT * FROM " . $row["table_name"] . " WHERE uid = " . $row["table_id"]));
			if($item) {
			    array_push($data,"<img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/isFav.png'>");
			    array_push($data,misc::lang("favorited the", array(substr($row["table_name"],0,strlen($row["table_name"])-1), "<a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".$item["title"]."</a>", $row["posted"])));
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
	//user follows
	if ($request == "show_user_follow")
	{
	    if (isset($_GET["id"]))
	    {
		$id = $_GET["id"];
		if ($id == user::uid())
		{
		    $name = misc::lang("you");
		    $who = misc::lang("follow", array($name)).":";
		}
		else
		{
		    $name = user::login_by_uid($id);
		    $who = misc::lang("follows", array("<a href='?profile=".$id."'>".$name."</a>")).":";
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
			array_push($data,"<a href='?profile=".$row["whom"]."'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='?profile=".$row["whom"]."'>".user::login_by_uid($row["whom"])."</a>");
		    }
		    echo content::create_dynamic_list($data,2,"dyn",10,true,true);
		}
		else
		{
		    $verb = misc::lang("does");
		    if ($name == misc::lang("you"))
			$verb = misc::lang("do");
		    echo "<table>
			      <tr>
				  <th>".misc::lang("not follow anyone", array($name, $verb))."</th>
			      </tr>
			  </table>
		    ";
		}
	    }
	}
	//user followed by
	if ($request == "show_user_followed")
	{
	    if (isset($_GET["id"]))
	    {
		$id = $_GET["id"];
		if ($id == user::uid())
		{
		    $name = misc::lang("you");
		    $who = misc::lang("are followed by", array($name)).":";
		}
		else
		{
		    $name = user::login_by_uid($id);
		    $who = misc::lang("is followed by", array("<a href='?profile=".$id."'>".$name."</a>")).":";
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
			array_push($data,"<a href='?profile=".$row["who"]."'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='?profile=".$row["who"]."'>".user::login_by_uid($row["who"])."</a>");
		    }
		    echo content::create_dynamic_list($data,2,"dyn",10,true,true);
		}
		else
		{
		    $verb = misc::lang("is");
		    if ($name == misc::lang("you"))
			$verb = misc::lang("are");
		    echo "<table>
			      <tr>
				  <th>".misc::lang("not followed by", array($name, $verb))."</th>
			      </tr>
			  </table>
		    ";
		}
	    }
	}
	//new map version
	if ($request == "new_version")
	{
	    if (isset($_GET["id"]))
	    {
		$query = "SELECT n_ver FROM maps WHERE uid = ".$_GET["id"];
		$result = db::executeQuery($query);
		while ($row = db::nextRowFromQuery($result))
		{
		    if ($row["n_ver"] == 0)
			profile::upload_map($_GET["id"]);
		}
	    }
	}
    }
    
    public static function guide_unit_filters($arg)
    {
	$my_items = false;
	$my_checked = "";
	$sort_by = "latest";
	$type = "";
	if (isset($_POST["apply_filter"]))
	{
	    $sort_by = $_POST["sort"];
	    $type = $_POST["type"];
	    if (isset($_POST[$arg."_my_items"]))
		$my_items = true;
	}
	elseif (isset($_COOKIE[$arg."_sort_by"]))
	{
	    $sort_by = $_COOKIE[$arg."_sort_by"];
	    $type = $_COOKIE[$arg."_type"];
	    if (isset($_COOKIE[$arg."_my_items"]))
	    {
		$my_items = true;
		$my_checked = "checked";
	    }
	}
	$checkbox = "";
	if (user::online())
	    $checkbox = "<input style='float:right; margin-top: 15px; margin-right: 15px;' type='checkbox' name='".$arg."_my_items' ".$my_checked." title='".misc::lang("only my content")."'><label style='float:right; margin-top: 12px; margin-right: 5px;'>".misc::lang("only my content")."</label>";
	//filters
	echo "<form name='".$arg."_filters' method=POST action=''><table style='width:560px;'><tr><th>".misc::lang("sort by").":</th><th>".misc::lang("type").":</th></tr><tr>";
	echo "<td>";
	echo "<select name='sort' id='sort'>";
	echo "<option value='latest' ".misc::option_selected("latest",$sort_by).">".misc::lang("latest first")."</option>";
	echo "<option value='date' ".misc::option_selected("date",$sort_by).">".misc::lang("date")."</option>";
	echo "<option value='alpha' ".misc::option_selected("alpha",$sort_by).">".misc::lang("title")."</option>";
	echo "<option value='alpha_reverse' ".misc::option_selected("alpha_reverse",$sort_by).">".misc::lang("title in reverse")."</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "<td>";
	echo "<select name='type' id='type'>";
	if ($arg == "guide")
	{
	    echo "<option value='any_type' ".misc::option_selected("any_type",$type).">".misc::lang("any")."</option>";
	    echo "<option value='design' ".misc::option_selected("design",$type).">".misc::lang("design filter")."</option>";
	    echo "<option value='mapping' ".misc::option_selected("mapping",$type).">".misc::lang("mapping")."</option>";
	    echo "<option value='modding' ".misc::option_selected("modding",$type).">".misc::lang("modding")."</option>";
	    echo "<option value='coding' ".misc::option_selected("nature",$type).">".misc::lang("coding")."</option>";
	    echo "<option value='other' ".misc::option_selected("other",$type).">".misc::lang("other")."</option>";
	}
	elseif ($arg == "unit")
	{	    
	    echo "<option value='any_type' ".misc::option_selected("any_type",$type).">".misc::lang("any")."</option>";
	    echo "<option value='structure' ".misc::option_selected("structure",$type).">".misc::lang("structure")."</option>";
	    echo "<option value='infantry' ".misc::option_selected("infantry",$type).">".misc::lang("infantry")."</option>";
	    echo "<option value='vehicle' ".misc::option_selected("vehicle",$type).">".misc::lang("vehicle")."</option>";
	    echo "<option value='air-borne' ".misc::option_selected("air-borne",$type).">".misc::lang("air-borne")."</option>";
	    echo "<option value='nature' ".misc::option_selected("nature",$type).">".misc::lang("nature")."</option>";
	    echo "<option value='other' ".misc::option_selected("other",$type).">".misc::lang("other")."</option>";
	}
	echo "</select><br />";
	echo "</td>";
	echo "</tr></table><div style='width:578px;'><input style='float:right;' type='submit' name='apply_filter' value='".misc::lang("apply filters")."'>
	    ".$checkbox."
	    <input type='hidden' name='apply_filter_type' value='".$arg."'>
	    </div></form><br><br>
	";
	// order by
	if ($sort_by == "latest")
	    $order_by = "posted DESC";
	elseif ($sort_by == "date")
	    $order_by = "posted";
	elseif ($sort_by == "alpha")
	    $order_by = "title";
	elseif ($sort_by == "alpha_reverse")
	    $order_by = "title DESC";
	//type
	if ($type == "any_type")
	    $request_type = "";
	else
	    $request_type = $type;
	
	return array($order_by, $request_type, $my_items);
    }

    public static function map_filters($my_content="")
    {
	$my_items = false;
	$my_checked = "";
	$sort_by = "latest";
	$mod = "";
	$tileset = "";
	if (isset($_POST["apply_filter"]))
	{
	    $sort_by = $_POST["sort"];
	    $mod = $_POST["mod"];
	    $tileset = $_POST["tileset"];
	    if (isset($_POST["map_my_items"]))
		$my_items = true;
	}
	elseif (isset($_COOKIE["map_sort_by"]))
	{
	    $sort_by = $_COOKIE["map_sort_by"];
	    $mod = $_COOKIE["map_mod"];
	    $tileset = $_COOKIE["map_tileset"];
	    if (isset($_COOKIE["map_my_items"]))
	    {
		$my_items = true;
		$my_checked = "checked";
	    }
	}
	
	$checkbox = "";
	if ($my_content == "" and user::online())
	    $checkbox = "<input style='float:right; margin-top: 15px; margin-right: 15px;' type='checkbox' name='map_my_items' ".$my_checked." title='".misc::lang("only my content")."'><label style='float:right; margin-top: 12px; margin-right: 5px;'>".misc::lang("only my content")."</label>";

	//filters
	echo "<form name='map_filters' method=POST action=''><table style='width:560px;'><tr><th>".misc::lang("sort by").":</th><th>".misc::lang("mod").":</th><th>".misc::lang("tileset").":</th></tr><tr>";
	echo "<td>";
	echo "<select name='sort' id='sort'>";
	echo "<option value='latest' ".misc::option_selected("latest",$sort_by).">".misc::lang("latest first")."</option>";
	echo "<option value='date' ".misc::option_selected("date",$sort_by).">".misc::lang("date")."</option>";
	echo "<option value='alpha' ".misc::option_selected("alpha",$sort_by).">".misc::lang("title")."</option>";
	echo "<option value='alpha_reverse' ".misc::option_selected("alpha_reverse",$sort_by).">".misc::lang("title in reverse")."</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "<td>";
	echo "<select onChange='mod_tileset();' name='mod' id='mod'>";
	echo "<option value='any_mod' ".misc::option_selected("any_mod",$mod).">".misc::lang("any")."</option>";
	echo "<option value='ra' ".misc::option_selected("ra",$mod).">RA</option>";
	echo "<option value='cnc' ".misc::option_selected("cnc",$mod).">CNC</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "<td>";
	echo "<select name='tileset' id='tileset'>";
	echo "<option value='any_tileset' ".misc::option_selected("any_tileset",$tileset).">".misc::lang("any")."</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "</tr></table><div style='width:578px;'><input style='float:right;' type='submit' name='apply_filter' value='".misc::lang("apply filters")."'>
	    ".$checkbox."
	    <input type='hidden' name='apply_filter_type' value='map'>
	    </div></form>
	";
	
	//next JS script is for generating Tileset list on fly depending on selected mod
	echo "<script type='text/javascript'>
	    function mod_tileset()
	    {
		var chosen_option=document.getElementById('mod').options[document.getElementById('mod').selectedIndex]
		if (chosen_option.value == 'any_mod')
		{
		    document.map_filters.tileset.options.length=0
		    document.map_filters.tileset.options[0] = new Option('".misc::lang("any")."','any_tileset')
		}
		if (chosen_option.value == 'ra')
		{
		    document.map_filters.tileset.options.length=0
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('".misc::lang("any")."','any_tileset',false,".misc::option_selected_bool("any_tileset",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('temperat','temperat',false,".misc::option_selected_bool("temperat",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('snow','snow',false,".misc::option_selected_bool("snow",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('interior','interior',false,".misc::option_selected_bool("interior",$tileset).")
		}
		if (chosen_option.value == 'cnc')
		{
		    document.map_filters.tileset.options.length=0
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('".misc::lang("any")."','any_tileset',false,".misc::option_selected_bool("any_tileset",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('temperat','temperat',false,".misc::option_selected_bool("temperat",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('desert','desert',false,".misc::option_selected_bool("desert",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('winter','winter',false,".misc::option_selected_bool("winter",$tileset).")
		}
	    }
	    mod_tileset()

	</script>";
	// order by
	if ($sort_by == "latest")
	    $order_by = "posted DESC";
	elseif ($sort_by == "date")
	    $order_by = "posted";
	elseif ($sort_by == "alpha")
	    $order_by = "title";
	elseif ($sort_by == "alpha_reverse")
	    $order_by = "title DESC";
	//mod
	if ($mod == "any_mod")
	    $request_mod = "";
	else
	    $request_mod = $mod;
	//tileset
	if ($tileset == "any_tileset")
	    $request_tileset = "";
	else
	    $request_tileset = $tileset;
	
	return array($order_by, $request_mod, $request_tileset, $my_items);
    }
}

class objects
{
    public static function maps()
    {
	echo "<h3>".ucfirst(misc::lang("maps"))."!</h3>";
	list($order_by, $request_mod, $request_tileset, $my_items) = content::map_filters();
	$my = "";
	if ($my_items == true)
	    $my = " AND user_id = ".user::uid()." ";
	$result = db::executeQuery("SELECT * FROM maps WHERE g_mod LIKE ('%".$request_mod."%') AND tileset LIKE ('%".$request_tileset."%') AND n_ver = 0 ".$my."GROUP BY maphash ORDER BY ".$order_by);
	echo content::create_grid($result);
    }
    
    public static function units()
    {
	echo "<h3>".ucfirst(misc::lang("units"))."!</h3>";
	list($order_by, $request_type, $my_items) = content::guide_unit_filters("unit");
	$my = "";
	if ($my_items == true)
	    $my = " AND user_id = ".user::uid()." ";
	$result = db::executeQuery("SELECT * FROM units WHERE type LIKE ('%".$request_type."%') ".$my."ORDER BY ".$order_by);
	echo content::create_grid($result,"units");
    }
    
    public static function guides()
    {
	echo "<h3>".ucfirst(misc::lang("guides"))."!</h3>";
	list($order_by, $request_type, $my_items) = content::guide_unit_filters("guide");
	$my = "";
	if ($my_items == true)
	    $my = " AND user_id = ".user::uid()." ";
	$result = db::executeQuery("SELECT * FROM guides WHERE guide_type LIKE ('%".$request_type."%') ".$my."ORDER BY ".$order_by);
	echo content::create_grid($result,"guides");
    }
    
    public static function about()
    {
	echo "<h3>".ucfirst(misc::lang("about"))."!</h3>";
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
	    echo misc::lang("missing data");
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
		    $arr = array("title" => str_replace("\\\\\\", "", $row["title"]), "html_content" => str_replace("\\\\\\", "",  str_replace('\r\n', "", $row["html_content"])), "posted" => "<a href='?p=detail&table=guides&id=".$row["uid"]."'>Back to guide's page</a>", "guide_type" => "", "user_id" => user::uid(), "no_additional_info" => "");
		    echo content::displayItem($arr,"guides",true);
		    
		    echo "<form id=\"form_class\" enctype=\"multipart/form-data\" method=\"POST\" action=\"\">
			    <label>Upload guide:</label>
			    <br />
			    <label>".ucfirst(misc::lang("title")).": <input id='id_guide_title' type='text' value='".str_replace("\\\\\\", "", $row["title"])."' name='edit_guide_title' onkeyup='updateContent(\"id_display_title\",\"id_guide_title\");' onchange='updateContent(\"id_display_title\",\"id_guide_title\");' onkeypress='updateContent(\"id_display_title\",\"id_guide_title\");' /></label>
			    <br />
			    <label>".ucfirst(misc::lang("text")).": <textarea id='id_guide_text' name='edit_guide_text' cols='40' rows='5' onkeyup='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");' onchange='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");' onkeypress='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");'>".str_replace("\\\\\\", "",   str_replace("<br />", "\r\n", str_replace('\r\n', "", $row["html_content"])))."</textarea></label>
			    <br />
			    <select name='edit_guide_type'>";
		    echo "<option value='other' ".misc::option_selected("other", $row["guide_type"]).">".misc::lang("other")."</option>";
		    echo "<option value='design' ".misc::option_selected("design", $row["guide_type"]).">".misc::lang("design filter")."</option>";
		    echo "<option value='mapping' ".misc::option_selected("mapping", $row["guide_type"]).">".misc::lang("mapping")."</option>";
		    echo "<option value='modding' ".misc::option_selected("modding", $row["guide_type"]).">".misc::lang("modding")."</option>";
		    echo "<option value='coding' ".misc::option_selected("coding", $row["guide_type"]).">".misc::lang("coding")."</option>";

		    echo "</select>
			    <br />
			    <input type=\"hidden\" name=\"edit_guide_uid\" value=\"".$row["uid"]."\" />
			    <input type=\"submit\" name=\"submit\" value=\"".misc::lang("edit")."\" />
			    </form>
		    ";
		}
	    }
	}
    }
    
    public static function detail()
    {
	$result = db::executeQuery("SELECT * FROM " . $_GET['table'] . " WHERE uid = " . $_GET['id'] . "");
	while (db::nextRowFromQuery($result))
	{
	    $result = db::executeQuery("SELECT * FROM " . $_GET['table'] . " WHERE uid = " . $_GET['id'] . "");
	    echo content::displayItem($result, $_GET['table']);

	    $result = db::executeQuery("SELECT * FROM comments WHERE table_name = '" . $_GET['table'] . "' AND table_id = '" . $_GET['id'] . "' ORDER by posted");
	    echo content::create_comment_section($result);
	
	    echo content::create_comment_respond($_GET['table'],$_GET['id']);
	}
    }
    
    public static function search()
    {
    	if(isset($_GET["qsearch"]))
    	{
	    if (trim($_GET["qsearch"]) == "")
	    {
		echo "<table>
			  <tr>
			      <th>".misc::lang("empty request")."</th>
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
		    $content .= "<br><label>".misc::lang($value)." ".misc::lang("found").":</label>";
		    $content .= $output;
		}
	    }

	    $result = db::executeQuery("SELECT * FROM users WHERE login LIKE '%".$search."%'");
	    if (db::num_rows($result) > 0)
	    {
		$content .= "<br><label>".misc::lang("users")." ".misc::lang("found").":</label>";
	    	$data = array();
			while ($row = db::nextRowFromQuery($result))
		    	array_push($data,"<a href='?profile=".$row["uid"]."'>".$row["login"]."</a>");
		    $content .= content::create_dynamic_list($data,1);
	    }
	    if ($content == "")
	    {
		echo "<table>
			  <tr>
			      <th>".misc::lang("nothing found")."</th>
			  </tr>
		      </table>
		";
		return;
	    }
	    echo $content;
    	}
    }
}

?>
