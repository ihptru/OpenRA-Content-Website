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
	    $query = "SELECT
			table_name, table_id, 'people' as type
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
		  SELECT table_name,table_id,type FROM featured

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
			    echo "<h3>";
			    echo lang::$lang['sidebar menu']; 
			
			    echo "</h3>";
			    echo "<ul>				
				<li><a href='index.php?action=mymaps&p=profile'>maps</a></li>
				<li><a href='index.php?action=myunits&p=profile'>units</a></li>
				<li><a href='index.php?action=myguides&p=profile'>guides</a></li>
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
			    }
			    else
			    {
				$id = user::uid();
				$profile = "You";
			    }
			    profile::sidebar_data($profile, $id);
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
		    <h3><? echo lang::$lang['gallery']; ?></h3>

		    <p style='margin-left:5px;' class="thumbs">
			<?PHP 
			$query = "SELECT 
				    uid,
				    path AS image,
				    'maps' AS table_name
				  FROM maps
				  UNION ALL
				  SELECT
				    uid,
				    preview_image AS image,
				    'units' AS table_name
				  FROM units
				  ORDER BY RAND() LIMIT 12";
			$result = db::executeQuery($query);
			echo content::createImageGallery($result);
			
			?>
		    </p>			

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
