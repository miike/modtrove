<?php

if(isset($_REQUEST['logout']) && $_REQUEST['logout']) {
	foreach(array("user_name","user_fname","user_admin","user_email","user_uid") as $v)
		unset($_SESSION[$v]);
	setcookie("user_name", 0,0,'/');
	setcookie("user_hash", '',0,'/');
	header('Location: '.$ct_config['blog_path'].$_SERVER['LABTROVE_REQUEST_PATH']);
	newMsg("Logout successful","message");
	exit();
}


if(isset($_REQUEST['turl'])){
	$_SESSION['labtrove']['turl'] = $_REQUEST['turl'];
}

function chkperm($perm){
		return 1;
}

function check_remembered(){
	global $ct_config;
	if($usern = $_COOKIE['user_name']){
	   
		$user = get_user_info($usern);
		if($user != 'Error' && md5($ct_config['rememberme']['salt'].$user['user'].$user['access'].$user['email'].$user['uid'])==$_COOKIE['user_hash']){
				$_SESSION['user_name'] = $user['user'];
                $_SESSION['user_fname'] = $user['name'];
                $_SESSION['user_admin'] = $user['access'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_uid'] = $user['uid'];

		}else{
			return 'Error';
		}


	}


	}



function get_user_info($usern,$field = 0){

global $ct_config;

if($_SESSION['user_info'][$usern]['set'] < (time()-3600)){
	
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_name` LIKE  '{$usern}'";
	$tresult = runQuery($sql,'iGet User Info');

	if(mysql_num_rows (  $tresult )){
	$user = mysql_fetch_array($tresult);

                $_SESSION['user_info'][$usern] = array("access"=>$user['user_type'],"user"=>$user['user_name'],"name"=>$user['user_fname'],"email"=>$user['user_email'],"uid"=>$user['user_uid'],"image"=>$user['user_image'],"result"=>1,"set"=>time());
				}else{
		return 'Error';
	}
			

}
if($field){
	return $_SESSION['user_info'][$usern][$field];
}else{
	return $_SESSION['user_info'][$usern];
}


}

function renlogin_blog(){

global $ct_config;
$uri = NULL;

if(isset($_REQUEST['turl'])){
	$turl=$_REQUEST['turl'];
}else{
	$turl=$ct_config['blog_path'].$_SERVER['LABTROVE_REQUEST_PATH'];
}

if(isset($_SESSION['user_name'])){
        return ' Current user: <a class="with_user" href="'.render_link('',array('user' => $_SESSION['user_name'])).'">'.$_SESSION['user_fname'].'</a> | <a href="'.$ct_config['blog_path'].$_SERVER['LABTROVE_REQUEST_PATH'].'?logout=1">Log Out</a>';
}else{
	global $jquery;
	$jquery['set'] = 1;
	
	if(!isset($_REQUEST['flogin']) || !$_REQUEST['flogin']){
        $ret = '<div id="loginButton"> <a class="with_user" href="#" onclick="$(\'#loginButton\').hide();$(\'#loginBox\').show(); return false;">Login</a> </div>';
		$display = "none";
	}else{
		$display = "block";
	}
        if(isset($_REQUEST['flogin']) && $_REQUEST['flogin'] == 1) {
                        $ret = 'Invalid username or password! &nbsp;<br />';
        }
        $ret .= '<div id="loginBox" style="display: '.$display.'"><form action="/login.php" method="post"> <input type="hidden" name="rew" value="'.$ct_config['var_prefix'].'"><input type="hidden" name="turl" value="'.$turl.'"> Username <input type="text" size="7" name="usern" class="main_input" tabindex="1" align="absmiddle"> Password <input type="password" name="pass" size="10" tabindex="2" align="absmiddle" class="main_input"> Remember Me <input name="remember" type="checkbox" value="true"><input type="submit" name="loginsub" value=">" class="main_submit"></form></div>';

        return $ret;
}

}



function do_login(){


global $ct_config;

if(isset($_SESSION['labtrove']['turl']) && strlen($_SESSION['labtrove']['turl'])){
	$rurl=$_SESSION['labtrove']['turl'];
}else{
	$rurl=$ct_config['blog_path'];
}



if($_REQUEST['rew']){
	$ct_config['var_prefix'] = $_REQUEST['rew']{0};
}else{
	$ct_config['var_prefix'] = "?";
}

if($_REQUEST['loginsub']){

$usern = ereg_replace( "[^A-Za-z0-9\.]", "", $_REQUEST['usern']);
$pass =  stripslashes($_REQUEST['pass']);

if(strlen($pass)){
	
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_name` LIKE  '{$usern}' AND `user_enabled` = 1";
	$tresult = runQuery($sql,'iGet User Info');

	if(!mysql_num_rows (  $tresult )){
		header("Location: $rurl{$ct_config['var_prefix']}flogin=1&loginbox=1"); /// Redirect browser /
		exit();
	}else{
		$user = mysql_fetch_array($tresult);

		if($user['user_pass'] != crypt($pass,$user['user_pass'])){
				header("Location: $rurl{$ct_config['var_prefix']}flogin=1&loginbox=1"); /// Redirect browser /
				exit();
		}else{
				if($_REQUEST['remember']){
					setcookie("user_name", $user['user_name'], time()+(3600*24*$ct_config['rememberme']['time']),'/');	
					setcookie("user_hash", md5($ct_config['rememberme']['salt'].$user['user_name'].$user['user_type'].$user['user_email'].$user['user_uid']), time()+(3600*24*$ct_config['rememberme']['time']),'/');
				}
	
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_fname'] = $user['user_fname'];
                $_SESSION['user_admin'] = $user['user_type'];
                $_SESSION['user_email'] = $user['user_email'];
                $_SESSION['user_uid'] = $user['user_uid'];

				if(isset($_SESSION['labtrove']['turl']))
					unset($_SESSION['labtrove']['turl']);
					
			   header("Location: $rurl"); //* Redirect browser 	
				exit();
		}
     }
}else{
             header("Location: $rurl{$ct_config['var_prefix']}flogin=1&loginbox=1"); /// Redirect browser /	
				exit();
}
}

}



