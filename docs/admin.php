<?php

	include("../lib/default_config.php");
	
	include("style/{$ct_config['blog_style']}/blogstyle.php");

	$title_url = "admin.php";

	if($_SESSION['user_admin']<3){
		header("Location: {$ct_config['blog_path']}?msg=Forbidden!&turl=/".urlencode($_REQUEST['uri']));
		exit();
	}
	

	$title = 'Admin';
	$desc = "";
	$body = "";

	$body = "<h2>Blog Users</h2>";
	
	if( function_exists(getUsers) ){
		$body .= getUsers();
	}
	
include('page.php');

?>
