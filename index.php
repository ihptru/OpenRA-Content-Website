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
			$res = db::executeQuery("SELECT * FROM maps");
            echo content::createFeaturedItems($res,"maps");
        ?>		
	
	<!-- /featured -->
	</div>

	<!-- content -->
	<div id="content-wrap" class="clear">

		<div id="content">

			<!-- main -->
			<div id="main">
				<?PHP
				if (isset($_GET['register']) and (!user::online()))
				{
					user::register_actions();
				}
				elseif (isset($_GET['recover']) and (!user::online()))
				{
					echo "<a href='index.php?recover&recover_pass'>Recover Password</a><br>";
					echo "<a href='index.php?recover&recover_user'>Recover Username</a><br>";
					user::recover();
				}
				elseif (isset($_GET['profile']))
				{
					if (user::online())
					{
						profile::upload_map();
					}	
				}
				else
				{
					echo "<h3>Recent Articles</h3>";
				}
                    //content::createArticleItems($result);
                ?>
			<!-- /main -->	
			</div>
		
			<!-- sidebar -->
			<div id="sidebar">
							
				<div class="sidemenu">
					<h3>Sidebar Menu</h3>
					<ul>				
						<li><a href="index.php?register=true">Register</a></li>
					</ul>	
				</div>
							
				<div class="sidemenu">
					<h3>Sponsors</h3>
					<ul>
						<li><a href="http://themeforest.net?ref=ealigam" title="Site Templates">Themeforest</a><span>Site Templates, Web &amp; CMS Themes</span></li>
						<li><a href="http://www.4templates.com/?go=228858961" title="Website Templates">4Templates</a><span>Low Cost High-Quality Templates</span></li>
						<li><a href="http://store.templatemonster.com?aff=ealigam" title="Web Templates">TemplateMonster</a><span>Delivering the Best Templates on the Net!</span></li>
						<li><a href="http://graphicriver.net?ref=ealigam" title="Stock Graphics">GraphicRiver</a><span>Awesome Stock Graphics</span></li>
						<li><a href="http://www.dreamhost.com/r.cgi?287326|SSHOUT" title="Web Hosting">DreamHost</a><span>Premium Webhosting. Use the promocode <strong>sshout</strong> and save <strong>50 USD</strong>.</span></li>
					</ul>
				</div>
				
				<h3>Image Gallery </h3>

				<p class="thumbs">
                    <?PHP 
                        //db::createImageGallery($result)
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
            content::create_footer();
    ?>

<!-- /footer -->
</div>

</body>
</html>
