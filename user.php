<?PHP

class user
{
    public static $cookie_hash = "";

    public static function start_cookie_remember()
    {
	if (isset($_COOKIE["remember"]))
	    user::$cookie_hash = $_COOKIE["remember"];
    }

    // user online?
    public static function online()
    {
	if (isset($_SESSION['sess_id']))
	{
	    $sess_id = $_SESSION["sess_id"];
	    $user_id = $_SESSION["user_id"];

	    //session is set: record in `signed_in` table must exist
	    $query = "SELECT * FROM signed_in WHERE user_id = ".$user_id;
	    $result = db::executeQuery($query);
	    if (db::num_rows($result) == 0)
	    {
		//tried to fake user's identities
		session_destroy();
		header("Location: {$_SERVER['HTTP_REFERER']}");
	    }
	    $row = db::nextRowFromQuery($result);
	    if ($row["sess_hash"] == $sess_id)
	    {
		return True;
	    }
	    else
	    {
		// user_id matches but session_id is wrong: probably tried to fake user's identities
		session_destroy();
		header("Location: {$_SERVER['HTTP_REFERER']}");
	    }
	}

	if (misc::check_cookie_enabled())
	{
	    if(isset($_COOKIE["remember"]))
	    {
		$query = "SELECT * FROM signed_in WHERE sess_hash = '".user::$cookie_hash."'";
		$result = db::executeQuery($query);
		if (db::num_rows($result) == 0)
		{
		    //cookie is set but hashes do not match: probably faking user's identities
		    return False;
		}
		$row = db::nextRowFromQuery($result);
		$db_hash = $row["sess_hash"];
		$user_id = $row["user_id"];
		if (isset($_SESSION["sess_id"]))
		{
		    //user could still have not expired session
		    if ($_SESSION["sess_id"] == $db_hash)
		    {
			return True;
		    }
		    else
		    {
			//probably faking user's identities
			return False;
		    }
		}
		else
		{
		    //session is not set: expired; but `remember` in COOKIE is set
		    $current_session_id = session_id();
		    //update values in db and in cookie
		    
		    $query = "UPDATE signed_in SET sess_hash = ? WHERE user_id = ?";
		    db::executeQuery($query, array($current_session_id, $user_id));
		    $_SESSION["sess_id"] = $current_session_id;
		    $_SESSION["user_id"] = $user_id;
		    //we can not have same hash forever so change it in DB and in COOKIE when user is back after session was expired
		    setcookie("remember", $current_session_id, time()+3600*24*360, "/");
		    user::$cookie_hash = $current_session_id;
		    return True;
		}
	    }
	}
    }

    // uid of current client
    public static function uid()
    {
	if (user::online())
	{
	    return $_SESSION['user_id'];
	}
	else
	{
	    return "0";
	}
    }