function login_with_uid($uid){
	global $ct_config;
	
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_uid` LIKE  '{$uid}' AND `user_enabled` = 1";
	$tresult = runQuery($sql,'iGet User Info');

	if(mysql_num_rows (  $tresult )){
	$user = mysql_fetch_array($tresult);
  				$_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_fname'] = $user['user_fname'];
                $_SESSION['user_admin'] = $user['user_type'];
                $_SESSION['user_email'] = $user['user_email'];
                $_SESSION['user_uid'] = $user['user_uid'];
		return true;
	}else{
		return false;
	}

}

function add_users_to_zone_page(){
	
		if($_REQUEST['username']){
			$userinfo = get_user_info($_REQUEST['username'],"name");
				if($userinfo != 'Error'){
				$ret .= "Verified user {$_REQUEST['username']} as {$userinfo}<br><a href=\"#\" onclick=\"oField = window.opener.location.href=window.opener.location.href+'&zone={$_REQUEST['zone']}&auser={$_REQUEST['username']}';window.close(); return false;\">Now add to your group</a> ";
			}else{
				$ret .= "Could not verify the user {$_REQUEST['username']} Plese try again.";
			}
		}

		  $ret .= '
						<h2>To add an user please enter a username</h2>
						<form action="/settings_adduser.php?zone='.$_REQUEST['zone'].'" method="post" onsubmit="this.login.disabled=true;">
						<div>
						<input type="text" name="username" value="">
						<input type="submit" name="login" value="Verify user">					
						</div>

	</form>';

		return $ret;
	
}

function user_info_display(){
	global $jquery,$ct_config;
	$jquery['set'] = 1;
	
	
	if($_REQUEST['pass_udate']){
		$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_name` LIKE  '{$_SESSION['user_name']}' AND `user_enabled` = 1";
		$tresult = runQuery($sql,'iGet User Info');
		$user = mysql_fetch_array($tresult);
		if($user['user_pass'] != crypt(stripslashes($_REQUEST['pass_curr']),$user['user_pass'])){
			$err = "current password incorrect";
		}elseif(strlen($_REQUEST['pass_new'])<6){
			$err = "new password needs to more than 6 characters";
		}if($_REQUEST['pass_new']!=$_REQUEST['pass_two']){
			$err = "new passwords don't match";		
		}else{
			$sql = "UPDATE  `{$ct_config['blog_db']}`.`users` SET  `user_pass` =  '".addslashes(crypt(stripslashes($_REQUEST['pass_new'])))."'  WHERE  `user_name` LIKE  '{$_SESSION['user_name']}' LIMIT 1 ;";
			 runQuery($sql,'iGet User Info');
			$ok = 1;
		}
			if($ok){
				$msg = "<div class=\"infoBox msg\">Updated password</div>";
			}else{
				$msg = "<div class=\"infoBox error\">{$err}</div>";
			}
			
		
	}
	
	$blogpost['title'] = "User Information";
	$blogpost['post'] = "{$msg}";
	$blogpost['post'] .= "<table>";
	$blogpost['post'] .= "<tr><th>Username:</th><td>{$_SESSION['user_name']}</td></tr>";
	$blogpost['post'] .= "<tr><th>Full Name:</th><td>{$_SESSION['user_fname']}</td></tr>";
	$blogpost['post'] .= "<tr><th>Email:</th><td>{$_SESSION['user_email']}</td></tr>";
	$blogpost['post'] .= "<tr><th>User Type:</th><td>{$ct_config['perm_access'][$_SESSION['user_admin']]}</td></tr>";
	$blogpost['post'] .= "<tr><th>Your Master UID:</th><td>{$_SESSION['user_uid']}</td></tr>";
	$blogpost['post'] .= "</table>";
	
	if($err) $dis = ""; else $dis = "display:none;";
	
	$blogpost['post'] .= "(<a href=\"#\" onClick=\"$('#password').fadeIn();return false;\" name=\"details\">Change Password</a>)";
	$blogpost['post'] .= "<form style=\"{$dis}\" id=\"password\" action=\"{$_REQUEST['uri']}#password\" method=\"POST\">";
	$blogpost['post'] .= "<table>";
	$blogpost['post'] .= "<tr><th>Current Password:</th><td><input type=\"password\" name=\"pass_curr\"/></td></tr>";
	$blogpost['post'] .= "<tr><th>New Password:</th><td><input type=\"password\" name=\"pass_new\"/></td></tr>";
	$blogpost['post'] .= "<tr><th>Retype Password:</th><td><input type=\"password\" name=\"pass_two\"/></td></tr>";
	$blogpost['post'] .= "<tr><th></th><td><input type=\"submit\" value=\"update\" name=\"pass_udate\"/></td></tr>";
	$blogpost['post'] .= "</table>";
	$blogpost['post'] .= "</form>";
	
	return blog_style_post(&$blogpost);
}

