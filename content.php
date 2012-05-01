<?PHP

class content
{
    public static function head()
    {
	header::main();

	echo "<html><head><title>";
	echo header::pageTitle();
	echo "</title>";

	echo "<script type='text/javascript' src='libs/multifile.js'></script>";
	echo "<script type='text/javascript' src='libs/functions.js'></script>";
	echo "<script type='text/javascript' src='libs/jquery.js'></script>";
	echo "<script type='text/javascript' src='libs/enhanced_file_upload.js'></script>";
	echo "<script type='text/javascript' src='libs/password/mocha.js'></script>";

	echo "<link rel='stylesheet' type='text/css' media='screen' href='css/screen.css' />
	</head>";
    }
		
    public static function body_head()
    {
	echo "
	    <div id='header'>
		<p id='rss-feed'><a href='libs/feed.php' class='feed'>RSS</a></p>
		<a name='top'></a>
		<h1 id='logo-text'><a href='/' title=''>OpenRA - Resources</a></h1>		
		<p id='slogan'>Brings your content</p>
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
		    <a href='?register'>register</a>
		</div>
		<div id='recover_link'>
		    <a href=\"?recover\">recover</a>
		</div>
	    ";
	}
	echo "<form id='quick-search' action='index.php' method='GET'>
		<p>
		<input class='tbox' id='qsearch' type='text' name='qsearch' onclick=\"this.value='';\" onfocus=\"this.select()\" onblur=\"this.value=!this.value?'Search...':this.value;\" value='Search...' />
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
	echo "<form method='POST' action=''>
	    Login: <input type='text' name='login'>
	    Password: <input type='password' name='pass'>
	    <input style='position:absolute; right: -25px; top: 15px;' type='checkbox' name='remember' value='yes' checked title='remember me'>
	    <input type='submit' value='sign in'>
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
	echo "<li id='"; echo pages::current('', $request); echo"'><a href='/'>home</a></li>";
	echo "<li id='"; echo pages::current('maps', $request); echo"'><a href='?p=maps'>maps</a></li>";
	echo "<li id='"; echo pages::current('units', $request); echo"'><a href='?p=units'>units</a></li>";
	echo "<li id='"; echo pages::current('guides', $request); echo"'><a href='?p=guides'>guides</a></li>";
	echo "<li id='"; echo pages::current('replays', $request); echo"'><a href='?p=replays'>replays</a></li>";
            
