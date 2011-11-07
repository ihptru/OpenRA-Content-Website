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
				else
				{
					echo "<h3>Recent Articles</h3>";
				}
				if (user::online())
				{
					profile::upload_map();
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
	<div id="footer-outer" class="clear"><div id="footer-wrap">
	
		<div class="col-a">
				
			<h3>Contact Info</h3>
			
			<p>
			<strong>Phone: </strong>+1234567<br/>
			<strong>Fax: </strong>+123456789
			</p>
			
			<p><strong>Address: </strong>123 Put Your Address Here</p>
				
			<p><strong>E-mail: </strong>me@jungleland.com</p>
			<p>Want more info - go to our <a href="#">contact page</a></p>			
			
			<h3>Follow Us</h3>
			
			<div class="footer-list">
				<ul>				
					<li><a href="index.html" class="rssfeed">RSS Feed</a></li>
					<li><a href="index.html" class="email">Email</a></li>
					<li><a href="index.html" class="twitter">Twitter</a></li>									
				</ul>
			</div>					
				
		</div>
		
		<div class="col-a">			
			
			<h3>Site Links</h3>
			
			<div class="footer-list">
				<ul>				
					<li><a href="index.html">Home</a></li>
					<li><a href="index.html">Style Demo</a></li>
					<li><a href="index.html">Blog</a></li>
					<li><a href="index.html">Archive</a></li>
					<li><a href="index.html">About</a></li>		
					<li><a href="index.html">Template Info</a></li>		
					<li><a href="index.html">Site Map</a></li>					
				</ul>
			</div>					
				
		</div>
		
		<div class="col-a">
		
			<h3>Web Resource</h3>
			
			<p>Morbi tincidunt, orci ac convallis aliquam, lectus turpis varius lorem, eu
			posuere nunc justo tempus leo. </p>
			
			<div class="footer-list">
				<ul>
                    <li><a href="http://themeforest.net?ref=ealigam" title="Site Templates">ThemeForest</a></li>
					<li><a href="http://www.4templates.com/?go=228858961" title="Website Templates">4Templates</a></li>
					<li><a href="http://store.templatemonster.com?aff=ealigam" title="Web Templates">TemplateMonster</a></li>
					<li><a href="http://graphicriver.net?ref=ealigam" title="Stock Graphics">GraphicRiver</a></li>
					<li><a href="http://www.dreamhost.com/r.cgi?287326" title="Web Hosting">Dreamhost</a></li>
				</ul>
			</div>			

		</div>		

		<div class="col-b">
		
			<h3>About</h3>			
			
			<p>
			<a href="index.html"><img src="images/gravatar.jpg" width="40" height="40" alt="firefox" class="float-left" /></a>
			Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec libero. Suspendisse bibendum. 
			Cras id urna. Morbi tincidunt, orci ac convallis aliquam, lectus turpis varius lorem, eu 
			posuere nunc justo tempus leo. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec libero. Suspendisse bibendum. 
			Cras id urna. <a href="index.html">Learn more...</a>
			</p>			
			
		</div>		
		
		<div class="fix"></div>
		
		<!-- footer-bottom -->
		<div id="footer-bottom">
	
			<div class="bottom-left">
				<p>
				&copy; 2010 <strong>Copyright Info Here</strong>&nbsp; &nbsp; &nbsp;
				Design by <a href="http://www.styleshout.com/">styleshout</a>
				</p>
			</div>		
	
			<div class="bottom-right">
				<p>		
					<a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a> | 
		   		    <a href="http://validator.w3.org/check/referer">XHTML</a>	|
					<a href="index.html">Home</a> |
					<strong><a href="#top" class="back-to-top">Back to Top</a></strong>								
				</p>
			</div>

		<!-- /footer-bottom -->		
		</div>
	
	<!-- /footer-outer -->		
	</div></div>		

<!-- /footer -->
</div>

</body>
</html>
