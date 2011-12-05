<?PHP

class user
{
    // user online?
    public static function online()
    {
	if (isset($_SESSION['user_id']))
	{
	    return True;
	}
    }

    // uid of current client
    public static function uid()
    {
        return $_SESSION['user_id'];
    }

    // get username of current client
    public static function username()
    {
	if (isset($_SESSION['user_id']))
	{
	    $query = "SELECT login FROM users WHERE uid = " . $_SESSION['user_id'];
	    $result = db::executeQuery($query);
	    while ($db_data = db::fetch_array($result))
	    {
		return $db_data['login'];
	    }
	}
	else
	{
	    return "";	// if somehow this function is run by some hacker which is not logged in
	}
    }

    // is always executed to check if user requested logout
    public static function check_logout()
    {
	if (isset($_GET['logout']))
	{
	    if (user::online())
	    {
		unset($_SESSION['user_id']);
		header("Location: /");
	    }
	}
    }

    // is always executed to check login action
    public static function login()
    {
	if (isset($_POST['login']) && isset($_POST['pass']))
	{
	    if (!empty($_POST['login']) && !empty($_POST['pass']))
	    { 
		$login = $_POST['login'];
		$pass = md5($_POST['pass']);
		$db_pass = '';
		$query = "SELECT uid,pass FROM users WHERE login = '".$login."'";
		$result = db::executeQuery($query);
		if (db::num_rows($result) == 0)
		{
		    return;
		}
		while ($db_data = db::fetch_array($result))
		{
		    $db_pass = $db_data['pass'];
		    $user_id = $db_data['uid'];
		}
		if( $pass == $db_pass )		//hashes match
		{
		    $_SESSION['user_id'] = $user_id;	//start session
		    header("Location: /index.php?p=profile");
		}
	    }
	}
    }

