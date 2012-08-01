<?php


if($_REQUEST['logout']){
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
	if($_SESSION['perm'][$perm]){
		return 1;
	}else{
		require_once('nusoap-0/lib/nusoap.php');
			//input params
		$param = array('user' => base64_encode($_SESSION['user_name']), 'host' => base64_encode('chemtools'), 'service'=>base64_encode('chemtools'), 'perm' => base64_encode($perm));
		$clienta = new nu_soap_client('http://chemtools.chem.soton.ac.uk/login/chkperm.php?wsdl', true);
		$result = $clienta->call('chkperm', $param);
		if($result['result']==1){
			$_SESSION['perm'][$perm] = 1;
			return 1;
		}else{
			return 0;
		}
	}
}

function check_remembered(){

if($usern = $_COOKIE['user_name']){

require_once('nusoap-0/lib/nusoap.php');
$param = array('user' => base64_encode($usern), 'host' => base64_encode('chemtools'),    'service'=>base64_encode('chemtools'), 'password' => base64_encode(''), 'soton_ldap_only' => '0','lookuponly' => '1');
$client = new nu_soap_client('http://chemtools.chem.soton.ac.uk/login/index.php?wsdl', true);
$result = $client->call('chemlogin', $param);

	if($result['result']==1 && md5($ct_config['rememberme']['salt'].$result['user'].$result['access'].$result['email'].$result['uid'])){

                $_SESSION['user_name'] = $result['user'];
                $_SESSION['user_fname'] = $result['name'];
                $_SESSION['user_admin'] = $result['access'];
                $_SESSION['user_email'] = $result['email'];

	}else{
		return 'Error';
	}
	

}


}