	if (user::online())
	{
	    echo "<li style='float:right;' id=''><a href='?logout'>logout</a></li>";
	    echo "<li style='float:right;' id='"; echo pages::current('profile', $request); echo"'><a href='?profile=".user::uid()."'>profile</a></li>";
	    $pm_title = "";
	    $pm_notify = "";
	    $query = "SELECT * FROM pm WHERE to_user_id = ".user::uid()." AND isread = 0";
	    $res = db::executeQuery($query);
	    if (db::num_rows($res) > 0)
	    {
		if (db::num_rows($res) == 1)
		    $s = "";
		else
		    $s = "s";
		$pm_title = "title='".db::num_rows($res)." new message".$s."'";
		$pm_notify = "<span style='padding: 30px 15px 17px 11px;float:right;color:#ff0000;'>".db::num_rows($res)." new message".$s." -></span>";
	    }
	    echo "<li style='float:right;' id='"; echo pages::current('mail', $request); echo"'><a href='?p=mail&m=inbox' ".$pm_title.">pm</a></li>".$pm_notify;
	}
    }

    public static function create_register_form()
    {
	echo "<form id='register_form' method='POST' action=''>
	    <table style='text-align:right;'><tr><td collspan='2'><b>
	    Registration
	    </b></td></tr><tr><td>
	    Login</td><td><input type='text' name='rlogin'></td></tr><tr><td>
	    Password</td><td><input type='password' id='inputPassword' name='rpass'>
	    <div id='complexity' class='default'>Password security</div></td></tr><tr><td>
	    Re-enter password</td><td><input type='password' name='verpass'></td></tr><tr><td>
	    E-mail</td><td><input type='text' name='email'></td></tr><tr><td>
	    <input type='hidden' name='act'>
	    <td>
	";
	require_once('libs/recaptchalib.php');
	$publickey = "6Ldq-soSAAAAADuu6iGZoCiTSOzBcoKXBwlhjM5u";
	echo recaptcha_get_html($publickey);
	
	echo "</td></tr><tr><td><input type='submit' value='Confirm'
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
		case "screenshot_group":
		    $imagePath = $row["image"];
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
		if ($follow > 8)
		{
		    $content .= "<br /><br ><a href='?action=show_user_follow".$end."&id=".$row["who"]."' style='float:right;margin-right:10px;margin-top:-15px;'>Show all</a>";
		    break;
		}
		$content .= "<a href='?profile=".$show."' title='".user::login_by_uid($show)."'><img src='" . $imagePath . "' width='40' height='40' /></a>";
	    }
	    else if ($table == "screenshot_group")
	    {
		$content .= "<a href='".$imagePath."' target=_blank><img src='" . $imagePath . "' width='40' height='40' /></a>";
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
        $content .= "<p><span class='datetime'>" . $date . "</span><a href='?p=detail&table=articles&id=".$row["uid"]."' class='comment'>" . $comments . " Comments</a></p>";
        $content .= "</div>";
                
        $content .= "<div class='blk-content'>";
        if(strlen($text) > 500)
        	$text = substr($text,0,500) . "...";
        $content .= "<p>" . $text . "</p>";			
        $content .= "<p><a href='?p=detail&table=articles&id=".$row["uid"]."' class='more-link'>continue reading &raquo;</a></p>"; 
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
		$comments = "<br />".$res_comments." comments";
	    switch($table_item)
	    {
		case "maps":
		    $title = $row["title"];
		    $subtitle = "map posted at ".$row["posted"]." by <a href='?profile=".$row["user_id"]."'>" . $username["login"] . "</a>" . $comments;
		    $text = str_replace("\r\n", "<br />", $row["description"]);
		    $imagePath =  $row["path"] . "minimap.bmp";
		    break;
		case "units":
		    $title = str_replace("_", " ", $row["title"]);
		    $subtitle = "unit posted at ".$row["posted"]." by <a href='?profile=".$row["user_id"]."'>" . $username["login"] . "</a>" . $comments;
		    $text = str_replace("\r\n", "<br />", $row["description"]);
		    $imagePath = $row["preview_image"];
		    break;
		case "guides":
		    $title = $row["title"];
		    $subtitle = "guide posted at ".$row["posted"]." by <a href='?profile=".$row["user_id"]."'>" . $username["login"] . "</a>" . $comments;
		    $text = "";
		    $imagePath = "images/guide_" . $row["guide_type"] . ".png";
		    break;
		case "replays":
		    $title = $row["title"];
		    $subtitle = "replay posted at ".$row["posted"]." by <a href='?profile=".$row["user_id"]."'>" . $username["login"] . "</a>" . $comments;
		    $query = "SELECT * FROM replay_players WHERE id_replays = ".$row["uid"]." ORDER BY team";
		    $res_players = db::executeQuery($query);
		    $players = "";
		    while ($inner_row = db::nextRowFromQuery($res_players))
		    {
			$players .= $inner_row["name"] . ", ";
		    }
		    if ($players != "")
		    $players = "Players: ".rtrim($players,", ");
		    $text = "Version: ".$row["version"]."<br />Mods: ".$row["mods"]."<br />Server name: ".$row["server_name"]."<br />".$players;
		    $imagePath = "images/replay.png";
		    break;
	    }
	    
	    $content .= "<div id='featured-block' class='clear'>";
	    $content .= "<div id='featured-ribbon' style='background: url(../images/".$t."-ribbon.png) no-repeat;'></div>";

	    if(strlen($imagePath) > 0)
	    {
		$content .= "<div class='image-block'>";
		$content .= "<a href='?p=detail&id=" . $row["uid"] . "&table=" . $table_item . "' title=''><img src='" . $imagePath . "' alt='featured' style='max-height:350px;max-width:250px;'/></a>";
		$content .= "</div>";
	    }

	    $content .= "<div class='text-block'>";
	    $content .= "<h2>" . strip_tags($title) . "</h2>";
	    $content .= "<p class='post-info'>" . $subtitle . "</p>";
	    $content .= "<p>" . strip_tags($text, "<br>") . "</p>";
	    $content .= "<p><a href='?p=detail&id=" . $row["uid"] . "&table=" . $table_item . "' class='more-link'>Read More</a></p>";
	    $content .= "</div>";
	    $content .= "</div>";
	}

	return $content;
    }

    public static function create_grid($result, $table = "maps", $current_id=0, $columns=4, $rows=4)
    {
	//Setup
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
		    $title = str_replace("_", " ", $row["title"]);
		    $imagePath = $row["preview_image"];
		    break;
		case "guides":
		    $title = $row["title"];
		    $imagePath = "images/guide_" . $row["guide_type"] . ".png";
		    break;
		case "replays":
		    $title = $row["title"]." by ".user::login_by_uid($row["user_id"]);
		    $imagePath = "images/replay.png";
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
		$span_additional_info .= $res_comments . " comments<br />";
	    $res_fav = db::num_rows(db::executeQuery("SELECT uid FROM fav_item WHERE table_id = ".$row["uid"]." AND table_name = '".$table."'"));
	    if ($res_fav != 0)
		$span_additional_info .= $res_fav . " people favorited";
	    if ($span_additional_info != "")
		$span_additional_info = "<span>".$span_additional_info."</span>";
	    $content .= "<td id='".misc::current_map_version($row["uid"], $current_id)."'><a class='tooltip' href='?p=detail&table=".$table."&id=".$row["uid"]."'>";
	    if($imagePath != "")
	    	$content .= "<img src='" . $imagePath . "' style='max-height:96px;max-width:96px;'>";
	    $content .= "</br>" . strip_tags($title) . $span_additional_info . "</a></td>";

	    if($counter > $columns - 1)
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
	    $pages .= misc::paging($nrOfPages, $i, $current, $gets, $table, "grid", $pointer);
	}
	$pages .= "</td></tr></table>";
	if ($nrOfPages == 1)
	{ $pages = ""; }

	$content .= "</table>";
	$pages = preg_replace("/(\.\.\.)+/", " ... ", $pages);
	$content .= $pages;
	return $content;
    }

    public static function create_list($result, $table, $maxItemsPerPage=10, $u_id=-1)
    {
	$num_rows = db::num_rows($result);
	if ( $num_rows == 0)
	    return "";
	if(isset($_GET["current_list_page_".$table]))
		$current = $_GET["current_list_page_".$table];
	else
		$current = 1;
	$total = db::num_rows($result);
	$pointer = "#".$table;
	$content = "<a name='".$table."'></a><table>";
	if ($u_id != -1)
	{
	    if ($u_id == user::uid())
		if ($table == "fav_item")
		    $content .= "<th></th><th>Your latest favorited items (".$num_rows.")</th>";
		else
		    $content .= "<th>Your ".$table." (".$num_rows.")</th><th></th>";
	    else
		if ($table == "fav_item")
		    $content .= "<th></th><th>".user::login_by_uid($u_id)."'s latest favorited items (".$num_rows.")</th>";
		else
		    $content .= "<th>".user::login_by_uid($u_id)."'s ".$table." (".$num_rows.")</th><th></th>";
	}
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
		    $title = "<a href='?p=detail&table=".$table."&id=".$row["uid"]."'>" . strip_tags($title) . "</a></br>";
		    $imagePath = "<td><a href='?p=detail&table=maps&id=".$row["uid"]."'><img src='" . misc::minimap($row["path"]) . "'></a></td>";
		    $subtitle = "posted at <i>".$row["posted"]."</i> by <a href='?profile=".$row["user_id"]."'>" . $username . "</a>";
		    $text = "Description: ".str_replace("\\\\\\", "", $row["description"]);
		    if ($row["additional_desc"] != "")
			$text .= "<br />Additional info: ".str_replace("\\\\\\", "", $row["additional_desc"]);
		    $text .= "<br />Rev: ".ltrim($row["tag"], "r");
		    break;
		case "units":
		    $title = str_replace("_", " ", $row["title"]);
		    $title = "<a href='?p=detail&table=".$table."&id=".$row["uid"]."'>" . strip_tags($title) . "</a></br>";
		    $imagePath = "<td><a href='?p=detail&table=units&id=".$row["uid"]."'><img src='" . $row["preview_image"] . "'></a></td>";
		    $subtitle = "posted at <i>".$row["posted"]."</i> by <a href='?profile=".$row["user_id"]."'>" . $username . "</a>";
		    $text = "Description: ".str_replace("\\\\\\", "", $row["description"]);
		    break;
		case "guides":
		    $title = $row["title"];
		    $title = "<a href='?p=detail&table=".$table."&id=".$row["uid"]."'>" . strip_tags($title) . "</a></br>";
		    $imagePath = "<td><a href='?p=detail&table=guides&id=".$row["uid"]."'><img src='images/guide_" . $row["guide_type"] . ".png'></a></td>";
		    $subtitle = "posted at <i>".$row["posted"]."</i> by <a href='?profile=".$row["user_id"]."'>" . $username . "</a>";
		    $text = "";
		    break;
		case "replays":
		    $title = $row["title"];
		    $title = "<a href='?p=detail&table=".$table."&id=".$row["uid"]."'>" . strip_tags($title) . "</a></br>";
		    $query = "SELECT maphash,path FROM maps WHERE maphash = '".$row["maphash"]."' GROUP BY maphash";
		    $res = db::executeQuery($query);
		    $path = "";
		    while ($inner_row = db::nextRowFromQuery($res))
		    {
			$path = $inner_row["path"];
		    }
		    $imagePath = "<td><img src='" . misc::minimap($path) . "'></td>";
		    $subtitle = "posted at <i>".$row["posted"]."</i> by <a href='?profile=".$row["user_id"]."'>" . $username . "</a>";
		    $query = "SELECT * FROM replay_players WHERE id_replays = ".$row["uid"]." ORDER BY team";
		    $res_players = db::executeQuery($query);
		    $players = "";
		    while ($inner_row = db::nextRowFromQuery($res_players))
		    {
			$players .= $inner_row["name"] . ", ";
		    }
		    if ($players != "")
			$players = "Players: ".rtrim($players,", ");
		    $desc = "";
		    if ($row["description"] != "")
			$desc = "<br />Description: ".str_replace("\\\\\\", "", $row["description"]);
		    $text = "<br />Version: ".$row["version"]."<br />Mods: ".$row["mods"]."<br />Server name: ".$row["server_name"]."<br />".$players.$desc;
		    break;
		case "articles":
		    $title = $row["title"];
		    $title = "<a href='?p=detail&table=".$table."&id=".$row["uid"]."'>" . strip_tags($title) . "</a></br>";
		    $imagePath = "<td><a href='?p=detail&table=articles&id=".$row["uid"]."'><img src='images/article.png'></a></td>";
		    $subtitle = "posted at <i>".$row["posted"]."</i> by <a href='?profile=".$row["user_id"]."'>" . $username . "</a>";
		    $text = "";
		    break;
		case "fav_item":
		    $title = "favorited the ".rtrim($row["table_name"],"s")." \"<a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".misc::item_title_by_uid($row["table_id"], $row["table_name"])."</a>\" at <i>".$row["posted"]."</i>";
		    $imagePath = "<td><img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/isFav.png'></td>";
		    $subtitle = "";
		    $text = "";
		    break;
		case "comments":
		    $title = "";
		    $subtitle = "<i>commented on ".$row["posted"]."</i><br />";
		    $inner_result = db::executeQuery("SELECT * FROM ".$row["table_name"]." WHERE uid = ".$row["table_id"]);
		    while ($inner_row = db::nextRowFromQuery($inner_result))
		    {
			switch($row["table_name"])
			{
			    case "maps":
				$imagePath = "<td><a href='?p=detail&table=maps&id=".$inner_row["uid"]."'><img src='" . misc::minimap($inner_row["path"]) . "' style='max-width:60px;'></a></td>";
				break;
			    case "units":
				$imagePath = "<td><a href='?p=detail&table=units&id=".$inner_row["uid"]."'><img src='" . $inner_row["preview_image"] . "'></a></td>";
				break;
			    case "guides":
				$imagePath = "<td><a href='?p=detail&table=guides&id=".$inner_row["uid"]."'><img src='images/guide_" . $inner_row["guide_type"] . ".png'></a></td>";
				break;
			    case "replays":
				$imagePath = "<td><a href='?p=detail&table=replays&id=".$inner_row["uid"]."'><img src='images/replay.png'></a></td>";
				break;
			    case "articles":
				$imagePath = "<td><a href='?p=detail&table=articles&id=".$inner_row["uid"]."'><img src='images/article.png'></a></td>";
				break;
			}
		    }
		    $text = stripslashes(stripslashes(str_replace('\r\n', "<br />", strip_tags($row["content"]))));
		    break;
	    }
	    
	    //TODO: Text should truncate if too large
	    $content .= "<tr>";
	    if($imagePath != "")
	    	$content .= $imagePath;
	    $content .= "<td style='margin-top: 0; vertical-align: top;'>" . $title . $subtitle . "</br>" . strip_tags($text, "<br><i>") . "</td></tr>";
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
	    $pages .= misc::paging($nrOfPages, $i, $current, $gets, $table, "list", $pointer);
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
	$screenshots = "";
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
		    $delete = "Delete " . rtrim($table,"s");
		    $delete = "<a href='?del_item=".$row["uid"]."&del_item_table=".$table."&del_item_user=".$row["user_id"]."' onClick='return confirmDelete(\" delete this ".rtrim($table,"s")."\")'>".$delete."</a>";
		    $edit = " | <a href='?p=edit_item&table=".$table."&id=".$row["uid"]."'>Edit</a>";
		}
	    }
	    else
	    {
		if(db::nextRowFromQuery(db::executeQuery("SELECT * FROM reported WHERE table_name = '".$table."' AND table_id = ".$row["uid"]." AND user_id = " . user::uid())))
		{
		    $reported = "You've already reported this item";
		}
		else
		{
		    if(user::online())
			$reported = "<a href='?p=detail&table=".$table."&id=".$row["uid"]."&report' onClick='return confirmDelete(\" report this ".rtrim($table,"s")."\")'>Report this ".rtrim($table,"s")."</a>";
		}
	    }
	    $favIcon = "";
	    if(isset($row["uid"]) && user::online())
	    {
		$favIcon = "notFav.png";
		if( db::nextRowFromQuery(db::executeQuery("SELECT * FROM fav_item WHERE table_name = '".$table."' AND table_id = ".$row["uid"]." AND user_id = " . user::uid())) )
		{
		    $favIcon = "isFav.png";
		}
		$favTimes = "";
		$row_f = db::nextRowFromQuery(db::executeQuery("SELECT COUNT(*) AS count FROM fav_item WHERE table_name = '".$table."' AND table_id = ".$row["uid"]));
		if ($row_f["count"] > 0)
		    $favTimes = "favorited ".$row_f["count"]." times";
	    }
	    $viewed = 0;
	    if (isset($row["uid"]))
	    {
		$query = "SELECT viewed FROM $table WHERE uid = ".$row["uid"];
		$view_res = db::executeQuery($query);
		while ($view_row = db::nextRowFromQuery($view_res))
		{
		    $viewed = (int)$view_row["viewed"] + 1;
		}
		$query = "UPDATE $table SET viewed = ? WHERE uid = ?";
		db::executeQuery($query, array($viewed, $row["uid"]));
	    }
	    
	    switch($table)
	    {
		case "maps":
		    $title = strtoupper($row["g_mod"])." map: <font color='#d8ff00'>" . strip_tags($row["title"]) . "</font>";
		    $imagePath = misc::minimap($row["path"]);
		    $subtitle = $title . " posted at <i>".$row["posted"]."</i> by <a href='?profile=".$row["user_id"]."'>". $user_name . "</a>";
		    $add_add_info = "";
		    if ($row["additional_desc"] != "")
		    {
			$add_edit = "";
			if ($row["user_id"] == user::uid())
			    $add_edit = "<a style='float:right;' href='?p=detail&table=maps&id=".$row["uid"]."&edit_map_info'>edit</a>";
			if (isset($_GET["edit_map_info"]) and user::uid() == $row["user_id"])
			{
			    $text_add = "<form method=POST><input type='text' name='add_map_info' value='".str_replace("\\\\\\", "", str_replace("'", "`", $row["additional_desc"]))."'><input type='hidden' name='map_id' value='".$row["uid"]."'> <input type='submit' value='submit'><input type='hidden' name='user_id' value='".$row["user_id"]."'></form>";
			}
			else
			    $text_add = "Additional info: " . str_replace("\\\\\\", "", str_replace("\r\n", "<br />", $row["additional_desc"])) . $add_edit;
		    }
		    else
		    {
			if ($row["user_id"] == user::uid())
			{
			    $add_add_info = "<a style='float:right;' href='?p=detail&table=maps&id=".$row["uid"]."&add_map_info'>add additional info</a>";
			    if (isset($_GET["add_map_info"]))
				$text_add = "<form method=POST><input type='text' name='add_map_info'><input type='hidden' name='map_id' value='".$row["uid"]."'> <input type='submit' value='submit'><input type='hidden' name='user_id' value='".$row["user_id"]."'></form>";
			}
		    }
		    $text = str_replace("\r\n", "<br />", str_replace("\\\\\\", "", $row["description"])) . $add_add_info;
		    break;
		case "units":
		    $title_origin = strip_tags($row["title"]);
		    $imagePath = $row["preview_image"];

		    $add_description = "";
		    $desc_edit = "";
		    
		    if ($row["description"] != "")
		    {
			if ($row["user_id"] == user::uid())
			    $desc_edit = "<a style='float:right;' href='?p=detail&table=units&id=".$row["uid"]."&edit_unit_info'>edit</a>";
			if (isset($_GET["edit_unit_info"]) and user::uid() == $row["user_id"])
			    $text_desc = "<form method=POST><input type='text' name='add_unit_info' value='".str_replace("\\\\\\", "", str_replace("'", "`", $row["description"]))."'><input type='hidden' name='unit_id' value='".$row["uid"]."'> <input type='submit' value='submit'><input type='hidden' name='user_id' value='".$row["user_id"]."'></form>";
		    }
		    else
		    {
			if ($row["user_id"] == user::uid())
			{
			    $add_description = "<a style='float:right;padding-left: 7px;' href='?p=detail&table=units&id=".$row["uid"]."&add_unit_info'>add description</a>";
			    if (isset($_GET["add_unit_info"]))
				$text_desc = "<form method=POST><input type='text' name='add_unit_info'><input type='hidden' name='unit_id' value='".$row["uid"]."'> <input type='submit' value='submit'><input type='hidden' name='user_id' value='".$row["user_id"]."'></form>";
			}
		    }
		    
		    $title = "Unit: <font color='#d8ff00'>" . str_replace("_", " ", $title_origin) . "</font>   $add_description</td></tr><tr>";
		    $subtitle = $title . "<td>Posted at <i>" . $row["posted"] . "</i> by <a href='?profile=".$row["user_id"]."'>" . $user_name . "</a>";
		    $text = "";
		    if (isset($text_desc))
			$description = "<tr><td>".$text_desc."</td></tr>";
		    else
		    {
			$description = "";
			if ($row["description"] != "")
			    $description = "<tr><td>Description: " . str_replace("\\\\\\", "", str_replace("\r\n", "<br />", $row["description"])) . $desc_edit . "</td></tr>";
		    }
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
			    $edited_by = " | last edited by <a href='?profile=".$res_e_r["user_id"]."' id='id_display_username'>".$edited_name."</a>";
			}
		    }
		    if (!isset($row["no_additional_info"]))
			$content .= "<p class='post-info'>Posted by <a href='?profile=".$row["user_id"]."' id='id_display_username'>". $user_name . "</a>" . $edited_by . "<span style='float:right;'>viewed ".$viewed." times</span></p>";
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
		    $content .= "<p class='post-info'>Posted by <a href='?profile=".$row["user_id"]."'>". $user_name . "</a><span style='float:right;'>viewed ".$viewed." times</span></p>";
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
		case "replays":
		    $add_description = "";
		    $desc_edit = "";
		    if ($row["description"] != "")
		    {
			if ($row["user_id"] == user::uid())
			    $desc_edit = "<a style='float:right;' href='?p=detail&table=replays&id=".$row["uid"]."&edit_replay_info'>edit</a>";
			if (isset($_GET["edit_replay_info"]) and user::uid() == $row["user_id"])
			    $text_desc = "<form method=POST><input type='text' name='add_replay_info' value='".str_replace("'", "`", str_replace("\\\\\\", "", $row["description"]))."'><input type='hidden' name='replay_id' value='".$row["uid"]."'> <input type='submit' value='submit'><input type='hidden' name='user_id' value='".$row["user_id"]."'></form>";
		    }
		    else
		    {
			if ($row["user_id"] == user::uid())
			{
			    $add_description = "<a style='float:right;padding-left: 7px;' href='?p=detail&table=replays&id=".$row["uid"]."&add_replay_info'>add description</a>";
			    if (isset($_GET["add_replay_info"]))
				$text_desc = "<form method=POST><input type='text' name='add_replay_info'><input type='hidden' name='replay_id' value='".$row["uid"]."'> <input type='submit' value='submit'><input type='hidden' name='user_id' value='".$row["user_id"]."'></form>";
			}
		    }
		    $title = "Replay: <font color='#d8ff00'>" . strip_tags($row["title"]) . "</font>   $add_description</td></tr><tr>";
		    $subtitle = $title . "<td>Posted at <i>" . $row["posted"] . "</i> by <a href='?profile=".$row["user_id"]."'>" . $user_name . "</a>";
		    $text = "";
		    if (isset($text_desc))
			$description = "<tr><td>".$text_desc."</td></tr>";
		    else
		    {
			$description = "";
			if ($row["description"] != "")
			    $description = "<tr><td>Description: " . str_replace("\\\\\\", "", str_replace("\r\n", "<br />", $row["description"])) . $desc_edit . "</td></tr>";
		    }
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
		$content .= "<td style='padding: .5em .5em;'><a href='?p=detail&table=".$table."&id=".$row["uid"]."&fav'><img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/".$favIcon."' title='".$favTimes."'></a></td>";
	    }
	    $content .= "</tr>";

	    if($text != "")
	    {
		$allow = "<table><tr><td><img><a><b><i><u><p>";
		$text = strip_tags($text, $allow);
		$content .= "<tr><td>".$text."</td></tr>";
	    }
	    if (isset($text_add))
		$content .= "<tr><td>".$text_add."</td></tr>";
	     
	    if($table == "maps")
	    {
		$content .= "<tr><td><table style='padding:auto;margin:auto;'><tr><td>author: ".$row["author"]."</td><td>size: ".$row["width"]."x".$row["height"]."</td><td>tileset: ".$row["tileset"]."</td><td>type: ".$row["type"]."</td></tr></table></td></tr>";
		$players = "";
		$res_p = db::executeQuery("SELECT * FROM map_stats WHERE map_hash = '".$row["maphash"]."'");
		while ($res_p_r = db::nextRowFromQuery($res_p))
		{
		    $players = "; mostly played with ".round($res_p_r["avg_players"])." players";
		}
		$content .= "<tr><td>".$row["players"]." players map".$players."</td></tr>";
		$mapfile = explode("-", basename($row["path"]), 3);
		$mapfile = $mapfile[2] . ".oramap";
	     	$download = $row["path"] . $mapfile;
		$content .= "<tr><td>This page is viewed ".$viewed." times</td></tr>";
	     	$content .= "<tr><td><a href='".$download."'>Download</a></tr></td>";
		if (user::uid() == $row["user_id"])
		    $content .= "<tr><td><a href='?action=manage_screenshots&table=maps&id=".$row["uid"]."'>Manage screenshots</a></td></tr>";
		
		if ($row["p_ver"] == 0 and $row["n_ver"] == 0)
		    $vers = "<td>This is the only version</td>";
		else
		    $vers = "<td><a href='?action=versions&table=maps&id=".$row["uid"]."'>Check other versions</a></td>";
		if ($row["user_id"] == user::uid())
		    if ($row["n_ver"] == 0)
			$vers .= "<td><a href='?action=new_version&id=".$row["uid"]."'>Upload new version</a></td>";
		$map_version_content = "<table><tr><td>Rev: ".ltrim($row["tag"], "r")."</td>".$vers."</tr></table>";
		
		//screenshots
		$query = "SELECT * FROM screenshot_group WHERE table_name = 'maps' AND table_id = ".$row["uid"]." AND user_id = ".$row["user_id"];
		$res_sc = db::executeQuery($query);
		$data = array();
		while ($row_sc = db::nextRowFromQuery($res_sc))
		{
		    array_push($data,"<a href='".$row_sc["image_path"]."' target=_blank><img style='max-width:150px;' src='".$row_sc["image_path"]."'></a>");
		}
		if (count($data) > 0)
		{
		    $screenshots = "<table><tr>";
		    $i_sc = 0;
		    foreach ($data as $value)
		    {
			$i_sc++;
			if ($i_sc == 3)
			    $screenshots .= "</tr><tr>";
			$screenshots .= "<td>".$value."</td>";
		    }
		    $screenshots .= "</tr></table>";
		}
	    }
	    else if($table == "units")
	    {
		if (isset($_GET["edit_unit_type"]) and user::uid() == $row["user_id"])
		{
		    $content .= "<tr><td><form method=POST><select name='edit_unit_type' id='edit_unit_type'>
				<option value='structure' ".misc::option_selected("structure",$row["type"]).">Structure</option>
				<option value='infantry' ".misc::option_selected("infantry",$row["type"]).">Infantry</option>
				<option value='vehicle' ".misc::option_selected("vehicle",$row["type"]).">Vehicle</option>
				<option value='air-borne' ".misc::option_selected("air-borne",$row["type"]).">Air-borne</option>
				<option value='nature' ".misc::option_selected("nature",$row["type"]).">Nature</option>
				<option value='other' ".misc::option_selected("other",$row["type"]).">Other</option>
				<input type='hidden' name='unit_id' value='".$row["uid"]."'>
				<input type='hidden' name='user_id' value='".$row["user_id"]."'>
				<input type='submit' value='submit'></form>
				</form></td></tr>
		    ";
		}
		else
		{
		    $edit_type = "";
		    if (user::uid() == $row["user_id"])
			$edit_type = "<a style='float:right;' href='?p=detail&table=units&id=".$row["uid"]."&edit_unit_type'>edit</a>";
		    $content .= "<tr><td>Type: ".$row["type"].$edit_type."</td></tr>";
		}
		$content .= $description;
	     	$content .= "<tr><td><br>Files (click to download):";
	     	$directory = "users/".$user_name."/units/".$title_origin."/";
		$shapes = glob($directory . "*.*");
		$content .= "<table>";
		foreach($shapes as $shape)
		{
		    if (basename($shape) != "preview.gif")
			$content .= "<tr><td><a href='".$shape."'>".basename($shape)."</a></td></tr>";
		}
		$content .= "</table></td></tr>";
		$content .= "<tr><td>This page is viewed ".$viewed." times</td></tr>";
		if (user::uid() == $row["user_id"])
		    $content .= "<tr><td><a href='?action=manage_screenshots&table=units&id=".$row["uid"]."'>Manage screenshots</a></td></tr>";
		//screenshots
		$query = "SELECT * FROM screenshot_group WHERE table_name = 'units' AND table_id = ".$row["uid"]." AND user_id = ".$row["user_id"];
		$res_sc = db::executeQuery($query);
		$data = array();
		while ($row_sc = db::nextRowFromQuery($res_sc))
		{
		    array_push($data,"<a href='".$row_sc["image_path"]."' target=_blank><img style='max-width:150px;' src='".$row_sc["image_path"]."'></a>");
		}
		if (count($data) > 0)
		{
		    $screenshots = "<table><tr>";
		    $i_sc = 0;
		    foreach ($data as $value)
		    {
			$i_sc++;
			if ($i_sc == 3)
			    $screenshots .= "</tr><tr>";
			$screenshots .= "<td>".$value."</td>";
		    }
		    $screenshots .= "</tr></table>";
		}
	    }
	    else if ($table == "replays")
	    {
		$content .= $description;
		$content .= "<tr><td>Version: " . $row["version"] . "</td></tr>";
		$content .= "<tr><td>Mods: " . $row["mods"] . "</td></tr>";
		$content .= "<tr><td>Server name: " . $row["server_name"] . "</td></tr>";
		
		$query = "SELECT * FROM maps WHERE maphash = '".$row["maphash"]."' GROUP BY maphash";
		$result = db::executeQuery($query);
		while ($inner_row = db::nextRowFromQuery($result))
		{
		    $content .= "<tr><td>Played on map: <a href='?p=detail&table=maps&id=".$inner_row["uid"]."'>" . misc::item_title_by_uid($inner_row["uid"], "maps") . "</a>";
		    $content .= "<br /><br /><center><a href='?p=detail&table=maps&id=".$inner_row["uid"]."'><img src='".misc::minimap($inner_row["path"])."'></a></center></td></tr>";
		}
		$query = "SELECT * FROM replay_players WHERE id_replays = ".$row["uid"]." ORDER BY team";
		$result = db::executeQuery($query);
		
		if (db::num_rows($result) != 0)
		    $content .= "<tr><td><table align='center'>";
		$i = 0;
		$team = -1;
		while ($inner_row = db::nextRowFromQuery($result))
		{
		    if (($team != $inner_row["team"] and $i != 0) or ($team == 0 and $i != 0))
			$content .= "</table><table align='center'><tr><td> vs </td></tr></table><table align='center'>";
		    $content .= "<tr><td><img style='border: 0px solid #261b15; padding: 0px;' src='images/flag-".$inner_row["country"].".png'> ".$inner_row["name"]."</td></tr>";
		    $team = $inner_row["team"];
		    $i++;
		}
		
		if (db::num_rows($result) != 0)
		    $content .= "</table></td></tr>";
		$content .= "<tr><td>This page is viewed ".$viewed." times</td></tr>";
		$content .= "<tr><td><a href='".$row["path"]."'>Download</a></tr></td>";
	    }
	     
	    if ($delete != "")
		$content .= "<tr><td>".$delete."</td></tr>";
	    elseif ($reported != "")
		$content .= "<tr><td>".$reported."</td></tr>";
	    
	     
	    $content .= "</table>";
	    $content .= $map_version_content;
	    $content .= $screenshots;
	}
	return $content;
    }

    public static function displayEvents($result)
    {
	$data = array();
	array_push($data, "Latest activity of users you follow:");
	while ($row = db::nextRowFromQuery($result))
	{
	    $name = "<a href='?profile=".$row["user_id"]."'>".user::login_by_uid($row["user_id"])."</a>";
	    $type = $row["type"];
	    switch($type)
	    {
		case "add":
		    if ($row["table_name"] == "screenshot")
		    {
			$desc = " added new screenshot which no longer exists";
			$q = "SELECT * FROM screenshot_group WHERE uid = ".$row["table_id"];
			$res = db::executeQuery($q);
			if (db::num_rows($res) == 1)
			{
			    $row_sc = db::nextRowFromQuery($res);
			    $desc = " added new <a href='".$row_sc["image_path"]."' target=_blank>screenshot</a> for <a href='?p=detail&table=".$row_sc["table_name"]."&id=".$row_sc["table_id"]."'>".rtrim($row_sc["table_name"],"s")."</a>";
			}
		    }
		    else
		    {
			$desc = " added new <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".rtrim($row["table_name"],'s')."</a>";
			if (!misc::item_exists($row["table_id"], $row["table_name"]))
			    $desc = " added new ".rtrim($row["table_name"],'s')." which no longer exists";
		    }
		    break;
		case "delete_comment":
		    $desc = " deleted comment on <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".rtrim($row["table_name"],'s')."</a>";
		    if (!misc::item_exists($row["table_id"], $row["table_name"]))
			$desc = " deleted comment on ".rtrim($row["table_name"],'s')." which no longer exists";
		    break;
		case "report":
		    $desc = " reported <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".rtrim($row["table_name"],'s')."</a>";
		    if (!misc::item_exists($row["table_id"], $row["table_name"]))
			$desc = " reported ".rtrim($row["table_name"],'s')." which no longer exists";
		    break;
		case "fav":
		    $desc = " favorited <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".rtrim($row["table_name"],'s')."</a>";
		    if (!misc::item_exists($row["table_id"], $row["table_name"]))
			$desc = " favorited ".rtrim($row["table_name"],'s')." which no longer exists";
		    break;
		case "unfav":
		    $desc = " unfavorited <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".rtrim($row["table_name"],'s')."</a>";
		    if (!misc::item_exists($row["table_id"], $row["table_name"]))
			$desc = " unfavorited ".rtrim($row["table_name"],'s')." which no longer exists";
		    break;
		case "comment":
		    $desc = " commented <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".rtrim($row["table_name"],'s')."</a>";
		    if (!misc::item_exists($row["table_id"], $row["table_name"]))
			$desc = " commented".rtrim($row["table_name"],'s')." which no longer exists";
		    break;
		case "login":
		    $desc = " logged in";
		    break;
		case "logout":
		    $desc = " logged out";
		    break;
		case "edit":
		    $desc = " edited <a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".rtrim($row["table_name"],'s')."</a>";
		    if (!misc::item_exists($row["table_id"], $row["table_name"]))
			$desc = " edited ".rtrim($row["table_name"],'s')." which no longer exists";
		    break;
		case "follow":
		    $desc = " started to follow <a href='?&profile=".$row["table_id"]."'>".user::login_by_uid($row["table_id"])."</a>";
		    break;
		case "unfollow":
		    $desc = " stopped following <a href='?&profile=".$row["table_id"]."'>".user::login_by_uid($row["table_id"])."</a>";
		    break;
		case "delete_item":
		    $desc = false;
		    break;
	    }
	    if ($desc)
		array_push($data, $name . $desc . " at " . $row["posted"]);
	}
	return content::create_dynamic_list($data, 1, "event_log",  11, true, true);
    }

    public static function create_comment_section($result)
    {
	$counter = 0;
	$content = "";
	$comment_page_id = 0;

	$content .= "<a name='comments'></a>";
	$pointer = "#comments";
	$table = "";
	$maxItemsPerPage = 15;
	if(isset($_GET["current_comment_page_".$table]))
		$current = $_GET["current_comment_page_".$table];
	else
		$current = 1;

	$comments = db::num_rows($result);
	$content .= "<h3 id='comments'>" . $comments . " Responses</h3>";
	$content .= "<ol class='commentlist'>";
	$i = 0;
	while ($comment = db::nextRowFromQuery($result))
	{
	    if( !($i >= ($current-1) * $maxItemsPerPage && $i < $current * $maxItemsPerPage ) )
	    {
		$i++;
		continue;
	    }
	    $i++;

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
		
	    $content .= "<a name=".$comment_page_id."></a>";
	    if (misc::comment_owner($comment["user_id"]))
	    {
		$content .= "<div style='position:absolute; margin: -14px 0px 0px 541px; border: 0px solid #2C1F18;color:#ff0000;'><a href='?delete_comment=".$comment["uid"]."&user_comment=".user::uid()."&table_name=".$comment["table_name"]."&table_id=".$comment["table_id"]."' onClick='return confirmDelete(\" delete comment\")' title='Delete'><img src='images/delete.png' style='border: 0px solid #261b15; padding: 0px; max-width:50%;' border='0' alt='delete' /></a></div>";
	    }
	    $content .= "<div class='comment-info'>";
	    $content .= "<a href='?profile=".$comment["user_id"]."'><img alt='' src='" . $avatarImg . "' style='margin-top:10px; max-width:50' /></a>";
	    $content .= "<cite>";
	    $content .= "<a href='?profile=".$comment["user_id"]."'>" . $author["login"] . "</a> Says: <br />";
	    $content .= "<span class='comment-data'><a href='#".$comment_page_id."' title=''>" . $comment["posted"] . "</a></span>";
	    $content .= "</cite>";
	    $content .= "</div>";

	    $content .= "<div class='comment-text'>";
	    $content .= "<p>" . stripslashes(stripslashes(str_replace('\r\n', "<br />", strip_tags($comment["content"])))) . "</p>";
	    $content .= "<div class='reply'>";
	    //$content .= "<a rel='nofollow' class='comment-reply-link' href='index.html'>Reply</a>"; // << need correct page
	    $content .= "</div>";
	    $content .= "</div>";

	    $content .= "</li>";
	}
	$content .= "</ol>";
	
	if ($comments != 0)
	{
	    $nrOfPages = floor(($comments-0.01) / $maxItemsPerPage) + 1;
	    $gets = "";
	    $pages = "<table><tr><td>";
	    $keys = array_keys($_GET);
	    foreach($keys as $key)
		if($key != "current_comment_page_".$table)
		    $gets .= "&" . $key . "=" . $_GET[$key];
	    for($i = 1; $i < $nrOfPages+1; $i++)
	    {
		$pages .= misc::paging($nrOfPages, $i, $current, $gets, $table, "comment", $pointer);
	    }
	    $pages .= "</td></tr></table>";
	    
	    if ($nrOfPages == 1)
		$pages = "";
	    
	    $content .= "</table>";
	    $pages = preg_replace("/(\.\.\.)+/", " ... ", $pages);
	    $content .= $pages;
	}
	return $content;
    }

    public static function create_comment_respond($table_name,$table_id)
    {
	$content = "";
	if(user::online())
	{
	    $content .= "<div id='respond'>";
	    $content .= "<h3>Leave a Reply</h3>";			
	    $content .= "<form action='?p=detail&table=".$table_name."&id=".$table_id."' method='post' id='commentform'>";
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
    
    public static function create_dynamic_list($data, $columns, $name = "dyn", $maxItemsPerPage = 10, $header = false, $use_pages = true, $width="")
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
		$content .= "<a name='".$modifiedName."'></a><table style='".$width."'>";
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
		    if ($current == 2 and $i == $start)
			continue;
		    $content .= "<tr>";
		    for($row = 0; $row < $columns; $row++)
		    {
			$content .= "<td>" . $data[$i+$row] . "</td>";
		    }
		    $content .= "</tr>";
		}
		$nrOfPages = floor(($total-0.01) / $maxItemsPerPage) + 1;
		$content .= "</table>";
		$gets = "";
		$pages = "<table><tr><td>";
		$keys = array_keys($_GET);
		foreach($keys as $key)
		{
		    if($key != "current_dynamic_page_".$modifiedName)
			$gets .= "&" . $key . "=" . $_GET[$key];
		}
		for($i = 1; $i < $nrOfPages+1; $i++)
		{
		    $pages .= misc::paging($nrOfPages, $i, $current, $gets, $modifiedName, "dynamic", $pointer);
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
	$content .= '<div style="float:left;"><img src="favicon.ico" style="position:absolute;width:16px;padding:0;margin:0;border:0;"/><strong><a style="padding-left:20px;" href="http://open-ra.org" target="_blank">OpenRA Official Website <img src="images/new_tab_n.gif" style="padding:0;margin:0;border:0;" /></a></strong> |<a href="http://logs.open-ra.org" target="_blank">IRC logs/stats <img src="images/new_tab_n.gif" style="padding:0;margin:0;border:0;" /></a> |<a href="http://logs.open-ra.org/mapstats/index.html" target="_blank">Map stats <img src="images/new_tab_n.gif" style="padding:0;margin:0;border:0;" /></a> |<a href="http://res0l.net/src/LiveORA/map.html" target="_blank">Live Players Map <img src="images/new_tab_n.gif" style="padding:0;margin:0;border:0;" /></a> |<a href="https://github.com/Holloweye/OpenRA-Content-Website/issues" target="_blank">This site\'s issue tracker <img src="images/new_tab_n.gif" style="padding:0;margin:0;border:0;" /></a></div>';
	$content .= '<strong><a href="?p=members">Members</a></strong> |';
	$content .= '<a href="/">Home</a> |';
	$content .= '<strong><a href="#top" class="back-to-top">Back to Top</a></strong>';

	$content .= '</div><br />';
	$content .= '<!-- Start of StatCounter Code for Default Guide -->
		    <script type="text/javascript">
		    var sc_project=7756346; 
		    var sc_invisible=1; 
		    var sc_security="ba45e507"; 
		    </script>
		    <script type="text/javascript"
		    src="http://www.statcounter.com/counter/counter.js"></script>
		    <noscript><div class="statcounter"><a title="tumblr
		    visitor" href="http://statcounter.com/tumblr/"
		    target="_blank"><img class="statcounter"
		    src="http://c.statcounter.com/7756346/0/ba45e507/1/"
		    alt="tumblr visitor"></a></div></noscript>
		    <!-- End of StatCounter Code for Default Guide -->
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
	elseif ($page == "replays")
	{
	    objects::replays();
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
	    $result = db::executeQuery("SELECT * FROM users ORDER BY register_date ASC");
	    if (db::num_rows($result) > 0)
	    {
		echo "<div class='sidemenu'><ul><li>Members (".db::num_rows($result)."):</li></ul></div>";
	    	$data = array();
		array_push($data,"#","Username","Joined","Message");
		while ($row = db::nextRowFromQuery($result))
		{
		    $avatar = misc::avatar($row["uid"]);
		    array_push($data,"<a href='?profile=".$row["uid"]."'><img src='".$avatar."' style='max-width:50px;'></a>","<a href='?profile=".$row["uid"]."'>".$row["login"]."</a>", date("D M j, Y g:i a", mail::convert_timestamp($row["register_date"])), "<a href='?p=mail&m=compose&to=".$row["uid"]."'>Send a PM</a>");
		}
		echo content::create_dynamic_list($data,4,"members",10,true,true);
	    }
	}
	elseif ($page == "mail")
	{
	    mail::mbox();
	}
    }
    
    public static function action($request)
    {
	if ($request == "mymaps")
	{
	    if (!user::online())
		return;
	    profile::upload_map();
	    echo "<br /><br /><div class='sidemenu'><ul><li>Your maps:</li></ul></div>";
	    list($order_by, $request_mod, $type, $request_tileset, $my_items) = content::map_filters("no_show_my_content_filter");
	    
	    $my = "";
	
	    $field_lc = "";
	    $ljoin_lc = "";
	    if ($order_by == "lately_commented")
	    {
		$order_by = "comment_posted DESC";
		$field_lc = ", c.posted AS comment_posted";
		$ljoin_lc = "LEFT JOIN comments AS c on c.table_id = m.uid";
	    }
	    if ($my_items == true)
		$my = " AND m.user_id = ".user::uid()." ";
	    $query = "SELECT m.*".$field_lc." FROM maps AS m ".$ljoin_lc." WHERE m.g_mod LIKE ('%".$request_mod."%') AND upper(m.type) LIKE upper('%".$type."%') AND m.tileset LIKE ('%".$request_tileset."%') ".$my."GROUP BY m.maphash ORDER BY ".$order_by;
	    
	    $result = db::executeQuery($query);
	    $output = content::create_grid($result);
	    if ($output == "")
	    {
		echo "<table><tr><th>No maps uploaded yet</th></tr></table>";
	    }
	    echo "<br />" . $output;
	}
	if ($request == "myguides")
	{
	    if (!user::online())
		return;
	    profile::upload_guide();
	    echo "<br /><br /><div class='sidemenu'><ul><li>Your guides:</li></ul></div>";
	    $result = db::executeQuery( "SELECT * FROM guides WHERE user_id = ".user::uid()." ORDER BY posted DESC" );
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
	    echo "<br /><br /><div class='sidemenu'><ul><li>Your units:</li></ul></div>";
	    $result = db::executeQuery("SELECT * FROM units WHERE user_id = ".user::uid()." ORDER BY posted DESC");
	    $output = content::create_grid($result, "units");
	    if ($output == "")
	    {
		echo "<table><tr><th>No units uploaded yet</th></tr></table>";
	    }
	    echo $output;
	}
	if ($request == "myreplays")
	{
	    if (!user::online())
		return;
	    profile::upload_replay();
	    echo "<br /><br /><div class='sidemenu'><ul><li>Your replays:</li></ul></div>";
	    
	    list($order_by, $my_items, $version) = content::replay_filters("no_show_my_content_filter");
	    
	    $my = "";
	
	    $field_lc = "";
	    $ljoin_lc = "";
	    if ($order_by == "lately_commented")
	    {
		$order_by = "comment_posted DESC";
		$field_lc = ", c.posted AS comment_posted";
		$ljoin_lc = "LEFT JOIN comments AS c on c.table_id = r.uid";
	    }
	    if ($my_items == true)
		$my = " AND r.user_id = ".user::uid()." ";
	    $query = "SELECT r.*".$field_lc." FROM replays AS r ".$ljoin_lc." WHERE version LIKE ('%".$version."%') ".$my." ORDER BY ".$order_by;
	    
	    $result = db::executeQuery($query);
	    $output = content::create_grid($result,"replays",0,3,4);
	    if ($output == "")
	    {
		echo "<table><tr><th>No replays uploaded yet</th></tr></table>";
	    }
	    echo "<br />" . $output;
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
		    array_push($data,"","This people like <u>".$faction."</u> faction:");
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
		    array_push($data,"No one likes <u>".$faction."</u> faction");
		    echo content::create_dynamic_list($data,1,"dyn",1,true,true);
		}
	    }
	}
	if ($request == "user_items")
	{
	    if (isset($_GET["table"]) and isset($_GET["id"]))
	    {
		$table = $_GET["table"];
		$id = $_GET["id"];
		$query = "SELECT * FROM ".$table." WHERE user_id = ".$id." ORDER BY posted DESC";
		$result = db::executeQuery($query);
		echo content::create_list($result, $table, 15, $id);
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
		    $name = "You";
		    $who = $name." follow:";
		}
		else
		{
		    $name = user::login_by_uid($id);
		    $who = "<a href='?profile=".$id."'>".$name."</a> follows:";
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
	//user followed by
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
		    $who = "<a href='?profile=".$id."'>".$name."</a> is followed by:";
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
	//manage screenshots
	if ($request == "manage_screenshots")
	{
	    if ( !(isset($_GET["table"]) and isset($_GET["id"])))
		return;
	    if (!user::online())
		return;
	    if (!misc::item_owner($_GET["id"], $_GET["table"], user::uid()))
		return;
	    $query = "SELECT * FROM screenshot_group WHERE table_id = ".$_GET["id"]." AND table_name = '".$_GET["table"]."' AND user_id = ".user::uid();
	    $result = db::executeQuery($query);
	    $can_upload = 4 - (int)db::num_rows($result);
	    if ($can_upload == 0)
		echo "<h4>You have reached your limit for this ".rtrim($_GET["table"],"s")."!</h4>";
	    else
		echo "<h4>You can upload ".(string)$can_upload." more screenshots for this ".rtrim($_GET["table"],"s")."!</h4>";
	    
	    $data = array();
	    while ($row = db::nextRowFromQuery($result))
	    {
		array_push($data,"<a href='".$row["image_path"]."' target=_blank><img style='max-width:150;border: 0px solid #261b15; padding: 0px;' src='".$row["image_path"]."'></a>");
		array_push($data,"<a href='?del_item=".$row["uid"]."&del_item_table=screenshot_group&del_item_user=".user::uid()."' onClick='return confirmDelete(\" delete this screenshot\")'>Delete</a>");
	    }
	    echo content::create_dynamic_list($data,2,"screenshots",5,false,false);
	    if (db::num_rows($result) < 4)
	    {
		echo "<form id='form_class' enctype='multipart/form-data' method='POST' action=''>
		    <label>Choose an image (jpeg,png,gif):<br /><br />
		    <span class='file-wrapper'>
			<input type='file' name='screenshot_upload' id='enhanced' />
			<span class='button'>Choose a file</span>
		    </span><br />
		    </label>
		    <br />
		    <input type='hidden' name='table_id' value='" . $_GET["id"] . "'>
		    <input type='hidden' name='table_name' value='" . $_GET["table"] . "'>
		    <input type='submit' name='submit' value='submit' />
		    </form>
		";
		upload::screenshot();
	    }
	}
    }
    
    public static function replay_filters($my_content="")
    {
	$my_items = false;
	$my_checked = "";
	$sort_by = "latest";
	$version = "";
	if (isset($_POST["apply_filter"]))
	{
	    $sort_by = $_POST["sort"];
	    if (isset($_POST["replay_my_items"]))
		$my_items = true;
	    if (isset($_POST["replay_version"]))
		$version = $_POST["replay_version"];
	}
	elseif (isset($_COOKIE["replay_sort_by"]))
	{
	    $sort_by = $_COOKIE["replay_sort_by"];
	    if (isset($_COOKIE["replay_my_items"]))
	    {
		$my_items = true;
		$my_checked = "checked";
	    }
	    if (isset($_COOKIE["replay_version"]))
		$version = $_COOKIE["replay_version"];
	}
	$checkbox = "";
	if ($my_content == "" and user::online())
	    $checkbox = "<input style='float:right; margin-top: 15px; margin-right: 15px;' type='checkbox' name='replay_my_items' ".$my_checked." title='only my content'><label style='float:right; margin-top: 12px; margin-right: 5px;'>only my content</label>";
	//filters
	echo "<form name='replay_filters' method=POST action=''><table style='width:560px;'><tr><th>sort by:</th><th>version contains:</th></tr><tr>";
	echo "<td>";
	echo "<select name='sort' id='sort'>";
	echo "<option value='latest' ".misc::option_selected("latest",$sort_by).">latest first</option>";
	echo "<option value='date' ".misc::option_selected("date",$sort_by).">oldest first</option>";
	echo "<option value='alpha' ".misc::option_selected("alpha",$sort_by).">title</option>";
	echo "<option value='alpha_reverse' ".misc::option_selected("alpha_reverse",$sort_by).">title in reverse order</option>";
	echo "<option value='lately_commented' ".misc::option_selected("lately_commented",$sort_by).">lately commented</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "<td><input type='text' name='replay_version' value='".$version."'></td>";
	echo "</tr></table><div style='width:578px;'><input style='float:right;' type='submit' name='apply_filter' value='Apply filters'>
	    ".$checkbox."
	    <input type='hidden' name='apply_filter_type' value='replay'>
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
	elseif ($sort_by == "lately_commented")
	    $order_by = $sort_by;
	
	if ($my_content != "")
	    $my_items = true;

	return array($order_by, $my_items, $version);
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
	    $checkbox = "<input style='float:right; margin-top: 15px; margin-right: 15px;' type='checkbox' name='".$arg."_my_items' ".$my_checked." title='only my content'><label style='float:right; margin-top: 12px; margin-right: 5px;'>only my content</label>";
	//filters
	echo "<form name='".$arg."_filters' method=POST action=''><table style='width:560px;'><tr><th>sort by:</th><th>type:</th></tr><tr>";
	echo "<td>";
	echo "<select name='sort' id='sort'>";
	echo "<option value='latest' ".misc::option_selected("latest",$sort_by).">latest first</option>";
	echo "<option value='date' ".misc::option_selected("date",$sort_by).">oldest first</option>";
	echo "<option value='alpha' ".misc::option_selected("alpha",$sort_by).">title</option>";
	echo "<option value='alpha_reverse' ".misc::option_selected("alpha_reverse",$sort_by).">title in reverse order</option>";
	echo "<option value='lately_commented' ".misc::option_selected("lately_commented",$sort_by).">lately commented</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "<td>";
	echo "<select name='type' id='type'>";
	if ($arg == "guide")
	{
	    echo "<option value='any_type' ".misc::option_selected("any_type",$type).">Any</option>";
	    echo "<option value='design' ".misc::option_selected("design",$type).">Design (2D/3D) (".misc::amount_of_items_option("guides", "WHERE guide_type = 'design'", $my_items).")</option>";
	    echo "<option value='mapping' ".misc::option_selected("mapping",$type).">Mapping (".misc::amount_of_items_option("guides", "WHERE guide_type = 'mapping'", $my_items).")</option>";
	    echo "<option value='modding' ".misc::option_selected("modding",$type).">Modding (".misc::amount_of_items_option("guides", "WHERE guide_type = 'modding'", $my_items).")</option>";
	    echo "<option value='coding' ".misc::option_selected("coding",$type).">Coding (".misc::amount_of_items_option("guides", "WHERE guide_type = 'coding'", $my_items).")</option>";
	    echo "<option value='other' ".misc::option_selected("other",$type).">Other (".misc::amount_of_items_option("guides", "WHERE guide_type = 'other'", $my_items).")</option>";
	}
	elseif ($arg == "unit")
	{	    
	    echo "<option value='any_type' ".misc::option_selected("any_type",$type).">Any</option>";
	    echo "<option value='structure' ".misc::option_selected("structure",$type).">Structure (".misc::amount_of_items_option("units", "WHERE type = 'structure'", $my_items).")</option>";
	    echo "<option value='infantry' ".misc::option_selected("infantry",$type).">Infantry (".misc::amount_of_items_option("units", "WHERE type = 'infantry'", $my_items).")</option>";
	    echo "<option value='vehicle' ".misc::option_selected("vehicle",$type).">Vehicle (".misc::amount_of_items_option("units", "WHERE type = 'vehicle'", $my_items).")</option>";
	    echo "<option value='air-borne' ".misc::option_selected("air-borne",$type).">Air-borne (".misc::amount_of_items_option("units", "WHERE type = 'air-borne'", $my_items).")</option>";
	    echo "<option value='nature' ".misc::option_selected("nature",$type).">Nature (".misc::amount_of_items_option("units", "WHERE type = 'nature'", $my_items).")</option>";
	    echo "<option value='other' ".misc::option_selected("other",$type).">Other (".misc::amount_of_items_option("units", "WHERE type = 'other'", $my_items).")</option>";
	}
	echo "</select><br />";
	echo "</td>";
	echo "</tr></table><div style='width:578px;'><input style='float:right;' type='submit' name='apply_filter' value='Apply filters'>
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
	elseif ($sort_by == "lately_commented")
	    $order_by = $sort_by;
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
	$type = "";
	if ($my_content != "")
	    $my_items = true;
	if (isset($_POST["apply_filter"]))
	{
	    $sort_by = $_POST["sort"];
	    $mod = $_POST["mod"];
	    $tileset = $_POST["tileset"];
	    $type = $_POST["type"];
	    if (isset($_POST["map_my_items"]))
		$my_items = true;
	}
	elseif (isset($_COOKIE["map_sort_by"]))
	{
	    $sort_by = $_COOKIE["map_sort_by"];
	    $mod = $_COOKIE["map_mod"];
	    $tileset = $_COOKIE["map_tileset"];
	    $type = $_COOKIE["map_type"];
	    if (isset($_COOKIE["map_my_items"]))
	    {
		$my_items = true;
		$my_checked = "checked";
	    }
	}
	
	$checkbox = "";
	if ($my_content == "" and user::online())
	    $checkbox = "<input style='float:right; margin-top: 15px; margin-right: 15px;' type='checkbox' name='map_my_items' ".$my_checked." title='only my content'><label style='float:right; margin-top: 12px; margin-right: 5px;'>only my content</label>";

	//filters
	echo "<form name='map_filters' method=POST action=''><table style='width:560px;'><tr><th>sort by:</th><th>type:</th><th>mod:</th><th>tileset:</th></tr><tr>";
	echo "<td>";
	echo "<select name='sort' id='sort'>";
	echo "<option value='latest' ".misc::option_selected("latest",$sort_by).">latest first</option>";
	echo "<option value='date' ".misc::option_selected("date",$sort_by).">oldest first</option>";
	echo "<option value='alpha' ".misc::option_selected("alpha",$sort_by).">title</option>";
	echo "<option value='alpha_reverse' ".misc::option_selected("alpha_reverse",$sort_by).">title in reverse order</option>";
	echo "<option value='lately_commented' ".misc::option_selected("lately_commented",$sort_by).">lately commented</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "<td>";
	echo "<select name='type' id='type'>";
	echo "<option value='any_type' ".misc::option_selected("any_type",$type).">Any</option>";
	echo "<option value='conquest' ".misc::option_selected("conquest",$type).">conquest (".misc::amount_of_items_option("maps", "WHERE UPPER(type) = UPPER('conquest') AND n_ver = 0", $my_items).")</option>";
	echo "<option value='koth' ".misc::option_selected("koth",$type).">koth (".misc::amount_of_items_option("maps", "WHERE UPPER(type) = UPPER('koth') AND n_ver = 0", $my_items).")</option>";
	echo "<option value='minigame' ".misc::option_selected("minigame",$type).">minigame (".misc::amount_of_items_option("maps", "WHERE UPPER(type) = UPPER('minigame') AND n_ver = 0", $my_items).")</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "<td>";
	echo "<select onChange='mod_tileset();' name='mod' id='mod'>";
	echo "<option value='any_mod' ".misc::option_selected("any_mod",$mod).">Any</option>";
	echo "<option value='ra' ".misc::option_selected("ra",$mod).">RA (".misc::amount_of_items_option("maps", "WHERE g_mod = 'ra' AND n_ver = 0", $my_items).")</option>";
	echo "<option value='cnc' ".misc::option_selected("cnc",$mod).">CNC (".misc::amount_of_items_option("maps", "WHERE g_mod = 'cnc' AND n_ver = 0", $my_items).")</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "<td>";
	echo "<select name='tileset' id='tileset'>";
	echo "<option value='any_tileset' ".misc::option_selected("any_tileset",$tileset).">Any</option>";
    	echo "</select><br />";
	echo "</td>";
	echo "</tr></table><div style='width:578px;'><input style='float:right;' type='submit' name='apply_filter' value='Apply filters'>
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
		    document.map_filters.tileset.options[0] = new Option('Any','any_tileset')
		}
		if (chosen_option.value == 'ra')
		{
		    document.map_filters.tileset.options.length=0
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('Any','any_tileset',false,".misc::option_selected_bool("any_tileset",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('temperat (".misc::amount_of_items_option("maps", "WHERE g_mod = 'ra' AND tileset = 'temperat' AND n_ver = 0", $my_items).")','temperat',false,".misc::option_selected_bool("temperat",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('snow (".misc::amount_of_items_option("maps", "WHERE g_mod = 'ra' AND tileset = 'snow' AND n_ver = 0", $my_items).")','snow',false,".misc::option_selected_bool("snow",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('interior (".misc::amount_of_items_option("maps", "WHERE g_mod = 'ra' AND tileset = 'interior' AND n_ver = 0", $my_items).")','interior',false,".misc::option_selected_bool("interior",$tileset).")
		}
		if (chosen_option.value == 'cnc')
		{
		    document.map_filters.tileset.options.length=0
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('Any','any_tileset',false,".misc::option_selected_bool("any_tileset",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('temperat (".misc::amount_of_items_option("maps", "WHERE g_mod = 'cnc' AND tileset = 'temperat' AND n_ver = 0", $my_items).")','temperat',false,".misc::option_selected_bool("temperat",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('desert (".misc::amount_of_items_option("maps", "WHERE g_mod = 'cnc' AND tileset = 'desert' AND n_ver = 0", $my_items).")','desert',false,".misc::option_selected_bool("desert",$tileset).")
		    document.map_filters.tileset.options[document.map_filters.tileset.options.length] = new Option('winter (".misc::amount_of_items_option("maps", "WHERE g_mod = 'cnc' AND tileset = 'winter' AND n_ver = 0", $my_items).")','winter',false,".misc::option_selected_bool("winter",$tileset).")
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
	elseif ($sort_by == "lately_commented")
	    $order_by = $sort_by;
	//mod
	if ($mod == "any_mod")
	    $request_mod = "";
	else
	    $request_mod = $mod;
	//type
	if ($type == "any_type")
	    $type = "";
	//tileset
	if ($tileset == "any_tileset")
	    $request_tileset = "";
	else
	    $request_tileset = $tileset;

	return array($order_by, $request_mod, $type, $request_tileset, $my_items);
    }
}

class objects
{
    public static function maps()
    {
	echo "<h3>Maps!</h3>";
	list($order_by, $request_mod, $type, $request_tileset, $my_items) = content::map_filters();
	$my = "";
	$n_ver_e = "AND m.n_ver = 0";
	
	$field_lc = "";
	$ljoin_lc = "";
	if ($order_by == "lately_commented")
	{
	    $order_by = "comment_posted DESC";
	    $field_lc = ", c.posted AS comment_posted";
	    $ljoin_lc = "LEFT JOIN comments AS c on c.table_id = m.uid";
	    $n_ver_e = "";
	}
	if ($my_items == true)
	    $my = " AND m.user_id = ".user::uid()." ";
	$query = "SELECT m.*".$field_lc." FROM maps AS m ".$ljoin_lc." WHERE m.g_mod LIKE ('%".$request_mod."%') AND upper(m.type) LIKE upper('%".$type."%') AND m.tileset LIKE ('%".$request_tileset."%') ".$n_ver_e." ".$my."GROUP BY m.maphash ORDER BY ".$order_by;
	
	$result = db::executeQuery($query);
	echo "<br />".content::create_grid($result);
    }
    
    public static function units()
    {
	echo "<h3>Units!</h3>";
	list($order_by, $request_type, $my_items) = content::guide_unit_filters("unit");
	$my = "";
	
	$field_lc = "";
	$ljoin_lc = "";
	if ($order_by == "lately_commented")
	{
	    $order_by = "comment_posted DESC";
	    $field_lc = ", c.posted AS comment_posted";
	    $ljoin_lc = "LEFT JOIN comments AS c on c.table_id = u.uid";
	}
	if ($my_items == true)
	    $my = " AND u.user_id = ".user::uid()." ";
	$result = db::executeQuery("SELECT u.*".$field_lc." FROM units AS u ".$ljoin_lc." WHERE u.type LIKE ('%".$request_type."%') ".$my."ORDER BY ".$order_by);
	echo content::create_grid($result,"units");
    }
    
    public static function guides()
    {
	echo "<h3>Guides!</h3>";
	list($order_by, $request_type, $my_items) = content::guide_unit_filters("guide");
	$my = "";
	
	$field_lc = "";
	$ljoin_lc = "";
	if ($order_by == "lately_commented")
	{
	    $order_by = "comment_posted DESC";
	    $field_lc = ", c.posted AS comment_posted";
	    $ljoin_lc = "LEFT JOIN comments AS c on c.table_id = g.uid";
	}
	if ($my_items == true)
	    $my = " AND g.user_id = ".user::uid()." ";
	$result = db::executeQuery("SELECT g.*".$field_lc." FROM guides AS g ".$ljoin_lc." WHERE g.guide_type LIKE ('%".$request_type."%') ".$my."ORDER BY ".$order_by);
	echo content::create_grid($result,"guides");
    }
    
    public static function replays()
    {
	echo "<h3>Replays!</h3>";
	
	list($order_by, $my_items, $version) = content::replay_filters();
	    
	$my = "";

	$field_lc = "";
	$ljoin_lc = "";
	if ($order_by == "lately_commented")
	{
	    $order_by = "comment_posted DESC";
	    $field_lc = ", c.posted AS comment_posted";
	    $ljoin_lc = "LEFT JOIN comments AS c on c.table_id = r.uid";
	}
	if ($my_items == true)
	    $my = " AND r.user_id = ".user::uid()." ";
	$query = "SELECT r.*".$field_lc." FROM replays AS r ".$ljoin_lc." WHERE version LIKE ('%".$version."%') ".$my." ORDER BY ".$order_by;
	
	$result = db::executeQuery($query);
	echo content::create_grid($result,"replays",0,3,4);
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
		    
		    echo "<form id='form_class' enctype='multipart/form-data' method='POST' action=''>
			    <label>Upload guide:</label>
			    <br />
			    <label>Title: <input id='id_guide_title' type='text' value='".str_replace("\\\\\\", "", $row["title"])."' name='edit_guide_title' onkeyup='updateContent(\"id_display_title\",\"id_guide_title\");' onchange='updateContent(\"id_display_title\",\"id_guide_title\");' onkeypress='updateContent(\"id_display_title\",\"id_guide_title\");' /></label>
			    <br />
			    <label>Text: <textarea id='id_guide_text' name='edit_guide_text' cols='40' rows='5' onkeyup='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");' onchange='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");' onkeypress='updateContent(\"id_display_text\",\"id_guide_text\",\"<table><tr><td><th></th><img><a><b><i><u><p><br><ul><li><ol><dl><dd><dt>\");'>".str_replace("\\\\\\", "",   str_replace("<br />", "\r\n", str_replace('\r\n', "", $row["html_content"])))."</textarea></label>
			    <br />
			    <select name='edit_guide_type'>";
		    echo "<option value='other' ".misc::option_selected("other", $row["guide_type"]).">Other</option>";
		    echo "<option value='design' ".misc::option_selected("design", $row["guide_type"]).">Design (2D/3D)</option>";
		    echo "<option value='mapping' ".misc::option_selected("mapping", $row["guide_type"]).">Mapping</option>";
		    echo "<option value='modding' ".misc::option_selected("modding", $row["guide_type"]).">Modding</option>";
		    echo "<option value='coding' ".misc::option_selected("coding", $row["guide_type"]).">Coding</option>";

		    echo "</select>
			    <br />
			    <input type='hidden' name='edit_guide_uid' value='".$row["uid"]."' />
			    <input type='submit' name='submit' value='Edit' />
			    </form>
		    ";
		}
	    }
	}
    }
    
    public static function detail()
    {
	if(!in_array($_GET['table'], array("maps","units","guides","replays","articles")))
	    return;
	$result = db::executeQuery("SELECT * FROM " . $_GET['table'] . " WHERE uid = " . $_GET['id'] . "");
	while (db::nextRowFromQuery($result))
	{
	    $result = db::executeQuery("SELECT * FROM " . $_GET['table'] . " WHERE uid = " . $_GET['id'] . "");
	    echo content::displayItem($result, $_GET['table']);

	    $result = db::executeQuery("SELECT * FROM comments WHERE table_name = '" . $_GET['table'] . "' AND table_id = '" . $_GET['id'] . "' ORDER by posted DESC");
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
		    array_push($data,"<a href='?profile=".$row["uid"]."'>".$row["login"]."</a>");
		$content .= content::create_dynamic_list($data,1,"users");
	    }
	    $result = db::executeQuery("SELECT * FROM comments WHERE content LIKE '%".$search."%'");
	    if (db::num_rows($result) > 0)
	    {
		$content .= "<br><label>comments found:</label>";
		$data = array();
		while ($row = db::nextRowFromQuery($result))
		    array_push($data,"<a href='/?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".rtrim($row["table_name"],"s")."</a> is commented by <a href='/?profile=".$row["user_id"]."'>".user::login_by_uid($row["user_id"])."</a>", str_replace("\\\\\\", "", str_replace("\\r\\n", "", $row["content"])));
		$content .= content::create_dynamic_list($data,2,"comments");
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

?>
