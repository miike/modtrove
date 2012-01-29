<?php

$ct_config['protected_paths'][] = "openid";

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

                $_SESSION['user_info'][$usern] = array("user"=>$user['user_name'],"access"=>$user['user_type'],"name"=>$user['user_fname'],"email"=>$user['user_email'],"uid"=>$user['user_uid'],"image"=>$user['user_image'],"result"=>1,"set"=>time());
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


if(isset($_SESSION['labtrove']['turl']) && strlen($_SESSION['labtrove']['turl'])){
	$turl=$_SESSION['labtrove']['turl'];
}else{
	$turl=$ct_config['blog_path'].$_SERVER['LABTROVE_REQUEST_PATH'];
}


//if(!$_REQUEST['loginbox']){
if(isset($_SESSION['user_name'])){
	$uri = (isset($_REQUEST['uri'])) ? $_REQUEST['uri'] : '';
        return '<span class="with_user">  Current user: <a href="'.render_link('',array('user' => $_SESSION['user_name'])).'">'.$_SESSION['user_fname'].'</a> | <a href="'.$ct_config['blog_path'].$uri.'?logout=1">Log Out</a> </span>';
}else{
 
	global $jquery;
	$jquery['set'] = 1;
	$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}openid/openid.js\"></script>\n";
		$jquery['srcs'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$ct_config['blog_path']}openid/openid.css\" />\n";
	$jquery['function'] .= "openid.init('openid_identifier');\n";
		$ret .= '<img src="/inc/user.gif" height=11> <a href="'.$ct_config['blog_path'].$_SERVER['LABTROVE_REQUEST_PATH'].'?loginbox=1">Login</a>';
	
		if(!$_REQUEST['flogin']){
	        $ret = '<div id="loginButton"> <a class="with_user" href="#" onclick="$(\'#openid\').fadeIn(); return false;">Login</a> </div>';
			$display = "none";
		}else{
			$error .= 'Invalid OpenId <br />';
			$display = "block";
		}

	   $ret .= '<div id="openid" class="openpopup" style="display:'.$display.'">

			<!-- Simple OpenID Selector -->
			<form action="'.$ct_config['blog_path'].'openid/try_auth.php" method="get" id="openid_form">
				<input type="hidden" name="action" value="verify" />
				<input type="hidden" name="turl" value="'.$turl.'">
				<fieldset>
			    		<legend>Sign-in or Create New Account</legend>
						<img src="inc/icons/cancel.png" style="float:right;cursor:pointer;" onclick="$(\'#openid\').fadeOut();"/>
							'.$error.'
			    		<div id="openid_choice">
				    		<p>Please click your account provider:</p>
				    		<div id="openid_btns"></div>
						</div>
						<div id="openid_input_area" style="clear:left;">
					
							
							
						</div>
						<div style="float:right">Remember Me <input name="remember" type="checkbox" value="true"></div>
						
						<noscript>
						<p>OpenID is service that allows you to log-on to many different websites using a single indentity.
						Find out <a href="http://openid.net/what/">more about OpenID</a> and <a href="http://openid.net/get/">how to get an OpenID enabled account</a>.</p>
						</noscript>
				</fieldset>
			</form>
			<!-- /Simple OpenID Selector -->
								
				</div>';
		}

        return $ret;



}


function do_login(){

 return 0;
}



