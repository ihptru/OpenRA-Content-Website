<?PHP
include_once("hub.php");
include_once("content.php");
include_once("profile.php");
include_once("header.php");

content::head();
?>

<!-- wrap -->
<div id="wrap">

    <?PHP
    content::body_head();
    ?>

    <!-- featured -->		
    <div id="featured">			

	<?PHP

	if (count($_GET) == 0)
	{
	    $filename = 'featured.temp';
	    if (file_exists($filename))
	    {
		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		$f_item = explode(",", $contents);
		$data_array = array("table_name" => $f_item[0], "table_id" => $f_item[1], "type" => rtrim($f_item[2]));
		echo content::createFeaturedItems($data_array);
	    }
	}
	?>		

    <!-- /featured -->
    </div>

	<!-- content -->
	<div id="content-wrap" class="clear">

	    <div id="content">


		<!-- main -->
		<div id="main">
		    <?PHP

		    pages::main_page_request();
		    
		    ?>
		<!-- /main -->
		</div>
		
		<!-- sidebar -->
		<div id="sidebar">
		    <div class="sidemenu">
		    <?
			if (user::online())
			{
			    echo "<h3>Your content (upload)</h3>";
			    echo "<ul>				
				<li><a href='index.php?action=mymaps&profile=".user::uid()."'>maps</a></li>
				<li><a href='index.php?action=myunits&profile=".user::uid()."'>units</a></li>
				<li><a href='index.php?action=myguides&profile=".user::uid()."'>guides</a></li>
				<li><a href='index.php?action=myreplays&profile=".user::uid()."'>replays</a></li>
				</ul>
			    ";
			    if(isset($_GET["profile"]))
			    {
				if ($_GET["profile"] == user::uid())
				{
				    $id = user::uid();
				    $profile = "You";
				}
				else
				{
				    $id = $_GET["profile"];
				    $profile = user::login_by_uid($id);
				}
				profile::sidebar_data($profile, $id);
			    }
			    
			}
			else
			{
			    if(isset($_GET["profile"]))
			    {
				$id = $_GET["profile"];
				$profile = user::login_by_uid($id);
				profile::sidebar_data($profile, $id);
			    }
			}

		    ?>
		    </div>

			<?PHP 
			$query = "SELECT 
				    uid,
				    image_path AS image,
				    'screenshot_group' AS table_name
				  FROM screenshot_group
				  ORDER BY RAND() LIMIT 8";
			$result = db::executeQuery($query);
			if (db::num_rows($result) > 0)
			{
			    // 1 loop iteration
			    while (1==1)
			    {
				// force actions: do not show gallery to not interference with more important content for ImageGallery
				if (isset($_GET["p"]))
				    if ($_GET["p"] == "detail")
				    {
					if (($_GET["table"] == "maps" or $_GET["table"] == "units") and content::$thereis_screenshot==true)
					    break;
				    }
				    else if ($_GET["p"] == "gallery")
					break;
				echo "<h3>Gallery</h3>";
				echo content::createImageGallery($result);
				echo "<div class='gallery'><a href='?p=gallery' style='float:right;margin-right:40px;'>Show all</a></div>";
				break;
			    }
			}
			
			?>

		<!-- /sidebar -->				
		</div>		
			
	    <!-- /content -->	
	    </div>
	<!-- /content-wrap -->	
	</div>
<!-- /wrap -->
</div>

<!-- footer -->
<?php
    $q = "SELECT uid,login,register_date FROM users ORDER BY uid DESC LIMIT 1";
    $res = db::executeQuery($q);
    while ($row = db::nextRowFromQuery($res))
    {
	$register_date = explode(" ", $row["register_date"]);
	echo "New user: <a href='?profile=".$row["uid"]."' style='color:#0cd20c;'>".$row["login"]."</a> (".$register_date[0].")";
    }
?>

<div id="footer">	

    <!-- footer-outer -->	
    <?PHP
    echo content::create_footer();
    ?>

<!-- /footer -->
</div>

</body>
</html>
