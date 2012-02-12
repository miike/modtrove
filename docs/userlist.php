<?php
//code for user listing
include("../config.php");
include("../lib/functions_database.php");
include("../lib/functions_blog.php");
include("../lib/functions.php");

include("style/{$ct_config['blog_style']}/blogstyle.php");

$truncate = true; //truncates long usernames (openIDs)
$limit = 30; //truncating limit (in characters)

if ($_SESSION['user_admin'] < $ct_config['viewlevel']){ //if user has insufficient permissions, or page is disabled
	header("Location: " . $ct_config['blog_url']);
}

if ($ct_config['userlistenabled'] == false){
	newMsg("You do not have sufficient permissions to view this page", "error");
	header("Location: " . $ct_config['blog_path']);
}


$blogpost['title'] = "Modtrove User List";
$blogpost['post'] = ""; //set empty
$query = "SELECT `user_name`, `user_fname`, `user_type` FROM `users` WHERE `user_enabled` = 1";
$result = runQuery($query, "retrieve user list");
//output the results of the query

$blogpost['post'] .= "<table class='sorted'>";
$blogpost['post'] .= "<tr><th>Username</th><th>Name</th><th>User type</th></tr>";
while ($row = mysql_fetch_assoc($result)){
	$blogpost['post'] .= "<tr><td>" . truncate($row['user_name'], $limit) . "</td><td>" . $row['user_fname'] . "</td><td>" . castAuthority($row['user_type']) . "</td></tr>\n";
}

$blogpost['post'] .= "</table>";


$body .= blog_style_post(&$blogpost);
include("page.php");

function castAuthority($useradmin){ //converts permission integer into a role
	switch($useradmin){
		case 0:
			return "View user";
			break;
		case 1:
			return "Standard user";
			break;
		case 2:
			return "Moderator";
			break;
		case 3:
			return "Administrator";
			break;
		default:
			return "Unknown";
	}
}

function truncate($string, $limit){
	
	if (strlen($string) < $limit || $truncate == false){
		return $string;
	}
	else{
		return substr($string, 0, $limit) . '&hellip;';
	}
}

?>
