<?PHP

class profile
{
    public static function ifFollow($profile)
    {
	//-1	do not show
	//0	follow
	//1	unfollow
	if ($profile != user::uid())
	{
	    $query = "SELECT * FROM following WHERE who = :1 AND whom = :2";
	    $result = db::executeQuery($query, array(user::uid(), $profile));
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
	    $to_head = "Your avatar:";
	}
	else
	{
	    $to_head = $profile."'s avatar:";
	}
	array_push($data,$to_head);
	array_push($data,"<img style='display: block;   margin-left: auto;   margin-right: auto;' src='".misc::avatar($id)."'>");
	
	//pm
	if ($id != user::uid() and user::online())
	    array_push($data,"<a href='?p=mail&m=compose&to=".$id."'>Send a PM</a>");
	
	if (profile::ifFollow($id)==0)
	{
	    array_push($data,"<a href='?follow=".$id."'>Follow user</a>");
	}
	elseif (profile::ifFollow($id)==1)
	{
	    array_push($data,"<a href='?unfollow=".$id."'>Unfollow user</a>");
	}
	echo "<div style='margin-left:-11px;'>" . content::create_dynamic_list($data,1,"dyn",3,true,false,$width="width:273px;") . "</div>";
	$query = "SELECT
		    who,
		    whom,
		    'following' AS table_name
		  FROM following WHERE who = :1";
	$result = db::executeQuery($query, array($id));
	if (db::num_rows($result) > 0)
	{
	    if ( $profile == "You" )
	    {
		$to_head = "You follow <u>".db::num_rows($result)."</u> people:";
	    }
	    else
	    {
		$to_head = $profile." follows <u>".db::num_rows($result)."</u> people:";
	    }
	    echo "<ul>
		      <li>
			 ".$to_head;
	    echo "<p style='margin-left:-15px;' class='thumbs'>";
	    echo content::createImageGallery($result,"follow");
	    echo "</p>";
	    echo "</li>
		  </ul><br />";
	}

	// followed by is not shown for non logged in users
	$query = "SELECT
		    who,
		    whom,
		    'following' AS table_name
		  FROM following WHERE whom = :1";
	$result = db::executeQuery($query, array($id));
	if (db::num_rows($result) > 0)
	{
	    if ( $profile == "You" )
	    {
		$to_head = "You are followed by <u>".db::num_rows($result)."</u> people:";
	    }
	    else
	    {
		$to_head = $profile." is followed by <u>".db::num_rows($result)."</u> people:";
	    }
	    echo "<ul>
		  <li>
		      ".$to_head;
	    echo "<p style='margin-left:-15px;' class='thumbs'>";
	    echo content::createImageGallery($result,"followed");
	    echo "</p>";
	    echo "</li>
	      </ul><br />";
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
    	
    	$query = "SELECT * FROM users WHERE uid = :1";
    	$result = db::executeQuery($query, array($self));
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
		db::executeQuery("UPDATE users SET occupation = :1 WHERE uid = :2", array($_POST["occupation"], user::uid()));
		$didUpdate = true;
	    }
	    if(isset($_POST["real_name"])) {
		db::executeQuery("UPDATE users SET real_name = :1 WHERE uid = :2", array($_POST["real_name"], user::uid()));
		$didUpdate = true;
	    }
	    if(isset($_POST["gender"])) {
		db::executeQuery("UPDATE users SET gender = :1 WHERE uid = :2", array($_POST["gender"], user::uid()));
		$didUpdate = true;
	    }
	    if(isset($_POST["fav_faction"])) {
		db::executeQuery("UPDATE users SET fav_faction = :1 WHERE uid = :2", array($_POST["fav_faction"], user::uid()));
		$didUpdate = true;
	    }
	    if(isset($_POST["interests"])) {
		db::executeQuery("UPDATE users SET interests = :1 WHERE uid = :2", array($_POST["interests"], user::uid()));
		$didUpdate = true;
	    }
	    if(isset($_POST["country"])) {
		db::executeQuery("UPDATE users SET country = :1 WHERE uid = :2", array($_POST["country"], user::uid()));
		$didUpdate = true;
	    }

	    if($didUpdate)
		echo "<u>profile updated!</u><br />";
	    $query = "SELECT * FROM users WHERE uid = :1";
	    $result = db::executeQuery($query, array(user::uid()));
	    $usr = db::nextRowFromQuery($result);

	    echo "<table><tr><td><form action='?profile=".user::uid()."&edit=on' method='post' enctype=\"multipart/form-data\" id='commentform'>";
	    echo "<p>";
	    echo "<label>Change avatar</label><br /><br />";
	    echo "<span class='file-wrapper'>
		    <input type='file' name='avatar_upload' id='enhanced' />
		    <span class='button'>Choose a file</span>
		</span><br />";
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
	    if (user::online() and $usr["uid"] == user::uid())
		$whos = "Your";
	    else
		$whos = $usr["login"]."'s";
	    //Display common info
	    echo "<table>";
	    echo "<tr><td><h1>".$whos." profile</h1></td>";
	    $img = "";
	    if($usr["country"] != "None" && $usr["country"] != "")
		$img = "<img style='float:center;border: 0px solid #261b15; padding: 0px;' src='images/country_flags/".$usr["country"]."'>";
	    if(user::uid() == $usr["uid"] && user::online())
		echo "<td><a href='?profile=".user::uid()."&edit=on'><h2>edit</h2></a>".$img."</td>";
	    else
		echo "<td style='padding: .0em 0em;'><center>".$img."</center></td>";
	    echo "</tr>";
	    echo "<tr><td>Gender</td><td>".$gender."</td></tr>";
	    echo "<tr><td>Occupation</td><td>".str_replace("\\\\\\","",$usr["occupation"])."</td></tr>";
	    echo "<tr><td>Interests</td><td>".str_replace("\\\\\\","",$usr["interests"])."</td></tr>";
	    echo "<tr><td>Real name</td><td>".str_replace("\\\\\\","",$usr["real_name"])."</td></tr>";
	    echo "<tr><td>Favorite faction</td><td><a href='?action=display_faction&faction=".$usr["fav_faction"]."'><img style='border: 0px solid #261b15; padding: 0px;' src='images/flag-".$usr["fav_faction"].".png'></a></td></tr>";
    		
	    $query = "SELECT * FROM country WHERE name = :1";
	    $result = db::executeQuery($query, array($usr["country"]));
	    if($country = db::nextRowFromQuery($result))
		echo "<tr><td>Country</td><td>".$country["title"]."</td></tr>";
	    else
		echo "<tr><td>Country</td><td>None</td></tr>";

	    $x = $usr["experience"];
	    $level = floor((25 + sqrt(625 + 100 * $x)) / 50);
	    $nextLevel = $level+1;
	    $expNeeded = 25 * $nextLevel * $nextLevel - 25 * $nextLevel;
	    echo "<tr><td>Level</td><td>".$level."</td></tr>";
	    echo "<tr><td>Experience left to ".$nextLevel."</td><td>".($expNeeded - $x)."</td></tr>";
	    echo "</table>";

	    //Display latest favorited items
	    
	    $result = db::executeQuery("SELECT * FROM fav_item WHERE user_id = :1 ORDER BY posted DESC", array($_GET["profile"]));
	    if (db::num_rows($result) > 0)
	    {
		$data = array();
		$usr_fav = user::login_by_uid($_GET["profile"]);
		if ($usr_fav == user::username())
		    $usr_fav = "Your";
		else
		    $usr_fav .= "'s";
		array_push($data,"",$usr_fav." latest favorited items:");
		$i_fav = 0;
		while ($row = db::nextRowFromQuery($result))
		{
		    $i_fav++;
		    if ($i_fav == 11)
		    {
			array_push($data,"","<a href='?action=user_items&table=fav_item&id=".$self."'>Show more favorites</a>");
			break;
		    }
		    $item = misc::item_title_by_uid($row["table_id"], $row["table_name"]);
		    if($item != "")
		    {
			array_push($data,"<img width=20 height=20 style='border: 0px solid #261b15; padding: 0px;' src='images/isFav.png'>");
			array_push($data,"favorited the ".substr($row["table_name"],0,strlen($row["table_name"])-1)." \"<a href='?p=detail&table=".$row["table_name"]."&id=".$row["table_id"]."'>".$item."</a>\" at ".$row["posted"]);
		    }
		}
		echo content::create_dynamic_list($data,2,"favorites",11,true,false);
	    }

	    $result = db::executeQuery("
		SELECT 'Total number of <u>maps</u>' as item, count(*) AS amount, 'maps' AS table_name FROM maps WHERE user_id = :1
		UNION
		SELECT 'Total number of <u>units</u>' as item, count(*) AS amount, 'units' AS table_name FROM units WHERE user_id = :2
		UNION
		SELECT 'Total number of <u>guides</u>' as item, count(*) AS amount, 'guides' AS table_name FROM guides WHERE user_id = :3
		UNION
		SELECT 'Total number of <u>replays</u>' as item, count(*) AS amount, 'replays' AS table_name FROM replays WHERE user_id = :4
		UNION
		SELECT 'Total number of <u>comments</u>' as item, count(*) AS amount, 'comments' AS table_name FROM comments WHERE user_id = :5
	    ", array($usr["uid"], $usr["uid"], $usr["uid"], $usr["uid"], $usr["uid"]));
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
			$amount = "<a href='?action=user_items&table=".$row["table_name"]."&id=".$self."'>".$row["amount"]."</a>";
		    }
		    array_push($data,$row["item"],$amount);
		}
		echo content::create_dynamic_list($data,2,"dyn",15,true,false);
	    }
	    if (user::online() and $self == user::uid())
	    {
		$query = "SELECT * FROM following WHERE who = :1";
		$result = db::executeQuery($query, array(user::uid()));
		$data = array();
		while ($row = db::nextRowFromQuery($result))
		{
		    $data[$row["whom"]] = $row["date_time"];
		}
		if (count($data) >= 1)
		{
		    $queries = array();
		    $res_values = array();
		    $res_i = 1;
		    foreach ($data as $key => $value)
		    {
			$val = $res_i;
			$val_d = $res_i + 1;
			array_push($queries, "SELECT * FROM event_log WHERE user_id = :".$val." AND posted > :".$val_d);
			array_push($res_values, $key);
			array_push($res_values, $value);
			$res_i += 2;
		    }
		    $query = implode(" UNION ", $queries) . " ORDER BY posted DESC";
		    $result = db::executeQuery($query, $res_values);
		    if (db::num_rows($result) > 0)
		    {
			echo content::displayEvents($result);
		    }
		}
		
	    }
    	}
    }
    
    public static function upload_map($prev_version_id="0")
    {
	if (!user::online())
	{
	    return;
	}
	echo "<form id='form_class' enctype='multipart/form-data' method='POST' action=''>
		<label>Choose a <u>map</u> file to upload (<u>.oramap</u>):</label><br /><br />
		<span class='file-wrapper'>
		    <input type='file' name='map_upload' id='enhanced' />
		    <span class='button'>Choose a file</span>
		</span><br />
		</label>
		<br />
		<label>Additional info (optional): </label></lable><input type='text' name='additional_desc'><br />
		<input type='submit' name='submit' value='Upload' />
		</form>
	";
            
	$username = user::username();
	$uploaded = upload::upload_oramap($username, $prev_version_id, user::uid());
	if ($uploaded != "")
	{
	    if ($uploaded == "0")
	    {
		echo "<table><tr><th>Map is successfully uploaded</th></tr></table>";
		$query = "SELECT uid,path FROM maps WHERE user_id = :1
			    ORDER BY posted DESC LIMIT 1
		";
		$row = db::nextRowFromQuery(db::executeQuery($query, array(user::uid())));
		$imagePath = misc::minimap($row["path"]);
		echo "<p><a href='/?p=detail&table=maps&id=".$row["uid"]."'><img src='".$imagePath."'></a></p>";
		return;
	    }
	    echo "<table><tr><th>".$uploaded."</th></tr></table>";
	}
    }
    
    public static function upload_guide()
    {
    	if(!user::online())
	    return;
    	
	$arr = array("title" => "", "html_content" => "", "guide_type" => "", "user_id" => user::uid(), "no_additional_info" => "");
	echo content::displayItem($arr,"guides",true);
	
    	echo "<form id='form_class' enctype='multipart/form-data' method='POST' action=''>
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
		<input type='submit' name='submit' value='Upload' />
		</form>
	";
    }
    
    public static function upload_unit()
    {
    	if(!user::online())
	    return;
    	
    	echo "<form id='form_class' enctype='multipart/form-data' action='' method=POST>
	    <label>Uploading <u>unit</u> (shp, yaml):</label><br><br>
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
	    <label>Palette:</label>
		<select name='unit_palette'>
		<option value='temperat.pal'>Temperat</option>
		<option value='egopal.pal'>Egopal</option>
		<option value='interior.pal'>Interior</option>
		<option value='snow.pal'>Snow</option>
		</select><br>
	    <label>Unit description:<br> </label>
		<textarea name='unit_description' cols='40' rows='5'></textarea><br>
	    <label>Choose unit file:</label><br>
		<span class='file-wrapper-no-info'>
		    <input type='file' name='file_0' id='my_file_element' />
		    <span class='button'>Choose a file</span>
		</span>
		<div id='files_list'></div>
		<script>
		    var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 8 );
		    multi_selector.addElement( document.getElementById( 'my_file_element' ) );
		</script><br />
	";
		
	echo "<input type='submit' value='Upload'>
	    </form>
	";

	$username = user::username();
	$uploaded = upload::upload_unit($username);
	if ($uploaded == "1")
	{
	    echo "<table><tr><th>Name of unit is not set!</th></tr></table>";
	    return;
	}
	if ($uploaded != "")
	{
	    echo "<table><tr><th>".$uploaded."</th></tr></table>";
	    $query = "SELECT uid FROM units WHERE user_id = :1
		       ORDER BY posted DESC LIMIT 1
	    ";
	    $row = db::nextRowFromQuery(db::executeQuery($query, array(user::uid())));
	    echo "<table><tr><th>(<a href='?p=detail&table=units&id=".$row["uid"]."'>check unit's page</a>)</th></tr></table>";
	}
    }
    
    public static function upload_replay()
    {
	if (!user::online()) { return; }

	$query = "SELECT COUNT(*) AS count FROM replays WHERE user_id = :1";
	$row = db::nextRowFromQuery(db::executeQuery($query, array(user::uid())));
	$can_upload = 50 - (int)$row["count"];
	echo "<h4>You can upload ".(string)$can_upload." more replays!</h4><br /><p><i>version 2012-0315 and newer only supported</i></p>";
	echo "<form id='form_class' enctype='multipart/form-data' method='POST' action=''>
		<label>Choose a <u>replay</u> file to upload (<u>.rep</u>):</label><br /><br />
		<span class='file-wrapper'>
		    <input type='file' name='replay_upload' id='enhanced' />
		    <span class='button'>Choose a file</span>
		</span><br />
		<br />
		<label>Description: </label></lable><input type='text' name='description'><br />
		<input type='submit' name='submit' value='Upload' />
		</form>
	";
            
	$username = user::username();
	$uploaded = upload::upload_replay($username, user::uid());
	if ($uploaded != "")
	{
	    if ($uploaded == "0")
	    {
		$query = "SELECT uid FROM replays WHERE user_id = :1
			    ORDER BY posted DESC LIMIT 1
		";
		$row = db::nextRowFromQuery(db::executeQuery($query, array(user::uid())));
		echo "<table><tr><th>Successfully uploaded! (<a href='?p=detail&table=replays&id=".$row["uid"]."'>check replay's page</a>)</th></tr></table>";
		return;
	    }
	    echo "<table><tr><th>".$uploaded."</th></tr></table>";
	}
    }
}

?>
