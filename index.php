<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?PHP
	include_once("hub.php");
    include_once("content.php");
	
	//Create DB If you don't have it setup
	db::connect();
	//db::setup();
?>
<head>

<title><?PHP echo $lang['website_name']; ?></title>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<meta name="author" content="Erwin Aligam - styleshout.com" />
<meta name="description" content="Site Description Here" />
<meta name="keywords" content="openra" />
<meta name="robots" content="index, follow, noarchive" />
<meta name="googlebot" content="noarchive" />

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" media="screen" /><![endif]-->

</head>

<body>
<?PHP
/*
if(isset($_POST['login']) && isset($_POST['pass']))
{
	$login=$_POST['login'];
	$pass=md5($_POST['pass']);
	$dbconn = pg_connect("host=localhost dbname=oramod user=oramod password=iequeiR6");
	$sql="SELECT * FROM users WHERE login='".$login."'";
	$result = pg_query($sql) or die(pg_last_error());
	while ($sign = pg_fetch_array($result))
	{
		$passtwo=$sign['pass'];
		$user_id=$sign['uid'];
	}
	if($pass==$passtwo)
	{
		echo "successfuL";
		$_SESSION['user_id']=$user_id;
	}
	else
	{
		echo "no sucessfull";
	}
}	

if(isset($_SESSION['user_id']))
{
	echo "LOGGED IN!!!";
}
else
{
		echo "<form method=\"POST\" action=\"\">
			Login: <input type=\"text\" name=\"login\">
			Password: <input type=\"password\" name=\"pass\">
			<input type=\"submit\" value=\"sign in\">
			</form>";
}
*/
?>
<!-- wrap -->
<div id="wrap">
	<!-- header -->
	<div id="header">			
		<a name="top"></a>
		<h1 id="logo-text"><a href="index.html" title=""><?PHP echo $lang['website_name'] ?></a></h1>		
		<p id="slogan"><?PHP echo $lang['website_slowgun']; ?></p>					
		
		<div  id="nav">
			<ul>
                <?PHP
                    content::createMenu();
                ?>
			</ul>		
		</div>		
		
		<p id="rss-feed"><a href="index.html" class="feed">Grab the RSS FEEd</a></p>	
		
		<form id="quick-search" action="index.html" method="get" >
			<p>
			<label for="qsearch">Search:</label>
			<input class="tbox" id="qsearch" type="text" name="qsearch" value="Search..." title="Start typing and hit ENTER" />
			<input class="btn" alt="Search" type="image" name="searchsubmit" title="Search" src="images/search.png" />
			</p>
		</form>	
						
	<!-- /header -->					
	</div>
	
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
				if (isset($_GET['register']))
				{
					if(isset($_GET['key']))
					{
						$dbconn = pg_connect("host=localhost dbname=oramod user=oramod password=iequeiR6");
						$sql_key="SELECT key FROM preusers WHERE key='".$_GET['key']."'";
						if(pg_numrows(pg_query($sql_key))==0)
						{
							echo "Activation Link error";
							pg_close($dbconn);
						}
						else
						{
							$dbconn = pg_connect("host=localhost dbname=oramod user=oramod password=iequeiR6");
							$sql_frompreuser = "SELECT * FROM preusers WHERE key='".$_GET['key']."'";
							$result_frompreuser = pg_query($sql_frompreuser) or die(pg_last_error());
							while ($info = pg_fetch_array($result_frompreuser))
							{
								$email=$info['email'];
								$pass=$info['pass'];
								$login=$info['login'];
								$date=$info['register_date'];
							}
							$sql_user = "INSERT INTO users
								(email,pass,login,register_date)
								VALUES
								('".$email."','".$pass."','".$login."','".$date."');
								DELETE FROM preusers WHERE key='".$_GET['key']."'";
							pg_query($sql_user) or die(pg_last_error());
							pg_close($dbconn);
							echo "$login : account activated";
						}	

					}
					elseif(isset($_POST['act']))
					{
						if(!empty($_POST['login']) && !empty($_POST['pass']) && !empty($_POST['email'])) 
						{
							$dbconn = pg_connect("host=localhost dbname=oramod user=oramod password=iequeiR6");
							$sql_mail="SELECT email FROM users WHERE email='".$_POST['email']."'";
							if(pg_numrows(pg_query($sql_mail))==0)
							{
								$sql_preuser = "INSERT INTO preusers
								(email,pass,login,key)
								VALUES
								('".$_POST['email']."','".md5($_POST['pass'])."','".$_POST['login']."','".md5($_POST['email'])."');";
								pg_query($sql_preuser);
								pg_close($dbconn);
								mail($_POST['email'], "Registration complete", "Activate: http://oramod.lv-vl.net/index.php?register=true&key=".md5($_POST['email'])."",
								"From: noreply@oramod.lv-vl.net\n"."Reply-To:"."X-Mailer: PHP/".phpversion());
								echo "Please Activate Your account";
							}
							else
							{
								echo "someone already uses this email"; 
							}
						}
						else
						{
							echo "something not filled";
						}
					}
					else
					{
						echo "<form method=\"POST\" action=\"\">";
						echo "<table style=\"text-align:right;\"><tr><td collspan=\"2\"><b>";
						echo "Registration";
						echo "</b></td></tr><tr><td>";
						echo "Login</td><td><input type=\"text\" name=\"login\"></td></tr><tr><td>";
						echo "Password</td><td><input type=\"text\" name=\"pass\"></td></tr><tr><td>";
						echo "E-mail</td><td><input type=\"text\" name=\"email\"></td></tr><tr><td>";
						echo "<input type=\"hidden\" name=\"act\">";
						echo "<input type=\"hidden\" name=\"register\">";
						echo "<input type=\"submit\" value=\"Confirm\">
						</td></tr></table></form>";
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
