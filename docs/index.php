<?php
include("../lib/default_config.php");


$title = $ct_config['blog_title'];
$desc = $ct_config['blog_desc'];
$body = "";

include("style/{$ct_config['blog_style']}/blogstyle.php");

if(!checkzone($ct_config['blog_zone']) ){

$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: Forbidden (try logging in)</div></div>";


}else{

if( is_set_not_empty('user_name', $_SESSION) )
{
//Not Logged i
if($_SESSION['user_admin']>0){
	$body .= "<div class=\" dialog dashboard_item\">";

	$body .= "<h2><small style=\"float:right;\"><a href=\"settings.php\" id=\"link_newpost\">new blog</a></small>Your Blogs</h2>";
	$sql = "SELECT * FROM `{$ct_config['blog_db']}`.`blog_blogs` WHERE `blog_user` LIKE '{$_SESSION['user_name']}' AND `blog_del` != 1;";
	$body .= "<UL>";
		$result = runQuery($sql,'Blogs');
	
			while($rowb = mysql_fetch_array($result)){
				if(checkzone($rowb['blog_zone'],0,$rowb['blog_id'])){
				if($rowb['blog_redirect'])
				$body .= "\t\t\t<li><a href=\"{$rowb['blog_redirect']}\" title=\"".$rowb['blog_desc']."\">".$rowb['blog_name']."</a>\n";
				else
				$body .= "\t\t\t<li><a href=\"".render_link($rowb['blog_sname'])."\" title=\"".$rowb['blog_desc']."\">".$rowb['blog_name']."</a>\n";
				}
			}
	$body .= "</UL>";
$body .= "</div>";
}
$body .= "<div class=\"dialog dashboard_item\">";

	$body .= "<h2><small style=\"float:right;\"><a href=\"user/{$_SESSION['user_name']}\" id=\"link_setting\">settings</a></small>Your Subscriptions</h2>";

	$sql = "SELECT * FROM `{$ct_config['blog_db']}`.`blog_sub` WHERE `sub_username` LIKE '{$_SESSION['user_name']}';";
	$result = runQuery($sql,'Blogs');

	if(!mysql_num_rows($result)){
		$body .= "You have no subscriptions yet,";
	}else{
	$bits = array();	
	$limit = 10;	
	$sql  = "SELECT  `bit_id` as uid ,`bit_id`  ,  `bit_user` ,  `bit_title` ,  `bit_content` , UNIX_TIMESTAMP(
`bit_datestamp`) AS datetime, `blog_blogs`.`blog_zone`, `blog_blogs`.`blog_name`,`blog_blogs`.`blog_sname`,`blog_blogs`.`blog_id`
FROM  `{$ct_config['blog_db']}`.`blog_bits`
INNER JOIN  `{$ct_config['blog_db']}`.`blog_blogs` ON  `blog_bits`.`bit_blog` =  `blog_blogs`.`blog_id` 
INNER JOIN  `{$ct_config['blog_db']}`.`blog_sub` ON  `blog_bits`.`bit_blog` =  `blog_sub`.`sub_blog` 
WHERE bit_edit = 0 AND `sub_username` LIKE '{$_SESSION['user_name']}'
ORDER BY `bit_datestamp` DESC Limit 20";
	$tresult = runQuery($sql,'Fetch Page Groups');
    $count = 0;
    while($row = mysql_fetch_array($tresult)){
		if(checkzone($row['blog_zone'],1,$row['blog_id'])){
			$bits[$row['datetime']] = $row;
			$count++;
			if($count>$limit) break;
		}
	}
$sql = "SELECT  `blog_com`.`com_id` as uid,  `blog_bits`.`bit_id` ,  `blog_com`.`com_user` AS  `bit_user` ,  `blog_com`.`com_title` AS  `bit_title` ,  `blog_com`.`com_cont` AS  `bit_content` , UNIX_TIMESTAMP(  `blog_com`.`com_datetime` ) AS datetime , 'comment' AS `btype` , `blog_com`.`com_edit`, `blog_blogs`.`blog_zone`, `blog_blogs`.`blog_name`,`blog_blogs`.`blog_sname`,`blog_blogs`.`blog_id`
FROM  `{$ct_config['blog_db']}`.`blog_bits` 
INNER JOIN  `{$ct_config['blog_db']}`.`blog_com` ON  `blog_bits`.`bit_id` =  `blog_com`.`com_bit` 
INNER JOIN  `{$ct_config['blog_db']}`.`blog_blogs` ON  `blog_bits`.`bit_blog` =  `blog_blogs`.`blog_id`
INNER JOIN  `{$ct_config['blog_db']}`.`blog_sub` ON  `blog_bits`.`bit_blog` =  `blog_sub`.`sub_blog` 
WHERE bit_edit = 0 AND `sub_username` LIKE '{$_SESSION['user_name']}' AND `blog_com`.`com_edit` = 0
ORDER BY  `com_datetime` DESC Limit 20";	
	$tresult = runQuery($sql,'Fetch Page Groups');
    $count = 0;
    while($row = mysql_fetch_array($tresult)){
		if(checkzone($row['blog_zone'],1,$row['blog_id'])){
		if($row['datetime'] > 1){
			$row['bit_title'] = "Comment: ".$row['bit_title'];
			$bits[$row['datetime']] = $row;
		}
		$count++;
		if($count>$limit) break;
		}
	}

	krsort($bits);
	$body .= "<ul>";
	$count = 0;
	foreach($bits as $row){
	$row['url'] = render_blog_link($row['bit_id'],1);
		if($row['btype']=='comment') $row['url'].= "#".$row['uid'];
	$body .= "<li><a href=\"{$row['url']}\">{$row['bit_title']}</a> by <a href=\"user/{$row['bit_user']}\">".get_user_info($row['bit_user'],'name')."</a><br />";
	$body .= "<span class=\"timestampComment\">".date("jS F Y @ H:i",$row['datetime'])." from {$row['blog_name']}</span></li>";
	$count++;
		if($count>$limit) break;

	}
		
	$body .= "</ul>";
		
		$auth_uid = array('uid'=>$_SESSION['user_uid'], 'subscription' => $_SESSION['user_name']);
		$body .= "<div style=\"text-align: right;\">This as a <a href=\"".render_link('feeds',$auth_uid)."\">RSS Feed</a> (<a href=\"".render_link('feeds',$auth_uid)."?withcomments\">With Comments</a>)</div>";
	}	
	
$body .= "</div>";

}







