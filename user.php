<?PHP

class user
{
	public static function online()
	{
		if (isset($_SESSION['user_id']))
		{
			return True;
		}
	}
	
	public static function check_logout()
	{
		if (isset($_GET['logout']))
		{
			if (user::online())
			{
				unset($_SESSION['user_id']);
				header("Location: {$_SERVER['HTTP_REFERER']}");
			}
		}
	}
	
	public static function login()
	{
		if(isset($_POST['login']) && isset($_POST['pass']))
		{
			$login = $_POST['login'];
			$pass = md5($_POST['pass']);
			$db_pass = '';
			$query = "SELECT uid,pass FROM users WHERE login = '".$login."'";
			$result = db::executeQuery($query);
			while ($db_data = db::fetch_array($result))
			{
				$db_pass = $db_data['pass'];
				$user_id = $db_data['uid'];
			}
			if( $pass == $db_pass )		//hashes match
			{
				$_SESSION['user_id'] = $user_id;	//start session
				header("Location: {$_SERVER['HTTP_REFERER']}");
			}
			//else
			//{
			//	echo " <script type=\"text/javascript\">alert(\"Password incorrect!\");</script>";
			//}
		}
	}
	
	public static function register_actions()
	{
		if(isset($_GET['key']))
		{
			$query = "SELECT * FROM activation WHERE hash = '".$_GET['key']."'";
			if (db::num_rows(db::executeQuery($query)) == 0)
			{
				echo lang::$lang['activation error'];
			}
			else
			{
				$query = "SELECT email,pass,login,register_date FROM activation WHERE hash = '".$_GET['key']."'";
				$result = db::executeQuery($query);
				while ($db_data = db::fetch_array($result))
				{
					$email = $db_data['email'];
					$pass = $db_data['pass'];
					$login = $db_data['login'];
					$date = $db_data['register_date'];
				}
				$query = "INSERT INTO users
							(email,pass,login,register_date)
							VALUES
							(
							'".$email."','".$pass."','".$login."','".$date."'
							);";
				db::executeQuery($query);
				$query = "DELETE FROM activation WHERE hash = '".$_GET['key']."'";
				db::executeQuery($query);
				echo "$login: ".lang::$lang['activated'];
			}
		}
		elseif(isset($_POST['act']))
		{
			if(!empty($_POST['login']) && !empty($_POST['pass']) && !empty($_POST['email'])) 
			{
				$query = "SELECT email FROM users WHERE email = '".$_POST['email']."'";
				if (db::num_rows(db::executeQuery($query)) == 0)
				{
					$query = "INSERT INTO activation
							(email,pass,login,hash)
							VALUES
							(
							'".$_POST['email']."','".md5($_POST['pass'])."','".$_POST['login']."','".md5($_POST['email'])."'
							);";
					db::executeQuery($query);
					mail($_POST['email'], lang::$lang['register complete'], lang::$lang['activate'].": http://oramod.lv-vl.net/index.php?register&key=".md5($_POST['email'])."",
					"From: noreply@oramod.lv-vl.net\n"."Reply-To:"."X-Mailer: PHP/".phpversion());
					echo lang::$lang['ask to activate'];
				}
				else
				{
					echo lang::$lang['email in use']; 
				}
			}
			else
			{
				echo lang::$lang['empty fields']."<br><br>";
				user::register_form();
			}
		}
		else
		{
			user::register_form();
		}
	}
	
	public static function register_form()
	{
		echo "<form id=\"register_form\" method=\"POST\" action=\"\">";
		echo "<table style=\"text-align:right;\"><tr><td collspan=\"2\"><b>";
		echo "Registration";
		echo "</b></td></tr><tr><td>";
		echo "Login</td><td><input type=\"text\" name=\"login\"></td></tr><tr><td>";
		echo "Password</td><td><input type=\"password\" name=\"pass\"></td></tr><tr><td>";
		echo "E-mail</td><td><input type=\"text\" name=\"email\"></td></tr><tr><td>";
		
		echo "<input type=\"hidden\" name=\"act\">";
		
		echo "<input type=\"submit\" value=\"Confirm\">
		</td></tr></table></form>";
	}
}

?>