function get_user_info($usern,$field = 0){

global $ct_config;

if($_SESSION['user_info'][$usern]['set'] < (time()-3600)){
	$skipsoap = 0;
	if($ct_config['usercache']['enable']){
		$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_name` LIKE  '{$usern}'";
		$tresult = runQuery($sql,'iGet User Info');

		if(!mysql_num_rows ( $tresult )){
			$action = "insert";
		}else{
			$user = mysql_fetch_array($tresult);
			if(strtotime($user['user_cache'])<(time()-$ct_config['usercache']['limit'])){
					$action = "update";
			}else{
				$_SESSION['user_info'][$usern] = array("user"=>$user['user_name'],"name"=>$user['user_fname'],"email"=>$user['user_email'],"uid"=>$user['user_uid'],"image"=>$user['user_image'],"result"=>1,"set"=>time());
				$skipsoap = 1;
			}
		}
	}

	
	if(!$skipsoap){
	
	require_once('nusoap-0/lib/nusoap.php');
	$param = array('user' => base64_encode($usern), 'host' => base64_encode($ct_config['soap_host']),    'service'=>base64_encode(''), 'password' => base64_encode(''), 'soton_ldap_only' => '0','lookuponly' => '1');

	$client = new nu_soap_client($ct_config['soap_login'].'index.php?wsdl', true);
	$result = $client->call('chemlogin', $param);

		if($result['result']==1){

	                $_SESSION['user_info'][$usern] = $result;
					$_SESSION['user_info'][$usern]['set'] = time();
				
		}else{
			if(isset($user['user_name']))
				$_SESSION['user_info'][$usern] = array("user"=>$user['user_name'],"name"=>$user['user_fname'],"email"=>$user['user_email'],"uid"=>$user['user_uid'],"image"=>$user['user_image'],"result"=>1,"set"=>time());
			else
				return 'Error';
		}
		
	
		
	if($ct_config['usercache']['enable'] && $action !== false){
		switch($action){
			case "update":
				$sql = "UPDATE  `{$ct_config['blog_db']}`.`users` SET  `user_fname` =  '".addslashes($result['name'])."',
				`user_email` =  '".addslashes($result['email'])."', `user_uid` =  '".addslashes($result['uid'])."',
				`user_cache` = NOW( ) WHERE  `users`.`user_name` = '{$usern}';";
			break;
			case "insert":
			echo $sql = "INSERT INTO `{$ct_config['blog_db']}`.`users` (`user_name`, `user_fname`,	`user_email`, `user_uid`,  `user_cache`) 
					VALUES ('{$usern}','".addslashes($result['name'])."','".addslashes($result['email'])."', '".addslashes($result['uid'])."', NOW());";
			break;
		}
		runQuery($sql,'iGet User Info');
	}
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


if(isset($_SESSION['user_name'])){
        return ' Current user: <a class="with_user" href="'.render_link('',array('user' => $_SESSION['user_name'])).'">'.$_SESSION['user_fname'].'</a> | <a href="'.$ct_config['blog_path'].$_SERVER['LABTROVE_REQUEST_PATH'].'?logout=1">Log Out</a>';
}else{
	global $jquery;
	$jquery['set'] = 1;
	
	if(!$_REQUEST['flogin']){
        $ret = '<div id="loginButton"> <a class="with_user" href="#" onclick="$(\'#loginButton\').hide();$(\'#loginBox\').show(); return false;">Login</a> </div>';
		$display = "none";
	}else{
		$display = "block";
	}
        if($_REQUEST['flogin'] == 1) {
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

$usern = ereg_replace( "[^A-Za-z0-9]", "", $_REQUEST['usern']);
$pass =  $_REQUEST['pass'];

if(strlen($pass)){
require_once('nusoap-0/lib/nusoap.php');

//input params
$param = array('user' => base64_encode($usern), 'host' => base64_encode($ct_config['soap_host']),    'service'=>base64_encode('chemtools'), 'password' => base64_encode($pass), 'soton_ldap_only' => '0', 'ip' => $_SERVER['REMOTE_ADDR']);

$client = new nu_soap_client($ct_config['soap_login'].'index.php?wsdl', true);
$result = $client->call('chemlogin', $param);

	if($result['result']==1){
				if($_REQUEST['remember']){
					setcookie("user_name", $result['user'], time()+(3600*24*$ct_config['rememberme']['time']),'/');	
					setcookie("user_hash", md5($ct_config['rememberme']['salt'].$result['user'].$result['access'].$result['email'].$result['uid']), time()+(3600*24*$ct_config['rememberme']['time']),'/');
				}
                $_SESSION['user_name'] = $result['user'];
                $_SESSION['user_fname'] = $result['name'];
                $_SESSION['user_admin'] = $result['access'];
                $_SESSION['user_email'] = $result['email'];
                $_SESSION['user_uid'] = $result['uid'];
			   header("Location: $rurl"); //* Redirect browser 
			   if(isset($_SESSION['labtrove']['turl']))
					unset($_SESSION['labtrove']['turl']);
        }else{
              header("Location: $rurl{$ct_config['var_prefix']}flogin=1&loginbox=1"); /// Redirect browser /
        }
}else{
             header("Location: $rurl{$ct_config['var_prefix']}flogin=1&loginbox=1"); /// Redirect browser /
}
}


exit();
}


function login_with_uid($uid){
		global $ct_config;
	require_once('nusoap-0/lib/nusoap.php');
	//input params
	$param = array('user' => "", 'host' => base64_encode($ct_config['soap_host']),    'service'=>base64_encode('chemtools'), 'password' => "", 'soton_ldap_only' => '0', 'uid' => base64_encode($uid), 'ip' => $_SERVER['REMOTE_ADDR']);

	$client = new nu_soap_client($ct_config['soap_login'].'/index.php?wsdl', true);
$result = $client->call('chemlogin', $param);

	if($result['result']==1){
		        $_SESSION['user_name'] = $result['user'];
                $_SESSION['user_fname'] = $result['name'];
                $_SESSION['user_admin'] = $result['access'];
                $_SESSION['user_email'] = $result['email'];
                $_SESSION['user_uid'] = $result['uid'];
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
	
	global $ct_config;

	$blogpost['title'] = "User Information";
	$blogpost['post'] = "<table>";
	$blogpost['post'] .= "<tr><th>Username:</th><td>{$_SESSION['user_name']}</td></tr>";
	$blogpost['post'] .= "<tr><th>Full Name:</th><td>{$_SESSION['user_fname']}</td></tr>";
	$blogpost['post'] .= "<tr><th>Email:</th><td>{$_SESSION['user_email']}</td></tr>";
	$blogpost['post'] .= "<tr><th>User Type:</th><td>{$ct_config['perm_access'][$_SESSION['user_admin']]}</td></tr>";
	$blogpost['post'] .= "<tr><th>Your Master UID:</th><td>{$_SESSION['user_uid']}</td></tr>";
	$blogpost['post'] .= "</table>";
	
	return blog_style_post(&$blogpost);
}

?>