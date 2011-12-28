<?PHP
include_once("hub.php");
include_once("content.php");

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
		  HAVING (COUNT(table_name) > 1) 
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
			    if(isset($_GET["profile"]))
			    {
				if ($_GET["profile"] == user::uid())
				{
				    $id = user::uid();
				    $profile = "Your avatar:";
				}
				else
				{
				    $id = $_GET["profile"];
				    $profile = user::login_by_uid($id)."'s avatar:";
				}
			    }
			    else
			    {
				$id = user::uid();
				$profile = "Your avatar:";
			    }
			    $data = array();
			    array_push($data,$profile);
			    array_push($data,"<img src='".misc::avatar($id)."'>");
			    echo content::create_dynamic_list($data,1,"dyn",2,true,true);
			}
			else
			{
			    if(isset($_GET["profile"]))
			    {
				$id = $_GET["profile"];
				$profile = user::login_by_uid($id)."'s avatar:";
				$data = array();
				array_push($data,$profile);
				array_push($data,"<img src='".misc::avatar($id)."'>");
				echo content::create_dynamic_list($data,1,"dyn",2,true,true);
			    }
			}
			
			?>
			<? if (user::online())
			{ ?>
			<h3><? echo lang::$lang['sidebar menu']; ?></h3>
			<ul>				
			    <li><a href="index.php?action=mymaps&p=profile"><? echo "maps"; ?></a></li>
			    <li><a href="index.php?action=myguides&p=profile"><? echo "guides"; ?></a></li>
			    <li><a href="index.php?action=myunits&p=profile"><? echo "units"; ?></a></li>
			</ul>	
			<? } ?>
		    </div>
		    <h3><? echo lang::$lang['gallery']; ?></h3>

		    <p class="thumbs">
			<?PHP 
			
			$result = db::executeQuery("SELECT * FROM maps ORDER BY RAND() LIMIT 12");
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