function login_with_uid($uid){
	global $ct_config;
	
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_uid` LIKE  '{$uid}'";
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

function verify_openid($openid){
	$file = @file_get_contents($openid);

	$openid = stripos($file,"<link rel=\"openid.server\"");
	$openid2 = stripos($file,"<link rel=\"openid2.server\"");
	//google
	$openid3 = stripos($file,"<XRD");
	$openid4 = stripos($file,"<Service");

	if($openid || $openid2 || ($openid4 && $openid3)){
		return true;
	}else{
		return false;
	}
}

function add_users_to_zone_page(){
	
	
	
	if($_REQUEST['zone'])
		$_SESSION['misc']['zone'] = $_REQUEST['zone'];
	
	$zone = $_SESSION['misc']['zone'];
	if($_REQUEST['openid.identity'])
		$_REQUEST['openid_identifier'] = $_REQUEST['openid.identity'];
	
	if($_REQUEST['openid_identifier']){
		if(verify_openid($_REQUEST['openid_identifier'])){
			$username_r = array("/([a-zA-Z]+:\/\/)/i" => "","/([a-zA-Z]+:\/\/)/i" => "",
						"/\/$/i" => "",					
						"/\?/i" => "-",					
						"/\&/i" => "-",					
						"/\//i" => "-",
						"/\s/i" => "");
			$user =  preg_replace(array_keys($username_r),array_values($username_r),$_REQUEST['openid_identifier']);
			$ret .= "Verified openid {$_REQUEST['openid_identifier']}<br><a href=\"#\" onclick=\"oField = window.opener.location.href=window.opener.location.href+'&zone={$zone}&auser={$user}';window.close(); return false;\">Now add to your group</a> ";
		}else{
			$ret .= "Could not verify the openid {$_REQUEST['openid_identifier']} Please try again.";
		}
	}
	
		
	  $ret .= '
	
		<h2>To add an user please use an openid:</h2>
		
		<h3>Option 1: If you know their openid</h3>
	<div id="">
				
					<form action="'.$ct_config['blog_path'].'settings_adduser.php?zone='.$zone.'" method="post" onsubmit="this.login.disabled=true;">
					<div>
					<input type="text" name="openid_identifier" class="openid_login" value="">
					<input type="submit" name="login" value="Verify user">					
					</div>

</form>
</div>';


global $jquery;
$jquery['set'] = 1;
$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}openid/openid.js\"></script>\n";
$jquery['srcs'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$ct_config['blog_path']}openid/openid.css\" />\n";
$jquery['function'] .= "openid.init('openid_identifier');\n";

$ret .= '<div id="openid" style="">
	
	<h3>Option 2: If they are present:</h3>
	
	<!-- Simple OpenID Selector -->
	<form action="/openid/try_auth.php?zone='.$_REQUEST['zone'].'" method="get" id="openid_form">
	
		<input type="hidden" name="action" value="verify" />
		<input type="hidden" name="turl" value="'.$url.'"/>
		<input type="hidden" name="zone" value="'.$zone.'"/>
		<input type="hidden" name="user_verify" value="1"/>
		
		<fieldset>
					'.$error.'
	    		<div id="openid_choice">
		    		<p>Please click your account provider:</p>
		    		<div id="openid_btns"></div>
				</div>

				<div id="openid_input_area">
			
					
				
				</div>	 
				
				<noscript>
				<p>OpenID is service that allows you to log-on to many different websites using a single indentity.
				Find out <a href="http://openid.net/what/">more about OpenID</a> and <a href="http://openid.net/get/">how to get an OpenID enabled account</a>.</p>
				</noscript>
		</fieldset>
	</form>
	Make sure you have logged out of the service, otherwise you may not be offered a login form. 
	<!-- /Simple OpenID Selector -->
						
		</div>';

	return $ret;

}

function user_info_display(){
  return user_info_display_by_user_name($_SESSION['user_name']);
}

function user_info_display_by_user_name($user_name){
	
	global $ct_config;


	
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_name` LIKE  '{$user_name}'";
	$tresult = runQuery($sql,'iGet User Info');

	$user = mysql_fetch_array($tresult);

	$blogpost['title'] = "User Information";
	$blogpost['post'] = "<table>";
	$blogpost['post'] .= "<tr><th>Username:</th><td>{$user_name}</td></tr>";
	$blogpost['post'] .= "<tr><th>OpenID:</th><td>{$user['user_openid']}</td></tr>";
	$blogpost['post'] .= "<tr><th>Full Name:</th><td>{$user['user_fname']}</td></tr>";
	$blogpost['post'] .= "<tr><th>Email:</th><td>{$user['user_email']}</td></tr>";
	$blogpost['post'] .= "<tr><th>User Type:</th><td>{$ct_config['perm_access'][$user['user_type']]}</td></tr>";
	$blogpost['post'] .= "<tr><th>Master UID:</th><td>{$user['user_uid']}</td></tr>";
	$blogpost['post'] .= "</table>";
	
	return blog_style_post(&$blogpost);
}

function getUsers(){
	global $ct_config;

	$sql = "SELECT user_name, user_fname, user_type FROM `{$ct_config['blog_db']}`.`users`";
	$tresult = runQuery($sql,'iGet User Info');

	// $blogpost['title'] = "Users";
	$blogpost['post'] = "<table>";
	$blogpost['post'] .= "<tr><th>Name</th><th>User type</th></tr>\n";

	while($row = mysql_fetch_array($tresult)){
		$user_type = "user";
		if($row['user_type'] == 2) $user_type = "user+"; // not sure if '2' is used
		if($row['user_type'] == 3) $user_type = "admin";
		$blogpost['post'] .= "<tr><td><a href='user/{$row['user_name']}'>{$row['user_fname']}</a></td><td>{$user_type}</td></tr>\n";
        }

	$blogpost['post'] .= "</table>";

	return blog_style_post(&$blogpost);
}

?>
