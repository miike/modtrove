<?php

include("../lib/default_config.php");

$pathinfo = $_REQUEST['uri'];

if($pathinfo){
	$pathinfo = explode("/",$pathinfo);
	if(array_search($pathinfo[0], array("uid"))===FALSE)
		$request['blog_sname'] = array_shift($pathinfo);
		while($request[array_shift($pathinfo)] = addslashes(array_shift($pathinfo)));

}

$_REQUEST['uri'] = "search/".$_REQUEST['uri'];

///Load Blog info
if($request['blog_sname']){

	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_blogs` WHERE `blog_sname` = '{$request['blog_sname']}'";
	$result = runQuery($sql,'Blogs');
	$blog = mysql_fetch_array($result);
	$blog_id = $blog['blog_id'];
	$title = $blog['blog_name'];
	$desc = $blog['blog_desc'];
	$title_url = render_link($blog['blog_sname']);

	checkblogconfig($blog_id);
	
	$where = "WHERE `bit_blog` = ".$blog_id ." AND bit_edit = 0";

}
if(!$blog_id && $request['blog_sname']){
	set_http_error(404, $_REQUEST['uri']);
	exit();
}

if(!$blog_id){
	$title = $ct_config['blog_title'];
	$desc = $ct_config['blog_desc'];
	$title_url = $ct_config['blog_path'];
	$where = "WHERE bit_edit = 0";
}


if($request['uid'] && ($request['uid']!=$_SESSION['user_uid'])){
	login_with_uid($request['uid']);
}

if($_SESSION['user_name'] && $request['subscription'] && ($request['subscription'] == $_SESSION['user_name'])){
	
	$where = " INNER JOIN  `{$ct_config['blog_db']}`.`blog_sub` ON  `blog_bits`.`bit_blog` =  `blog_sub`.`sub_blog` 
$where AND `sub_username` LIKE '{$_SESSION['user_name']}' ";
	
}


if($blog_id){
	if(!checkzone($blog['blog_zone'],0,$blog['blog_id']) || !checkzone($ct_config['blog_zone'])){
		header("Location: {$ct_config['blog_path']}?msg=Forbidden!");
		exit();
	}

	$channel_info = '
	<title>'.$blog['blog_name'].' - '.strip_tags($ct_config['blog_title']).'</title>
	<link>'.render_link($blog['blog_sname']).'</link>
	<description>'.$blog['blog_desc'].' on '.strip_tags($ct_config['blog_title']).'</description>
	<managingEditor>'.get_user_info($blog['blog_user'],'email')."(".get_user_info($blog['blog_user'],'name').")".'</managingEditor>
	<webMaster>'.get_user_info($ct_config['blog_webmaster'],'email')."(".get_user_info($ct_config['blog_webmaster'],'name').")".'</webMaster>
	<lastBuildDate>'.date('r').'</lastBuildDate>
	<generator>chemBlog</generator>';

}else{
	if(!checkzone($ct_config['blog_zone']) ){
	header("Location: /projects/blog/index.php?msg=Forbidden!");
		exit();
	}

				$channel_info = '
	<docs>http://backend.userland.com/rss092</docs>
	<title>'.strip_tags($ct_config['blog_title']).'</title>
	<link>'.render_link('').'</link>
	<description>'.strip_tags($ct_config['blog_desc']).'</description>
	<managingEditor>'.get_user_info($ct_config['blog_contact'],'email')."(".get_user_info($ct_config['blog_contact'],'name').")".'</managingEditor>
	<webMaster>'.get_user_info($ct_config['blog_webmaster'],'email')."(".get_user_info($ct_config['blog_webmaster'],'name').")".'</webMaster>
	<lastBuildDate>'.date('r').'</lastBuildDate>
	<generator>chemBlog</generator>';

}

$limit = 50;

header("Content-Type: text/xml");
?><?php '<?xml version="1.0" encoding="utf-8" ?>'?>

<rss version="2.0">
<channel><?php echo $channel_info?>

<?php
$sql = "SELECT  `bit_id` as uid ,`bit_id`  ,  `bit_user` ,  `bit_title` ,  `bit_content` , UNIX_TIMESTAMP(
`bit_datestamp`) AS datetime, `blog_blogs`.`blog_zone`, `blog_blogs`.`blog_id`
FROM  `{$ct_config['blog_db']}`.`blog_bits` 
INNER JOIN  `{$ct_config['blog_db']}`.`blog_blogs` ON  `blog_bits`.`bit_blog` =  `blog_blogs`.`blog_id` 
$where
ORDER BY  `bit_datestamp` DESC Limit 200 ";

$tresult = runQuery($sql,'Fetch Page Groups');
    $count = 0;
    while($row = mysql_fetch_array($tresult)){
	if(checkzone($row['blog_zone'],1,$row['blog_id'])){
			$bits[$row['datetime']] = $row;
			$count++;
			if($count>$limit) break;
	}
	}
if(isset($_REQUEST['withcomments'])){
$sql = "SELECT  `blog_com`.`com_id` as uid,  `blog_bits`.`bit_id` ,  `blog_com`.`com_user` AS  `bit_user` ,  `blog_com`.`com_title` AS  `bit_title` ,  `blog_com`.`com_cont` AS  `bit_content` , UNIX_TIMESTAMP(  `blog_com`.`com_datetime` ) AS datetime , 'comment' AS `btype` , `blog_com`.`com_edit`, `blog_blogs`.`blog_zone`, `blog_blogs`.`blog_id`
FROM  `{$ct_config['blog_db']}`.`blog_bits` 
INNER JOIN  `{$ct_config['blog_db']}`.`blog_com` ON  `blog_bits`.`bit_id` =  `blog_com`.`com_bit` 
INNER JOIN  `{$ct_config['blog_db']}`.`blog_blogs` ON  `blog_bits`.`bit_blog` =  `blog_blogs`.`blog_id`
$where AND `blog_com`.`com_edit` = 0
ORDER BY  `com_datetime` DESC Limit 200";



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

}

krsort($bits);

$count = 0;
foreach($bits as $row){

		$row['url'] = render_blog_link($row['bit_id'],1);
		if($row['btype']=='comment') $row['url'].= "#".$row['uid'];
?>

	<item>
	<title><?php echo htmlspecialchars($row['bit_title'])?></title>
	<author><?php echo get_user_info($row['bit_user'],'email')." (".get_user_info($row['bit_user'],'name').")"?></author>
	<guid isPermaLink="true"><?php echo $row['url']?></guid>
	<link><?php echo $row['url']?></link>
	<pubDate><?php echo date('r',$row['datetime'])?></pubDate>
	<description><?php echo htmlspecialchars(bbcode($row['bit_content']))?></description>
</item>

<?php	$count++;
	if($count>$limit) break;

 } 
?>
</channel>
</rss>
