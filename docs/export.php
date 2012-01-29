<?php

include("../lib/default_config.php");


	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_blogs` WHERE `blog_sname` = '{$_REQUEST['blog']}'";	
	$result = runQuery($sql,'Blogs');
	$blog = mysql_fetch_array($result);

	if(!checkzone(-1,0,$blog['blog_id'])){
		header("Location: {$ct_config['blog_path']}?msg=Forbidden!");
		exit();
	}


checkblogconfig($blog['blog_id']);

include("style/{$ct_config['blog_style']}/blogstyle.php");






	$blogpost = NULL;

		
		$title = $blog['blog_name'];
		$desc = $blog['blog_desc'];
		$title_url = render_link($blog['blog_sname']);
		
		$blogpost['title'] = "Export Blog";

		if(!$_REQUEST['saveblog']){
			foreach(array("blog_name","blog_desc","blog_type") as $val){
				$_REQUEST[$val] = $blog[$val];
			}
		}

$datapath = "{$ct_config['pwd']}/docs/cache/blogdumps";
if($_REQUEST['clear']){
	$_SESSION['export_key'][$_REQUEST['blog']] = NULL;
	header("Location: ".render_link("export.php?blog={$_REQUEST['blog']}"));
	exit();
}

if($_REQUEST['go']){
	$key = tempdir($datapath);
	$_SESSION['export_key'][$_REQUEST['blog']] = $key;
	$index = (int)$_REQUEST['index_post'];
	$depth = (int)$_REQUEST['depth'];
	$exec = "php {$ct_config['pwd']}/lib/scripts/exporthtml.php {$blog['blog_id']} {$index} {$depth} $key 2&>/dev/null &";
	echo `$exec`;
	sleep(1);
}


if($_SESSION['export_key'][$_REQUEST['blog']]){
	$blogpost['title'] = "Export Blog Proccessing";

	$jquery['code'] .= "\n setTimeout(function(){ updatestatus() }, 5000);
		
		function updatestatus(){
			$('#statustxt').load('".$ct_config['blog_path']."cache/blogdumps/".$_SESSION['export_key'][$_REQUEST['blog']]."/status');
			setTimeout(function(){ updatestatus() }, 5000);
		}
	
	";
	
	$blogpost['post'] .= "<b>Started:</b> <span>".date("r", @file_get_contents("{$datapath}/".$_SESSION['export_key'][$_REQUEST['blog']]."/created"))."</span>";
	$blogpost['post'] .= "<br/><b>Status:</b> <span id=\"statustxt\">".@file_get_contents("{$datapath}/".$_SESSION['export_key'][$_REQUEST['blog']]."/status")."</span>";
	
	$body .= blog_style_post(&$blogpost);
}else{
	$blogpost['post'] .= "<table>";
	$blogpost['post'] .= "<form method=\"POST\">";

	$blogpost['post'] .= "<tr><td colspan=2>To export the blog as a HTML dump please select from the following options</td></tr>";
	$blogpost['post'] .= "<tr><th width=100>Index Post</th><td><select name=\"index_post\">";
	$sql = "SELECT `bit_id`, `bit_title` FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_blog` ='{$blog['blog_id']}' AND `bit_edit` = 0 ORDER BY  `bit_datestamp` DESC ";	
	$result = runQuery($sql,'Blogs');
	
		$blogpost['post'] .= "<option value=\"-1\">All Posts</option>";
	while($bits = mysql_fetch_array($result))
		$blogpost['post'] .= "<option value=\"{$bits['bit_id']}\">{$bits['bit_title']}</option>";
	
	$blogpost['post'] .= "</select></td></tr>";
	
	$blogpost['post'] .= "<tr><th width=100>Follow Depth</th><td><select name=\"depth\">";
	for($i=1; $i<10; $i++)
		$blogpost['post'] .= "<option value=\"{$i}\">{$i}</option>";
	
	$blogpost['post'] .= "</select></td></tr>";

	$blogpost['post'] .= "<tr><td></td><td> <input type=\"submit\" name=\"go\" value=\"start export\"/></td></tr>";
	
	
	$blogpost['post'] .= "</form>";
	$blogpost['post'] .= "</table>";

	$body .= blog_style_post(&$blogpost);
	
}

include('page.php');
//// $datapath = tempdir($datapath);
?>
