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

if($usern = $_COOKIE['user_name']){
	$user = get_user_info($usern);
 
	if($result['result']==1 && md5("chemtools".$user['uid'].$user['user']) ==$_COOKIE['user_hash']){

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

                $_SESSION['user_info'][$usern] = array("user"=>$user['user_name'],"name"=>$user['user_fname'],"email"=>$user['user_email'],"uid"=>$user['user_uid'],"image"=>$user['user_image'],"result"=>1,"set"=>time());
		}else{

putenv("LDAPTLS_REQCERT=never");
$ds=ldap_connect($ct_config['ldap_url']);	// Is now secure
	if ($ds) {
			// Perform an anonymous bind which will allow us to search for the user
			$r=ldap_bind($ds,$ct_config['ldap_bind'],$ct_config['ldap_bind_pass']);
			if ( !$r ){ 
				error_log("[".date("d-M-Y H:i:s")."] ".$user." Anonymous Bind Failed?!! $str\n",3, $ct_config['ldap_log']);
				return 'Error';
			}

			$st_search="cn=".$usern;
			$sr=@ldap_search($ds,"{$ct_config['ldap_scope']}","$st_search");
			$info = @ldap_get_entries($ds, $sr);
			
			if($info["count"]==0) {
				return 'Error';
			}else{
			$userinfo = $info[0];
				 $_SESSION['user_info'][$usern] = array("user"=>$userinfo[$ct_config['ldap_nmap']['uname']][0],"name"=>$userinfo[$ct_config['ldap_nmap']['dname']][0],"email"=>$userinfo[$ct_config['ldap_nmap']['email']][0],"result"=>1,"set"=>time());
		
			}
		}
	}
}
if($field){
	return $_SESSION['user_info'][$usern][$field];
}else{
	$_SESSION['user_info'][$usern];
}


}


/*(function renlogin_blog(){

global $ct_config;

if(!$_REQUEST['loginbox']){
if(isset($_SESSION['user_name'])){
        return '<span class="with_user">  Current user: <a href="'.render_link('',array('user' => $_SESSION['user_name'])).'">'.$_SESSION['user_fname'].'</a> | <a href="/'.$_REQUEST['uri'].'?logout=1">Log Out</a> </span>';
}else{
        return '<img src="/inc/user.gif" height=11> <a href="/'.$_REQUEST['uri'].$ct_config['var_prefix'].'loginbox=1">Login</a>';
}
}else{

        if($_REQUEST['flogin'] == 1) {
                        $ret = 'Invalid username or password! &nbsp;<br />';
        }
        $ret .= '<form action="/login.php" method="post"> <input type="hidden" name="rew" value="'.$ct_config['var_prefix'].'"><input type="hidden" name="turl" value="/'.$_REQUEST['uri'].'"> Username <input type="text" size="7" name="usern" class="main_input" tabindex="1" align="absmiddle"> Password <input type="password" name="pass" size="10" tabindex="2" align="absmiddle" class="main_input"> Remember Me <input name="remember" type="checkbox" value="true"><input type="submit" name="loginsub" value=">" class="main_submit">';

        return $ret;
}

}
*/

function renlogin_blog(){

global $ct_config;


if(isset($_SESSION['user_name'])){
        return ' Current user: <a class="with_user" href="'.render_link('',array('user' => $_SESSION['user_name'])).'">'.$_SESSION['user_fname'].'</a> | <a href="/'.$_REQUEST['uri'].'?logout=1">Log Out</a>';
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
        $ret .= '<div id="loginBox" style="display: '.$display.'"><form action="/login.php" method="post"> <input type="hidden" name="rew" value="'.$ct_config['var_prefix'].'"><input type="hidden" name="turl" value="/'.$_REQUEST['uri'].'"> Username <input type="text" size="7" name="usern" class="main_input" tabindex="1" align="absmiddle"> Password <input type="password" name="pass" size="10" tabindex="2" align="absmiddle" class="main_input"> Remember Me <input name="remember" type="checkbox" value="true"><input type="submit" name="loginsub" value=">" class="main_submit"></form></div>';

        return $ret;
}

}

