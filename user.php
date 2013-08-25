<?PHP

class user
{
    // user online?
    public static function online()
    {
	if (!user::check_cookie_enabled())
	    return False;

	$sess_id = session_id();

	//session is set (logged in): record in `signed_in` table must exist
	$query = "SELECT * FROM signed_in WHERE sess_hash = :1";
	$result = db::executeQuery($query, array($sess_id));
	if (db::num_rows($result) != 0)
	    return True;	// everything is OK! User is online!
	// if we are in here, current session ID is different from one in DB (session is expired or login in another browser)
	// but if session is expired, there could be a `remember me` option set!
	// so we check it (it's related only to expired session, if login in another browser:
	//				DB value was changed and it will return False in any case)
	if(isset($_COOKIE["remember"]))
	{
	    $query = "SELECT * FROM signed_in WHERE sess_hash = :1";
	    $result = db::executeQuery($query, array($_COOKIE["remember"]));
	    if (db::num_rows($result) == 0)
	    {
		// cookie is set but hashes do not match: probably faking user's identities
		// or user is logged in different browser so it has updated db value
		// then we can clear current cookie var
		return False;
	    }
	    $row = db::nextRowFromQuery($result);
	    $user_id = $row["user_id"];

	    $current_session_id = session_id();

	    //update values in db and in cookie since there is such a session ID in DB
	    $query = "UPDATE signed_in SET sess_hash = :1 WHERE user_id = :2";
	    db::executeQuery($query, array($current_session_id, $user_id));
	    //we can not have same hash forever so change it in DB and in COOKIE when user is back after session was expired
	    setcookie("remember", $current_session_id, time()+3600*24*360, "/");
	    return True;
	}
	return False;
    }

    // uid of current client
    public static function uid()
    {
	if (user::online())
	{
	    $query = "SELECT * FROM signed_in WHERE sess_hash = :1";
	    $result = db::executeQuery($query, array(session_id()));
	    while ($row = db::nextRowFromQuery($result))
		return $row["user_id"];
	}
	return "0";
    }

    // get username of current client
    public static function username()
    {
	if (user::online())
	{
	    $query = "SELECT login FROM users WHERE uid = :1";
	    $result = db::executeQuery($query, array(user::uid()));
	    while ($db_data = db::nextRowFromQuery($result))
	    {
		return $db_data['login'];
	    }
	}
	return "";	// if somehow this function is run by some faker which is not logged in
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
		    setcookie("map_type", "", time()-60*60, "/");
		    if (isset($_COOKIE["map_my_items"]))
			setcookie("map_my_items", "", time()-60*60, "/");
		}
		if (isset($_COOKIE["unit_sort_by"]))
		{
		    setcookie("unit_sort_by", "", time()-60*60, "/");
		    setcookie("unit_type", "", time()-60*60, "/");
		    if (isset($_COOKIE["unit_my_items"]))
			setcookie("unit_my_items", "", time()-60*60, "/");
		}
		if (isset($_COOKIE["guide_sort_by"]))
		{
		    setcookie("guide_sort_by", "", time()-60*60, "/");
		    setcookie("guide_type", "", time()-60*60, "/");
		    if (isset($_COOKIE["guide_my_items"]))
			setcookie("guide_my_items", "", time()-60*60, "/");
		}
		if (isset($_COOKIE["replay_sort_by"]))
		{
		    if (isset($_COOKIE["replay_version"]))
			setcookie("replay_version", "", time()-60*60, "/");
		    setcookie("replay_sort_by", "", time()-60*60, "/");
		    if (isset($_COOKIE["replay_my_items"]))
			setcookie("replay_my_items", "", time()-60*60, "/");
		    if (isset($_COOKIE["replay_tournament"]))
			setcookie("replay_tournament", "", time()-60*60, "/");
		}
		if (isset($_COOKIE["msg_unread_only_filter"]))
		    setcookie("msg_unread_only_filter", "", time()-60*60, "/");
		
