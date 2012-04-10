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
	    $query = "
		-- most favourited
		SELECT
		    table_name,
		    table_id,
		    'peoples' as type
		FROM fav_item
		    WHERE table_name <> 'articles'
		    GROUP BY table_name,table_id
		    HAVING (COUNT(table_id) = 
				(SELECT MAX(user_id_amount) FROM
				    (SELECT COUNT(user_id) AS user_id_amount FROM fav_item GROUP BY table_id)
				    AS amounts
				)
			   )
		UNION ALL
		-- featured, editors choice, etc from featured table
		SELECT
		    table_name,
		    table_id,
		    type
		FROM featured
		UNION ALL
		-- mostly played maps
		SELECT
		    'maps' AS table_name,
		    uid AS table_id,
		    'played' AS type
		FROM maps
		    WHERE maphash = (SELECT map_hash FROM map_stats HAVING MAX(amount_games))
		UNION ALL
		-- most discussed
		SELECT
		    table_name,
		    table_id,
		    'discussed' as type
		FROM comments
		    WHERE table_name <> 'articles'
		    GROUP BY table_name,table_id
		    HAVING (COUNT(table_id) = 
				(SELECT MAX(user_id_amount) FROM
				    (SELECT COUNT(user_id) AS user_id_amount FROM comments GROUP BY table_id)
				    AS amounts
				)
			   )
		UNION ALL
		-- new map
		SELECT
		    'maps' AS table_name,
		    uid AS table_id,
		    'new_map' AS type
		FROM (SELECT * FROM maps ORDER BY posted DESC LIMIT 1) AS tmaps
		UNION ALL
		-- new guide
		SELECT
		    'guides' AS table_name,
		    uid AS table_id,
		    'new_guide' AS type
		FROM (SELECT * FROM guides ORDER BY posted DESC LIMIT 1) AS tguides
		UNION ALL
		-- new unit
		SELECT
		    'units' AS table_name,
		    uid AS table_id,
		    'new_unit' AS type
		FROM (SELECT * FROM units ORDER BY posted DESC LIMIT 1) AS tunits
		UNION ALL
		-- new replay
		SELECT
		    'replays' AS table_name,
		    uid AS table_id,
		    'new_replay' AS type
		FROM (SELECT * FROM replays ORDER BY posted DESC LIMIT 1) AS treplays

		ORDER BY RAND() LIMIT 1
	    ";
	    $res = db::executeQuery($query);
	    echo content::createFeaturedItems($res);
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
			    echo "<h3>Your content</h3>";
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
				  ORDER BY RAND() LIMIT 12";
			$result = db::executeQuery($query);
			if (db::num_rows($result) > 0)
			{
			    echo "<h3>Gallery</h3>";
			    echo "<p style='margin-left:5px;' class='thumbs'>";
			    echo content::createImageGallery($result);
			    echo "</p>";
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
<div id="footer">	

    <!-- footer-outer -->	
    <?PHP
    echo content::create_footer();
    ?>

<!-- /footer -->
</div>

</body>
</html>
