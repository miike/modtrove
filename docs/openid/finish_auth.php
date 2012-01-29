<?php

require_once "common.php";

include("../../lib/default_config.php");

if(file_exists("../lib/config/blog_{$_SESSION['blog_id']}.php"))
	include("../lib/config/blog_{$_SESSION['blog_id']}.php");

function escape($thing) {
    return htmlentities($thing);
}

function run() {
	global $ct_config;

    $consumer = getConsumer();



    // Complete the authentication process using the server's
    // response.
    $return_to = getReturnTo();
    $response = $consumer->complete($return_to);

    // Check the response status.
    if ($response->status == Auth_OpenID_CANCEL) {
        // This means the authentication was cancelled.
        $msg = 'Verification cancelled.';
    } else if ($response->status == Auth_OpenID_FAILURE) {
        // Authentication failed; display the error message.
        $msg = "OpenID authentication failed: " . $response->message;
    } else if ($response->status == Auth_OpenID_SUCCESS) {
        // This means the authentication succeeded; extract the
        // identity URL and Simple Registration data (if it was
        // returned).

		if(isset($_SESSION['labtrove']['turl']) && strlen($_SESSION['labtrove']['turl'])){
			$rurl=$_SESSION['labtrove']['turl'];
		}else{
			$rurl=$ct_config['blog_path'];
		}
		
        $openid = $response->getDisplayIdentifier();
        $esc_identity = escape($openid);

        $success = sprintf('You have successfully verified ' .
                           '<a href="%s">%s</a> as your identity.<br/>',
                           $esc_identity, $esc_identity);

		$username_r = array("/([a-zA-Z]+:\/\/)/i" => "","/([a-zA-Z]+:\/\/)/i" => "",
					"/\/$/i" => "",					
					"/\?/i" => "-",					
					"/\&/i" => "-",					
					"/\//i" => "-",
					"/\s/i" => "");
		$user['user_openid'] = $openid;
		$user['handle'] = sha1($_REQUEST['assoc_handle'].$openid);
		$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
        $sreg = $sreg_resp->contents();
		if(strlen($sreg['email']))
			$user['user_email'] = $sreg['email'];
		if(strlen($sreg['fullname']))
			$user['user_fname'] = $sreg['fullname'];
		$user['user_type'] = (int)$ct_config['openid']['default_user_type'];		

		$sql = "SELECT * FROM  `users` WHERE  `user_openid` LIKE '".addslashes($user['user_openid'])."' LIMIT 1;";
		$tresult = runQuery($sql,'iGet User Info');
		if(mysql_num_rows ( $tresult ) ){
			$user_sql = mysql_fetch_array($tresult, MYSQL_ASSOC);
			$user = array_merge($user,$user_sql);
			$sql = "UPDATE  `users` SET  `user_fname` =  '".addslashes($user['user_fname'])."', `user_email` =  '".addslashes($user['user_email'])."', `user_image` = '".addslashes($user['user_image'])."' WHERE  `users`.`user_id` ={$user['user_id']} LIMIT 1 ;";	
			if($user_sql['user_enabled']==0) displayError('Account Disabled');
		
		}else{
			if(strlen($user['user_email']) && $user['user_fname']){ $enabled = 1; }else{ $enabled = -1;}
			$user['user_name'] = preg_replace(array_keys($username_r),array_values($username_r),$openid);
			$user['user_uid'] = md5($user['handle']);
		$sql  = "INSERT INTO  `users` (`user_id` ,`user_name` , `user_openid`, `user_fname` ,`user_email`, `user_image`, `user_type` ,`user_enabled` ,`user_uid` ,`user_notes`)
				VALUES ( NULL ,  '".addslashes($user['user_name'])."', '".addslashes($user['user_openid'])."', '".addslashes($user['user_fname'])."',  '".addslashes($user['user_email'])."', '".addslashes($user['user_image'])."',  '{$user['user_type']}',  '$enabled',  '".addslashes($user['user_uid'])."',  '".date("Y-m-d H:i:s").": Account Added\n' ); ";
			newMsg("Registration Complete","message");
		}
		runQuery($sql,'update user info');
		if(strlen($user['user_email']) && $user['user_fname']){
			
			if($_SESSION['labtrove']['openid']['remember']){
					setcookie("user_name", $user['user_name'], time()+(3600*24*$ct_config['rememberme']['time']),'/');	
					setcookie("user_hash", md5($ct_config['rememberme']['salt'].$user['user_name'].$user['user_type'].$user['user_email'].$user['user_uid']), time()+(3600*24*$ct_config['rememberme']['time']),'/');
			}
			
			 $_SESSION['user_name'] = $user['user_name'];
			 $_SESSION['user_fname'] = $user['user_fname'];
             $_SESSION['user_email'] = $user['user_email'];
             $_SESSION['user_uid'] = $user['user_uid'];
             $_SESSION['user_admin'] = $user['user_type'];
			 
			 header("Location: $rurl"); //* Redirect browser 
			   if(isset($_SESSION['labtrove']['turl']))
					unset($_SESSION['labtrove']['turl']);
				exit();
		
		}else{
			include("../style/{$ct_config['blog_style']}/blogstyle.php");
		}

			$blogpost = NULL;	
			$blogpost['title'] = "User Registration.";

			$_SESSION['regist_user'] = $user;
			
			$blogpost['post'] = "<form method=post action=".$ct_config['blog_path']."openid/finish_auth.php>";
			$blogpost['post'] .= "<input type=hidden name=key value=\"{$user['handle']}\"/>";
			
			$blogpost['post'] .= "Please enter the following infomation to complete your registrtaion.";
			$blogpost['post'] .= "<table>";
			$blogpost['post'] .= "<tr><th>OpenID:</th><td>{$_SESSION['regist_user']['user_openid']}</td></tr>";
			if($_SESSION['regist_user']['user_fname'])
				$blogpost['post'] .= "<tr><th>Full Name:</th><td>{$_SESSION['regist_user']['user_fname']}
					<input type=hidden name=user_fname value=\"{$_SESSION['regist_user']['user_fname']}\"></td></tr>";
			else
				$blogpost['post'] .= "<tr><th>Full Name:</th><td><input type=text name=user_fname value=\"{$_SESSION['regist_user']['user_fname']}\"></td></tr>";
			if($_SESSION['regist_user']['user_email'])
				$blogpost['post'] .= "<tr><th>Email Address:</th><td>{$_SESSION['regist_user']['user_email']}
					<input type=hidden name=user_email value=\"{$_SESSION['regist_user']['user_email']}\"></td></tr>";
			else
				$blogpost['post'] .= "<tr><th>Email Address:</th><td><input type=text name=user_email value=\"{$_SESSION['regist_user']['user_email']}\"></td></tr>";

			$blogpost['post'] .= "<tr><th></th><td align=right><input type=submit name=submitinfo value=\"Continue\"/></td></tr>";
	


			$blogpost['post'] .= "</table>";

			$blogpost['post'] .= "</form>";
		global $body;
   			
			$body .= blog_style_post(&$blogpost);
			$title = "OpenId Sign In.";
	}
		$body .= $msg;	
}