		//remove from db
		$query = "DELETE FROM signed_in WHERE user_id = :1";
		db::executeQuery($query, array(user::uid()));
		//unset session vars
		misc::event_log(user::uid(), "logout");

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
		$query = "SELECT uid,pass FROM users WHERE login = :1";
		$result = db::executeQuery($query, array($login));
		if (db::num_rows($result) == 0)
		{
		    return;
		}
		while ($db_data = db::nextRowFromQuery($result))
		{
		    $db_pass = $db_data['pass'];
		    $user_id = $db_data['uid'];
		}
		if( $pass == $db_pass )		//hashes match
		{
		    $sess_hash = session_id();
		    $query = "SELECT * FROM signed_in WHERE user_id = :1";
		    $result = db::executeQuery($query, array($user_id));
		    if (db::num_rows($result) == 0)
		    {
			$query = "INSERT INTO signed_in
				(user_id, sess_hash)
				VALUES
				(:1,:2)
			";
			db::executeQuery($query, array($user_id, $sess_hash));
		    }
		    else
		    {
			$query = "UPDATE signed_in
				    SET sess_hash = :1
				    WHERE user_id = :2";
			db::executeQuery($query, array($sess_hash, $user_id));
		    }
		    
		    if (isset($_POST["remember"]))
		    {
			if ($_POST["remember"] == "yes")
			{
			    if (user::check_cookie_enabled())
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

    // is always executed to check register action
    public static function register_actions()
    {
	if(!isset($_POST['act']))
	{
	    content::create_register_form();
	    return;
	}
	
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
	    return;
	}
	
	if(empty($_POST['rlogin']) && empty($_POST['rpass']) && empty($_POST['verpass']) && empty($_POST['email'])) 
	{
	    echo "Empty fields found, try again<br><br>";
	    content::create_register_form();
	    return;
	}

	if (!preg_match('/^([a-zA-Z0-9_]+)$/', $_POST['rlogin']))
	{
	    echo "Login can contain only a-z, A-Z, 0-9, _";
	    return;
	}

	if ($_POST['rpass'] != $_POST['verpass'])
	{
	    echo "Passwords do not match"; 
	    return;
	}
	
	$query = "SELECT email FROM users WHERE email = :1";
	if (db::num_rows(db::executeQuery($query, array($_POST['email']))) != 0)
	{
	    echo "someone already uses this email";
	    return;
	}
	
	$query = "SELECT login FROM users WHERE login = :1";
	if (db::num_rows(db::executeQuery($query, array($_POST['rlogin']))) != 0)
	{
	    echo "Account with this username already exists";
	    return;
	}

	if ( !strpos($_POST['email'], '@') )
	{
	    echo "Email format error";
	    return;
	}

	$query = "INSERT INTO users
		(email,pass,login)
		VALUES
		(:1,:2,:3)
	;";
	$result = db::executeQuery($query, array($_POST['email'], md5($_POST['rpass']), $_POST['rlogin']));
	if ($result)
	{
	    user::prepare_account($_POST['rlogin']);
	    echo "Successfully registered!";
	}
    }

    // is run after account is activated
    public static function prepare_account($username)
    {
	$path = WEBSITE_PATH . "users/" . $username;
	mkdir($path);
	mkdir($path . "/maps");
	mkdir($path . "/units");
	mkdir($path . "/replays");
    }

    // is always run to check recover username or password action
    public static function recover()
    {
	if (isset($_GET['recover_pass']))
	{
	    echo "<form id='register_form' method='POST' action=''>";
	    echo "<table style='text-align:right;'><tr><td collspan='2'><b>";
	    echo "recover";
	    echo "</b></td></tr><tr><td>";
	    echo "Login</td><td><input type='text' name='rpass_login'></td></tr><tr><td>";
	    echo "E-mail</td><td><input type='text' name='rpass_email'></td></tr><tr><td>";
	    echo "<input type='submit' value='Confirm'>
		</td></tr></table></form>";
	}
	elseif(isset($_GET['recover_user']))
	{
	    echo "<form id='register_form' method='POST' action=''>";
	    echo "<table style='text-align:right;'><tr><td collspan='2'><b>";
	    echo "recover";
	    echo "</b></td></tr><tr><td>";
	    echo "Password</td><td><input type='password' name='ruser_pass'></td></tr><tr><td>";
	    echo "E-mail</td><td><input type='text' name='ruser_email'></td></tr><tr><td>";
	    echo "<input type='submit' value='Confirm'>
	    </td></tr></table></form>";
	}

	if (isset($_POST['rpass_login']) && isset($_POST['rpass_email']))
	{
	    $query = "SELECT login,email FROM users WHERE login = :1 AND email = :2";
	    if (db::num_rows(db::executeQuery($query,array($_POST['rpass_login'], $_POST['rpass_email']))) == 0)
	    {
		echo "User with such data not found";
		return;
	    }
	    $query = "SELECT login FROM recover WHERE login = :1";
	    if (db::num_rows(db::executeQuery($query, array($_POST['rpass_login']))) != 0)
	    {
		echo "You've already requested password update";
		return;
	    }
	    $query = "INSERT INTO recover
		    (login,email,hash)
		    VALUES
		    (:1,:2,:3)
	    ";
	    db::executeQuery($query, array($_POST['rpass_login'], $_POST['rpass_email'], md5($_POST['rpass_email'])));
	    misc::send_mail( $_POST['rpass_email'], 'Recover Password at OpenRA Content Website', 'recover password: http://'.$_SERVER['HTTP_HOST'].'/?recover&recover_link='.md5($_POST['rpass_email']), array( 'From' => 'noreply@'.$_SERVER['HTTP_HOST'] ) );
	    echo "Sent an email";
	    return;
	}
	elseif (isset($_POST['ruser_pass']) && isset($_POST['ruser_email']))
	{
	    $query = "SELECT pass,email FROM users WHERE pass = :1 AND email = :2";
	    if (db::num_rows(db::executeQuery($query, array(md5($_POST['ruser_pass']), $_POST['ruser_email']))) == 0)
	    {
		echo "User with such data not found";
		return;
	    }
	    $query = "SELECT login FROM users WHERE pass = :1 AND email = :2";
	    $result = db::executeQuery($query, array(md5($_POST['ruser_pass']), $_POST['ruser_email']));
	    while ($db_data = db::nextRowFromQuery($result))
	    {
		$user = $db_data['login'];
	    }
	    misc::send_mail( $_POST['ruser_email'], 'Recover Username at OpenRA Content Website', 'Your username: '.$user, array( 'From' => 'noreply@'.$_SERVER['HTTP_HOST'] ) );
	    echo "Sent an email";
	    return;
	}

	if (isset($_GET['recover_link']))
	{
	    $hash = $_GET['recover_link'];
	    $query = "SELECT hash FROM recover WHERE hash = :1";
	    if (db::num_rows(db::executeQuery($query, array($hash))) == 0)
	    {
		echo "Nothing to activate";
		return;
	    }
	    $query = "SELECT login FROM recover WHERE hash = :1";
	    $result = db::executeQuery($query, array($hash));
	    while ($db_data = db::nextRowFromQuery($result))
	    {
		$user = $db_data['login'];
	    }
	    echo "<form id='register_form' method='POST' action=''>";
	    echo "<table style='text-align:right;'><tr><td collspan='2'><b>";
	    echo "Enter new password:";
	    echo "</b></td></tr><tr><td>";
	    echo "Password</td><td><input type='password' name='rpass_new'></td></tr><tr><td>";
	    echo "Re-enter password</td><td><input type='password' name='rpass_new_check'></td></tr><tr><td>";
	    echo "<input type='submit' value='Confirm'>
		</td></tr></table></form>";
	    if (isset($_POST['rpass_new']) && isset($_POST['rpass_new_check']))
	    {
		if ($_POST['rpass_new'] == $_POST['rpass_new_check'])
		{
		    $password = md5($_POST['rpass_new']);
		    $query = "UPDATE users
				SET pass = :1
				WHERE login = :2";
		    db::executeQuery($query, array($password, $user));
		    $query = "DELETE FROM recover WHERE login = :1";
		    db::executeQuery($query, array($user));
		    echo "Password updated";
		    echo "<script>location.href='http://".$_SERVER["HTTP_HOST"]."/'</script>";
		}
		else
		{
		    echo "Passwords do not match";
		}
	    }
	    return;
	}
    }
    
    public static function login_by_uid($uid)
    {
	$query = "SELECT login FROM users WHERE uid = :1";
	$row = db::nextRowFromQuery(db::executeQuery($query, array($uid)));
	return $row["login"];
    }
    
    public static function email_by_uid($uid)
    {
	$query = "SELECT email FROM users WHERE uid = :1";
	$row = db::nextRowFromQuery(db::executeQuery($query, array($uid)));
	return $row["email"];
    }
    
    public static function exists($uid)
    {
	$query = "SELECT uid FROM users WHERE uid = :1";
	$result = db::executeQuery($query, array($uid));
	while ($row = db::nextRowFromQuery($result))
	    return true;
	return false;
    }
    
    public static function check_cookie_enabled()
    {
	if (session_id())
	{
	    return true;
	}
	return false;
    }
}

?>