    // get username of current client
    public static function username()
    {
	if (user::online())
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
	    return "";	// if somehow this function is run by some faker which is not logged in
	}
    }

    // is always executed to check if user requested logout
    public static function check_logout()
    {
	if (isset($_GET['logout']))
	{
	    if (user::online())
	    {
		//remove from cookie if user is remembered
		if (isset($_COOKIE["remember"]))
		{
		    //destroy cookie var
		    setcookie("remember", "", time()-60*60, "/");
		}
		//remove from cookie filters if they are set
		if (isset($_COOKIE["map_sort_by"]))
		{
		    setcookie("map_sort_by", "", time()-60*60, "/");
		    setcookie("map_mod", "", time()-60*60, "/");
		    setcookie("map_tileset", "", time()-60*60, "/");
		}
		elseif (isset($_COOKIE["unit_sort_by"]))
		{
		    setcookie("unit_sort_by", "", time()-60*60, "/");
		    setcookie("unit_type", "", time()-60*60, "/");
		}
		elseif (isset($_COOKIE["guide_sort_by"]))
		{
		    setcookie("guide_sort_by", "", time()-60*60, "/");
		    setcookie("guide_type", "", time()-60*60, "/");
		}
		//remove from db
		$query = "DELETE FROM signed_in WHERE user_id = ".user::uid();
		db::executeQuery($query);
		//unset session vars
		misc::event_log($_SESSION['user_id'], "logout");
		unset($_SESSION['user_id']);
		unset($_SESSION['sess_id']);

		session_destroy();	//after redirect user will get a new session ID
		header("Location: /");
	    }
	}
    }

    // is always executed to check login action
    public static function login()
    {
	if (user::online())
	{
	    return;	//is signed in
	}
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
		    $sess_hash = session_id();
		    $query = "SELECT * FROM signed_in WHERE user_id = ".$user_id;
		    $result = db::executeQuery($query);
		    if (db::num_rows($result) == 0)
		    {
			$query = "INSERT INTO signed_in
				(user_id, sess_hash)
				VALUES
				(?,?)
			";
			db::executeQuery($query, array($user_id, $sess_hash));
		    }
		    else
		    {
			$query = "UPDATE signed_in
				    SET sess_hash = ?
				    WHERE user_id = ?";
			db::executeQuery($query, array($sess_hash, $user_id));
		    }
		    
		    $_SESSION['sess_id'] = $sess_hash;	//start session
		    $_SESSION['user_id'] = $user_id;
		    
		    if (isset($_POST["remember"]))
		    {
			if ($_POST["remember"] == "yes")
			{
			    if (misc::check_cookie_enabled())
			    {
				setcookie("remember", $sess_hash, time()+3600*24*360, "/");
			    }
			}
		    }
		    misc::event_log($user_id, "login");
		    header("Location: /?profile=$user_id");
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
			(?,?,?,?)
		;";
		db::executeQuery($query, array($email, $pass, $login, $date));
		$query = "DELETE FROM activation WHERE hash = ?";
		db::executeQuery($query, array($_GET['key']));
		echo "$login: ".lang::$lang['activated'];

		user::prepare_account($login);
	    }
	}
	elseif(isset($_POST['act']))
	{
	    require_once('libs/recaptchalib.php');
	    $privatekey = "6Ldq-soSAAAAAJnSn1-Gi9CBbuFQ3O-SLw1f0scW";
	    $resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

	    if (!$resp->is_valid)
	    {
		// What happens when the CAPTCHA was entered incorrectly
		echo "The reCAPTCHA wasn't entered correctly. Go back and try it again." .
		"(reCAPTCHA said: " . $resp->error . ")";
		content::create_register_form();
	    }
	    else
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
						(?,?,?,?)
					;";
					db::executeQuery($query, array($_POST['email'], md5($_POST['rpass']), $_POST['rlogin'], md5($_POST['email'])));
					mail($_POST['email'], lang::$lang['register complete'], lang::$lang['activate'].": http://".$_SERVER['HTTP_HOST']."/?register&key=".md5($_POST['email'])."",
					    "From: noreply@".$_SERVER['HTTP_HOST']."\n"."Reply-To:"."X-Mailer: PHP/".phpversion());
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
		    (?,?,?)
	    ";
	    db::executeQuery($query, array($_POST['rpass_login'], $_POST['rpass_email'], md5($_POST['rpass_email'])));
	    mail($_POST['rpass_email'], "recover password", "recover password: http://".$_SERVER['HTTP_HOST']."/?recover&recover_link=".md5($_POST['rpass_email'])."",
		"From: noreply@".$_SERVER['HTTP_HOST']."\n"."Reply-To:"."X-Mailer: PHP/".phpversion());
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
		"From: noreply@".$_SERVER['HTTP_HOST']."\n"."Reply-To:"."X-Mailer: PHP/".phpversion());
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
				SET pass = ?
				WHERE login = ?";
		    db::executeQuery($query, array($password, $user));
		    $query = "DELETE FROM recover WHERE login = ?";
		    db::executeQuery($query, array($user));
		    echo lang::$lang['password updated'];
		}
		else
		{
		    echo lang::$lang['password not match'];
		}
	    }
	}
    }
    
    public static function login_by_uid($uid)
    {
	$query = "SELECT login FROM users WHERE uid = ".$uid;
	$row = db::nextRowFromQuery(db::executeQuery($query));
	return $row["login"];
    }
}

?>