if( is_set_not_empty('msg', $_REQUEST) )
	newMsg($_REQUEST['msg']);
//$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: {$_REQUEST['msg']} </div></div>";

//get Blogs Type
$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_types` ORDER BY  `blog_types`.`type_order` ASC ";
$tresult = runQuery($sql,'Fetch Page Groups');
    
    while($row = mysql_fetch_array($tresult)){
		$haspart = 0;
		$part = "\t<div class=\"containerPost\">\n";
	
		$part .= "\t\t<div class=\"postTitle\">".$row['type_name']."</div>\n";
	
		$part .= "\t\t<span class=\"timestamp\"><small>".$row['type_desc']."</small></span><div class=\"postText\">\n<UL>";

		$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_blogs` WHERE `blog_type` = ".$row['type_id']." AND `blog_del` != 1;";
		$result = runQuery($sql,'Blogs');

		while($rowb = mysql_fetch_array($result)){
				

				if(checkzone($rowb['blog_zone'],0,$rowb['blog_id'])){
					if($rowb['blog_redirect'])
						$part .= "\t\t\t<li><a href=\"{$rowb['blog_redirect']}\" title=\"".$rowb['blog_desc']."\">".$rowb['blog_name']."</a>";
					else
						$part .= "\t\t\t<li><a href=\"".render_link($rowb['blog_sname'])."\" title=\"".$rowb['blog_desc']."\">".$rowb['blog_name']."</a>";

					if($rowb['blog_zone']!=0)
						$part .= " <img src=\"inc/lock.gif\">"; 
				
					$part .= "\n";
					$haspart = 1;
				}
		}
		$part .= "</UL>\t\t</div>\n\t</div>\n";

	
		if($haspart) $body .= $part;
	}
	
}



//set RSS!
if( is_set_not_empty('user_uid', $_SESSION) ){
	$auth_uid = array('uid'=>$_SESSION['user_uid']);
}
if( !isset($auth_uid) ) { $auth_uid = NULL; }
$rss_feed[] = array("type" => "application/rss+xml", "title" => "".strip_tags($ct_config['blog_title']).": RSS 2.0", "url" => render_link('feeds',$auth_uid));
$rss_feed[] = array("type" => "application/rss+xml", "title" => "".strip_tags($ct_config['blog_title'])." with comments: RSS 2.0", "url" => render_link('feeds',$auth_uid)."?withcomments");

include('page.php');
?>