function do_login(){

global $ct_config;
if($_REQUEST['turl']){
$rurl=$_REQUEST['turl'];
}else{
$rurl="/";
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

$result = do_ldap($usern,$pass, &$userinfo);

			if($result){

				$user['user_name'] = $userinfo[$ct_config['ldap_nmap']['uname']][0];
				$user['user_fname'] = $userinfo[$ct_config['ldap_nmap']['dname']][0];
				$user['user_email'] = $userinfo[$ct_config['ldap_nmap']['email']][0];
				$user['user_uidsalt'] = $userinfo[$ct_config['ldap_nmap']['uidsalt']][0];
				
				$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`users` WHERE  `user_name` LIKE '".addslashes($user['user_name'])."' LIMIT 1;";
				$tresult = runQuery($sql,'iGet User Info');
				if(mysql_num_rows (  $tresult ) ){
			$user_sql = mysql_fetch_array($tresult, MYSQL_ASSOC);
			$user = array_merge($user,$user_sql);
			$sql = "UPDATE  `{$ct_config['blog_db']}`.`users` SET  `user_fname` =  '".addslashes($user['user_fname'])."', `user_email` =  '".addslashes($user['user_email'])."', `user_image` = '".addslashes($user['user_image'])."' WHERE  `users`.`user_id` ={$user['user_id']} LIMIT 1 ;";	
				}else{
			$user['user_type'] = 1;

			$user['user_uid'] = md5($user['user_uidsalt']);
			$sql  = "INSERT INTO  `{$ct_config['blog_db']}`.`users` (`user_id` ,`user_name` , `user_openid`, `user_fname` ,`user_email`, `user_image`, `user_type` ,`user_enabled` ,`user_uid` ,`user_notes`) VALUES ( NULL ,  '".addslashes($user['user_name'])."', '".addslashes($user['user_openid'])."', '".addslashes($user['user_fname'])."',  '".addslashes($user['user_email'])."', '".addslashes($user['user_image'])."',  '1',  '1',  '".addslashes($user['user_uid'])."',  '".date("Y-m-d H:i:s").": Account Added\n' ); ";
				}
			runQuery($sql,'iGet User Info');
				if($_REQUEST['remember']){
					setcookie("user_name", $user['user_name'], time()+(3600*24*30),'/');	
					setcookie("user_hash", md5("chemtools".$user['user_uid'].$user['user_name']), time()+(3600*24*30),'/');
				}
              	$_SESSION['user_name'] = $user['user_name'];
			 	$_SESSION['user_fname'] = $user['user_fname'];
            	$_SESSION['user_email'] = $user['user_email'];
            	$_SESSION['user_uid'] = $user['user_uid'];
	            $_SESSION['user_admin'] = $user['user_type'];
			   header("Location: $rurl"); //* Redirect browser 
        }else{
              header("Location: $rurl{$ct_config['var_prefix']}flogin=1&loginbox=1"); /// Redirect browser /
        }
}else{
             header("Location: $rurl{$ct_config['var_prefix']}flogin=1&loginbox=1"); /// Redirect browser /
}
}

}



function login_with_uid($uid){
	global $ct_config;
	
	$sql = "SELECT * FROM  `users` WHERE  `user_uid` LIKE  '{$uid}'";
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

function do_ldap($user,$pass="somethingnonempty", &$userinfo, $str = "")
{
global $ct_config;
putenv("LDAPTLS_REQCERT=never");

$ds=ldap_connect($ct_config['ldap_url']);	// Is now secure
			if ($ds) {
			// Perform an anonymous bind which will allow us to search for the user
			$r=ldap_bind($ds,$ct_config['ldap_bind'],$ct_config['ldap_bind_pass']);
			if ( !$r ){ 
				error_log("[".date("d-M-Y H:i:s")."] ".$user." Anonymous Bind Failed?!! $str\n",3, $ct_config['ldap_log']);
				return false;
			}

			$st_search="cn=".$user;
			$sr=@ldap_search($ds,"{$ct_config['ldap_scope']}","$st_search");
			$info = @ldap_get_entries($ds, $sr);
			
			if($info["count"]==0) {
				// User was not found in the directory, return an "invalid login"
				error_log("[".date("d-M-Y H:i:s")."] ".$user. " Invalid Username $str \n",3, $ct_config['ldap_log']);
				return false;
			}else{
				if($ct_config['ldap_member']){
					for($i=0;$i<$info[0]["memberof"]["count"];$i++){
						if(stristr($info[0]["memberof"][$i],$ct_config['ldap_member'])!==false)
							$foundingrp = 1;	
					}
				}else{
					$foundingrp = 1;
				}
				if(!$foundingrp){
					error_log("[".date("d-M-Y H:i:s")."] ".$user. " User not in group $str\n",3, $ct_config['ldap_log']);
					return false;
				}
				// Now perform a bind as the user in question using the supplied password
				$dn=$info[0]["dn"];
				if(@ldap_bind($ds, $dn, $pass)) {
					// Bind succeeded, so password is ok
					// Extract PINumber
					//$StudentNumber=$info[0]["employeeid"][0];
					error_log("[".date("d-M-Y H:i:s")."] ".$user. " Bind Succeeded $str\n",3, $ct_config['ldap_log']);
					$userinfo = $info[0];
					return true;
				} else {
					// Fail
					error_log("[".date("d-M-Y H:i:s")."] ".$user. " Bind failed (invalid pass?) $str\n",3, $ct_config['ldap_log']);
					return false;
				}
				ldap_close($ds);
			}

			}
	
}


function get_all_users(){
	global $ct_config;
putenv("LDAPTLS_REQCERT=never");

$ds=ldap_connect($ct_config['ldap_url']);	// Is now secure
			if ($ds) {
			// Perform an anonymous bind which will allow us to search for the user
			$r=ldap_bind($ds,$ct_config['ldap_bind'],$ct_config['ldap_bind_pass']);
			if ( !$r ){ 
				error_log("[".date("d-M-Y H:i:s")."] ".$user." Anonymous Bind Failed?!! $str\n",3, $ct_config['ldap_log']);
				return false;
			}

			
			$sr=@ldap_search($ds,"{$ct_config['ldap_member']}","cn=*");
			$info = @ldap_get_entries($ds, $sr);
			$users=$info[0]['member'];
			for($i=0;$i<$users['count'];$i++){
				$pos = strpos($users[$i],",");
				$user[] = substr($users[$i],3,($pos-3));
			}
			return $user;
}

}

$config['users_add_list'] = true;

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