    // is always executed to check register or activation action
    public static function register_actions()
    {
	if(isset($_GET['key']))
	{
	    $query = "SELECT hash FROM activation WHERE hash = '".$_GET['key']."'";
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

		user::prepare_account($login);
	    }
	}
	elseif(isset($_POST['act']))
	{
	    if(!empty($_POST['rlogin']) && !empty($_POST['rpass']) && !empty($_POST['verpass']) && !empty($_POST['email'])) 
	    {
		if ($_POST['rpass'] == $_POST['verpass'])
		{
		    $query = "SELECT email FROM users WHERE email = '".$_POST['email']."'";
		    if (db::num_rows(db::executeQuery($query)) == 0)
		    {
			$query = "SELECT login FROM users WHERE login = '".$_POST['rlogin']."'";
			if (db::num_rows(db::executeQuery($query)) == 0)
			{
			    if ( strpos($_POST['email'], '@') )
			    {
				$already_requested = false;
				$query = "SELECT login FROM activation WHERE login = '".$_POST['rlogin']."'";
				if (db::num_rows(db::executeQuery($query)) != 0)
				{
				    $already_requested = true;
				}
				$query = "SELECT email FROM activation WHERE email = '".$_POST['email']."'";
				if (db::num_rows(db::executeQuery($query)) != 0)
				{
				    $already_requested = true;
				}
				if ($already_requested == false)
				{
				    $query = "INSERT INTO activation
					    (email,pass,login,hash)
					    VALUES
					    (
					    '".$_POST['email']."','".md5($_POST['rpass'])."','".$_POST['rlogin']."','".md5($_POST['email'])."'
					    );";
				    db::executeQuery($query);
				    mail($_POST['email'], lang::$lang['register complete'], lang::$lang['activate'].": http://oramod.lv-vl.net/index.php?register&key=".md5($_POST['email'])."",
					"From: noreply@oramod.lv-vl.net\n"."Reply-To:"."X-Mailer: PHP/".phpversion());
				    echo lang::$lang['ask to activate'];
				}
				else
				{
				    echo lang::$lang['already requested'];
				}
			    }
			    else
			    {
				echo lang::$lang['email error'];
			    }
			}
			else
			{
			    echo lang::$lang['user exists'];
			}
		    }
		    else
		    {
			echo lang::$lang['email in use']; 
		    }
		}
		else
		{
		    echo lang::$lang['password not match']; 
		}
	    }
	    else
	    {
		echo lang::$lang['empty fields']."<br><br>";
		content::create_register_form();
	    }
	}
	else
	{
	    content::create_register_form();
	}
    }

    // is run after account is activated
    public static function prepare_account($username)
    {
	$path = WEBSITE_PATH . "users/" . $username;
	mkdir($path);
	mkdir($path . "/maps");
	mkdir($path . "/units");
    }

    // is always run to check recover username or password action
    public static function recover()
    {
	if (isset($_GET['recover_pass']))
	{
	    echo "<form id=\"register_form\" method=\"POST\" action=\"\">";
	    echo "<table style=\"text-align:right;\"><tr><td collspan=\"2\"><b>";
	    echo lang::$lang['recover'];
	    echo "</b></td></tr><tr><td>";
	    echo lang::$lang['login']."</td><td><input type=\"text\" name=\"rpass_login\"></td></tr><tr><td>";
	    echo "E-mail</td><td><input type=\"text\" name=\"rpass_email\"></td></tr><tr><td>";
	    echo "<input type=\"submit\" value=\"".lang::$lang['confirm']."\">
		</td></tr></table></form>";
	}
	elseif(isset($_GET['recover_user']))
	{
	    echo "<form id=\"register_form\" method=\"POST\" action=\"\">";
	    echo "<table style=\"text-align:right;\"><tr><td collspan=\"2\"><b>";
	    echo lang::$lang['recover'];
	    echo "</b></td></tr><tr><td>";
	    echo lang::$lang['password']."</td><td><input type=\"password\" name=\"ruser_pass\"></td></tr><tr><td>";
	    echo "E-mail</td><td><input type=\"text\" name=\"ruser_email\"></td></tr><tr><td>";
	    echo "<input type=\"submit\" value=\"".lang::$lang['confirm']."\">
	    </td></tr></table></form>";
	}

	if (isset($_POST['rpass_login']) && isset($_POST['rpass_email']))
	{
	    $query = "SELECT login,email FROM users WHERE login = '".$_POST['rpass_login']."' AND email = '".$_POST['rpass_email']."'";
	    if (db::num_rows(db::executeQuery($query)) == 0)
	    {
		echo lang::$lang['recover nouser'];
		return;
	    }
	    $query = "SELECT login FROM recover WHERE login = '".$_POST['rpass_login']."'";
	    if (db::num_rows(db::executeQuery($query)) != 0)
	    {
		echo lang::$lang['recover requested'];
		return;
	    }
	    $query = "INSERT INTO recover
		    (login,email,hash)
		    VALUES
		    (
		    '".$_POST['rpass_login']."','".$_POST['rpass_email']."','".md5($_POST['rpass_email'])."'
		    )";
	    db::executeQuery($query);
	    mail($_POST['rpass_email'], "recover password", "recover password: http://oramod.lv-vl.net/index.php?recover&recover_link=".md5($_POST['rpass_email'])."",
		"From: noreply@oramod.lv-vl.net\n"."Reply-To:"."X-Mailer: PHP/".phpversion());
	}
	elseif (isset($_POST['ruser_pass']) && isset($_POST['ruser_email']))
	{
	    $query = "SELECT pass,email FROM users WHERE pass = '".md5($_POST['ruser_pass'])."' AND email = '".$_POST['ruser_email']."'";
	    if (db::num_rows(db::executeQuery($query)) == 0)
	    {
		echo lang::$lang['recover nouser'];
		return;
	    }
	    $query = "SELECT login FROM users WHERE pass = '".md5($_POST['ruser_pass'])."' AND email = '".$_POST['ruser_email']."'";
	    $result = db::executeQuery($query);
	    while ($db_data = db::fetch_array($result))
	    {
		$user = $db_data['login'];
	    }
	    mail($_POST['ruser_email'], "recover username", "You username: ".$user,
		"From: noreply@oramod.lv-vl.net\n"."Reply-To:"."X-Mailer: PHP/".phpversion());
	    header("Location: /");
	}

	if (isset($_GET['recover_link']))
	{
	    $hash = $_GET['recover_link'];
	    $query = "SELECT hash FROM recover WHERE hash = '".$hash."'";
	    if (db::num_rows(db::executeQuery($query)) == 0)
	    {
		echo lang::$lang['nothing to activate'];
		return;
	    }
	    $query = "SELECT login FROM recover WHERE hash = '".$hash."'";
	    $result = db::executeQuery($query);
	    while ($db_data = db::fetch_array($result))
	    {
		$user = $db_data['login'];
	    }
	    echo "<form id=\"register_form\" method=\"POST\" action=\"\">";
	    echo "<table style=\"text-align:right;\"><tr><td collspan=\"2\"><b>";
	    echo lang::$lang['enter new pw'].":";
	    echo "</b></td></tr><tr><td>";
	    echo lang::$lang['password']."</td><td><input type=\"password\" name=\"rpass_new\"></td></tr><tr><td>";
	    echo lang::$lang['reenter pw']."</td><td><input type=\"password\" name=\"rpass_new_check\"></td></tr><tr><td>";
	    echo "<input type=\"submit\" value=\"".lang::$lang['confirm']."\">
		</td></tr></table></form>";
	    if (isset($_POST['rpass_new']) && isset($_POST['rpass_new_check']))
	    {
		if ($_POST['rpass_new'] == $_POST['rpass_new_check'])
		{
		    $password = md5($_POST['rpass_new']);
		    $query = "UPDATE users
				SET pass = '".$password."'
				WHERE login = '".$user."'";
		    db::executeQuery($query);
		    $query = "DELETE FROM recover WHERE login = '".$user."'";
		    db::executeQuery($query);
		    echo lang::$lang['password updated'];
		}
		else
		{
		    echo lang::$lang['password not match'];
		}
	    }
	}
    }
}

?>
