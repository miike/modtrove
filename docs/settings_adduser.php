<?php

include("../lib/default_config.php");


checkblogconfig($_SESSION['blog_id']);


$body .= "\t<div style=\"background: white; padding:10px; margin-top:0px;\">\n";

if($config['users_add_list']){
$body .= "<h2>Select User</h2>";

$users = get_all_users();
$body .= "<ul>";
foreach($users as $user){
	$body .= "<li><a href=\"#\" onclick=\"oField = window.opener.location.href=window.opener.location.href+'&zone={$_REQUEST['zone']}&auser={$user}';window.close(); return false;\">".get_user_info($user,'name')." ({$user})</a></li>";
}

$body .= "</ul>";

}else{
	$body .= add_users_to_zone_page();
}


$body .= "</div>";


$minipage = 1;
include('page.php');
?>