if($_REQUEST['submitinfo']){

		include("../style/{$ct_config['blog_style']}/blogstyle.php");
		$blogpost['title'] = "User Registration.";
				
	if($_SESSION['regist_user']['handle']==$_REQUEST['key']){
		if(!$_REQUEST['user_fname']){
			$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: Please Enter a full name.</div></div>";
			$fail = 1;
		}else{
			$_SESSION['regist_user']['user_fname'] = stripslashes($_REQUEST['user_fname']);
		}
		if(!validate_email($_REQUEST['user_email'])){
			$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: Please Enter a valid email address</div></div>";
			$fail = 1;
		}else{
			$_SESSION['regist_user']['user_email'] = stripslashes($_REQUEST['user_email']);
		}

		if($fail){

			$blogpost['post'] .= "<form method=post action=".$ct_config['blog_path']."openid/finish_auth.php>";
			$blogpost['post'] .= "<input type=hidden name=key value=\"{$_SESSION['regist_user']['handle']}\"/>";
			
			$blogpost['post'] .= "Please enter the following infomation to complete your registrtaion.";
			$blogpost['post'] .= "<table>";
			$blogpost['post'] .= "<tr><th>OpenID:</th><td>{$_SESSION['regist_user']['user_openid']}</td></tr>";
			if($_SESSION['regist_user']['user_fname'])
				$blogpost['post'] .= "<tr><th>Full Name:</th><td>{$_SESSION['regist_user']['user_fname']}
					<input type=hidden name=user_fname value=\"{$_REQUEST['user_fname']}\"></td></tr>";
			else
				$blogpost['post'] .= "<tr><th>Full Name:</th><td><input type=text name=user_fname value=\"{$_REQUEST['user_fname']}\"></td></tr>";
			if($_SESSION['regist_user']['user_email'])
				$blogpost['post'] .= "<tr><th>Email Address:</th><td>{$_SESSION['regist_user']['user_email']}
						<input type=hidden name=user_email value=\"{$_REQUEST['user_email']}\"></td></tr>";
			else
				$blogpost['post'] .= "<tr><th>Email Address:</th><td><input type=text name=user_email value=\"{$_REQUEST['user_email']}\"></td></tr>";

			$blogpost['post'] .= "<tr><th></th><td align=right><input type=submit name=submitinfo value=\"Continue\"/></td></tr>";
	


			$blogpost['post'] .= "</table>";

			$blogpost['post'] .= "</form>";

		}else{

			$sql = "UPDATE  `users` SET  `user_fname` =  '".addslashes($_SESSION['regist_user']['user_fname'])."', `user_email` =  '".addslashes($_SESSION['regist_user']['user_email'])."', `user_enabled` = 1 WHERE  `users`.`user_name` = '{$_SESSION['regist_user']['user_name']}' LIMIT 1 ;";	
		runQuery($sql,'update user info');
			 $_SESSION['user_name'] = $_SESSION['regist_user']['user_name'];
			 $_SESSION['user_fname'] = $_SESSION['regist_user']['user_fname'];
             $_SESSION['user_email'] = $_SESSION['regist_user']['user_email'];
             $_SESSION['user_uid'] = $_SESSION['regist_user']['user_uid'];
             $_SESSION['user_admin'] = $_SESSION['regist_user']['user_type'];
			if($_SESSION['turl']){
			 header("Location: {$_SESSION['turl']}"); exit();
			}else{
			 header("Location: /"); exit();
			}
		}
		
	}

			$body .= blog_style_post(&$blogpost);
			$title = "OpenId Sign In.";
}else{
run();
}

include '../page.php';
?>