function getUsers(){
	global 	$ct_config, $jquery;
	$jquery['set'] = 1;
	
	if($_REQUEST['add_user']){
		
			$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_name` LIKE  '{$_REQUEST['user_name']}'";
			$tresult = runQuery($sql,'iGet User Info');
			$user = mysql_fetch_array($tresult);
			if($user['user_id']){
				$err = "Username allready exits";
			}elseif(!$_REQUEST['user_name']){
				$err = "Please enter a username";
			}elseif($_REQUEST['usern'] != ereg_replace( "[^A-Za-z0-9\.]", "", $_REQUEST['usern'])){
				$err = "Invalid username, allowed characters 'A-Za-z0-9.'";
			}elseif(!$_REQUEST['user_fname']){
				$err = "Please enter a full name";
			}elseif(!$_REQUEST['user_email']){
				$err = "Please enter a email";
			}elseif(strlen($_REQUEST['pass_new'])<6){
				$err = "new password needs to more than 6 characters";
			}elseif($_REQUEST['pass_new']!=$_REQUEST['pass_two']){
				$err = "new passwords don't match";		
			}else{
				$sql = "INSERT INTO `users` (`user_id`, `user_name`, `user_pass`, `user_openid`, `user_fname`, `user_email`, `user_image`, `user_type`, `user_enabled`, `user_uid`, `user_notes`) 
					VALUES (NULL, '{$_REQUEST['user_name']}', '".addslashes(crypt(stripslashes($_REQUEST['pass_new'])))."', '', '{$_REQUEST['user_fname']}', '{$_REQUEST['user_email']}', '', '{$_REQUEST['user_type']}', '1', MD5('".md5(time()."".$_REQUEST['user_email'])."'), '');";
				runQuery($sql,'iGet User Info');
				$ok = 1;
				$msg = "<div class=\"infoBox message\">User Added</div>";
			}
			
			
			if(!$ok){
				$msg = "<div class=\"infoBox error\">{$err}</div>";
			}			
	}			
	
	
	if($_REQUEST['user_udate']){
		if($_REQUEST['user_udate']){
			if($_REQUEST['user_fname'] && $_REQUEST['user_fname']){
				$sql = "UPDATE  `{$ct_config['blog_db']}`.`users` SET  `user_fname` =  '{$_REQUEST['user_fname']}', `user_email` =  '{$_REQUEST['user_email']}', `user_type` =  '{$_REQUEST['user_type']}' WHERE  `users`.`user_id` = '{$_REQUEST['user_edit']}';";
				runQuery($sql,'iGet User Info');
				$ok = 1;
				if($_REQUEST['pass_new']){
					$ok = 0;
					if(strlen($_REQUEST['pass_new'])<6){
						$err = "new password needs to more than 6 characters";
					}elseif($_REQUEST['pass_new']!=$_REQUEST['pass_two']){
						$err = "new passwords don't match";		
					}else{
						$sql = "UPDATE  `{$ct_config['blog_db']}`.`users` SET  `user_pass` =  '".addslashes(crypt(stripslashes($_REQUEST['pass_new'])))."'  WHERE  `users`.`user_id` = '{$_REQUEST['user_edit']}' LIMIT 1 ;";
						 runQuery($sql,'iGet User Info');
						$ok = 1;
					}
					if(!$ok){
						$ret .= "<div class=\"infoBox error\">{$err}</div>";
					}
					
				}
				
				if($ok){
					$ret .= "<div class=\"infoBox msg\">Information Updated</div>";
				}
			}else{
				$ret .= "<div class=\"infoBox error\">Full Name and Email address required</div>";
			}
		}
		
		if(!$ok){
		$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_id` LIKE  '{$_REQUEST['user_edit']}'";
		$tresult = runQuery($sql,'iGet User Info');
		$user = mysql_fetch_array($tresult);
	
		$blogpost['title'] = "Edit User Information";
		$blogpost['post'] .= "<form id=\"edit_user\" action=\"admin.php?user_edit={$user['user_id']}\" method=\"POST\">";
		$blogpost['post'] .= "<table>";
		$blogpost['post'] .= "<tr><th>User Name:</th><td>{$user['user_name']}</td></tr>";
		$blogpost['post'] .= "<tr><th>Full Name:</th><td><input type=\"text\" name=\"user_fname\" value=\"{$user['user_fname']}\"/></td></tr>";
		$blogpost['post'] .= "<tr><th>Email:</th><td><input type=\"text\" name=\"user_email\" value=\"{$user['user_email']}\"/></td></tr>";
		$blogpost['post'] .= "<tr><th>Type:</th><td><select name=\"user_type\"/>";
		foreach($ct_config['perm_access'] as $k=>$v){
			if($k == $user['user_type']) $sel = "selected"; else $sel = "";
			$blogpost['post'] .= "<option value=\"$k\" $sel>$v</option>";
		}
		$blogpost['post'] .= "</select></td></tr>";
		$blogpost['post'] .= "<tr><th>New Password*:</th><td><input type=\"password\" name=\"pass_new\"/></td></tr>";
		$blogpost['post'] .= "<tr><th>Retype Password:</th><td><input type=\"password\" name=\"pass_two\"/></td></tr>";
		$blogpost['post'] .= "<tr><th></th><td><input type=\"submit\" value=\"save\" name=\"user_udate\"/></td></tr>";
		$blogpost['post'] .= "</table>";
		$blogpost['post'] .= "* Leave Blank if you dont what to change password";
		$blogpost['post'] .= "</form>";
		$ret .= blog_style_post(&$blogpost);
		
		}
	}
	
	
	
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` ORDER BY `user_name`;";
	
	
	$ret .= "{$msg}";
	$ret.= "<table border=1>";
	$ret.= "<tr><th>User Name</th><th>Full Name</th><th>Email</th><th>Type</th><th></th></tr>";
	$tresult = runQuery($sql,'iGet User Info');
	while($user = mysql_fetch_array($tresult)){
		$ret.= "<tr><td>{$user['user_name']}</td><td>{$user['user_fname']}</td><td><a href=\"mailto:{$user['user_email']}\">{$user['user_email']}</a></td><td>{$ct_config['perm_access'][$user['user_type']]}</td><td>(<a href=\"admin.php?user_edit={$user['user_id']}\">edit</a>)</td></tr>";
	}
	$ret.= "</table>";
	
	if($err && !$ok) $dis = ""; else $dis = "display:none;";
	
	$blogpost['post'] .= "(<a href=\"#\" onClick=\"$('#adduser').fadeIn();return false;\" name=\"adduser\">Add User</a>)";
	$blogpost['post'] .= "<form style=\"{$dis}\" id=\"adduser\" action=\"admin.php\" method=\"GET\">";
	$blogpost['post'] .= "{$msg}";
	$blogpost['post'] .= "<table>";	
	$blogpost['post'] .= "<tr><th>User Name:</th><td><input type=\"text\" name=\"user_name\" value=\"{$_REQUEST['user_name']}\"/></td></tr>";
	$blogpost['post'] .= "<tr><th>Full Name:</th><td><input type=\"text\" name=\"user_fname\" value=\"{$_REQUEST['user_fname']}\"/></td></tr>";
	$blogpost['post'] .= "<tr><th>Email:</th><td><input type=\"text\" name=\"user_email\" value=\"{$_REQUEST['user_email']}\"/></td></tr>";
	$blogpost['post'] .= "<tr><th>Type:</th><td><select name=\"user_type\"/>";
		foreach($ct_config['perm_access'] as $k=>$v){
			if($k == $user['user_type']) $sel = "selected"; else $sel = "";
			$blogpost['post'] .= "<option value=\"$k\" $sel>$v</option>";
		}
		$blogpost['post'] .= "</select></td></tr>";
	$blogpost['post'] .= "<tr><th>New Password:</th><td><input type=\"password\" name=\"pass_new\"/></td></tr>";
	$blogpost['post'] .= "<tr><th>Retype Password:</th><td><input type=\"password\" name=\"pass_two\"/></td></tr>";
	$blogpost['post'] .= "<tr><th></th><td><input type=\"submit\" value=\"update\" name=\"add_user\"/></td></tr>";
	$blogpost['post'] .= "</table>";
	$blogpost['post'] .= "</form>";
	
	$ret .= blog_style_post(&$blogpost);
	
	return $ret;
	

	
}

?>