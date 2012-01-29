<?php
//edit a comment
function add_com($bit_id, $com_title, $com_content){

global $ct_config;		
		$sql = "INSERT INTO  `{$ct_config['blog_db']}`.`blog_com` (  `com_id` ,  `com_bit` ,  `com_user` ,  `com_title` ,  `com_cont` ,  `com_datetime` ,  `com_del` ) 
VALUES ( 0 ,  '".$bit_id."',  '".$_SESSION['user_name']."',  '".trim($com_title)."',  '".trim($com_content)."', NOW( ) ,  '0'
);";	runQuery($sql,'Blogs');

$new_id = mysql_insert_id();

		$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_com` SET `com_id` =  $new_id WHERE  `com_rid` = ".$new_id." LIMIT 1";	runQuery($sql,'Blogs');

global $blog_id;
if($ct_config['blog_enmsg'])
	new_com_post($new_id, $blog_id);		

updatesidecache();

return $new_id;


}

//edit a comment
function edit_com($com_id,$com_editwhy, $com_title = NULL, $com_content = NULL){

global $ct_config;


$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_com` WHERE `com_id` = $com_id and com_edit=0";
$tresult = runQuery($sql,'Fetch old edit');
$row = mysql_fetch_array($tresult);

if(!$com_title){
	$com_title = addslashes($row['com_title']);
}
if(!$com_content){
	$com_content = addslashes($row['com_cont']);
}

$sql = "INSERT INTO  `{$ct_config['blog_db']}`.`blog_com` (  `com_id` ,  `com_bit` ,  `com_user` ,  `com_title` ,  `com_cont` ,  `com_datetime` ,  `com_del`, `com_edit`, `com_edituser`, `com_editwhy` ) 
VALUES ( $com_id ,  '".$row['com_bit']."',  '".$row['com_user']."',  '". trim($com_title)."',  '".trim($com_content)."', '".$row['com_datetime']."' ,  '0' , '0' , '', '' );";

runQuery($sql,'Insert ');
$new_id = mysql_insert_id();

		$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_com` SET  `com_edit` =  $new_id, `com_edituser` =  '".$_SESSION['user_name']."' , `com_editwhy` = '".trim($com_editwhy)."'  WHERE  `com_rid` = ".$row['com_rid']." LIMIT 1 ";
	runQuery($sql,'Blogs');

updatesidecache();

return $new_id;

}
function edit_blog($bit_id,$bit_editwhy, $bit_title = NULL, $bit_content = NULL, $bit_meta = NULL, $bit_group = NULL){

global $ct_config;
$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE `bit_id` = $bit_id AND bit_edit = 0";
$tresult = runQuery($sql,'Fetch old edit');
$row = mysql_fetch_array($tresult);

if(!$bit_title){
	$bit_title = addslashes($row['bit_title']);
}
if(!$bit_content){
	$bit_content = addslashes ($row['bit_content']);
}
if(!$bit_meta){
	$bit_meta = addslashes($row['bit_meta']);
}
if(!$bit_group){
	$bit_group = addslashes($row['bit_group']);
}
	$uri = $row['bit_uri'];


 $sql = "INSERT INTO  `{$ct_config['blog_db']}`.`blog_bits` (  `bit_id` ,  `bit_user` ,  `bit_title` ,  `bit_content` ,  `bit_meta` ,  `bit_datestamp` ,  `bit_timestamp` ,  `bit_group` ,  `bit_blog` ,  `bit_edit` , `bit_edituser`,  `bit_editwhy`, `bit_uri` ) 
VALUES (
$bit_id ,  '".$row['bit_user']."',  '".trim($bit_title)."',  '".trim($bit_content)."',  '".trim($bit_meta)."', '".$row['bit_datestamp']."', NOW( ) ,  '".trim($bit_group)."',  '".$row['bit_blog']."',  '0',  '' , '' , $uri);";

runQuery($sql,'Insert ');
$new_id = mysql_insert_id();

$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_rid` = $new_id";
		$tresult = runQuery($sql,'Fetch md5 Groups');
    	$rowa = mysql_fetch_array($tresult);

		$key = md5($rowa['bit_rid'].$rowa['bit_rid'].$rowa['bit_user'].$rowa['bit_title'].$rowa['bit_content'].$rowa['bit_meta'].$rowa['bit_datestamp'].$rowa['bit_timestamp'].$rowa['bit_group'].$rowa['bit_blog']);
	
		$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_bits` SET  `bit_md5` = '$key' WHERE  `bit_rid` = $new_id";
		runQuery($sql,'Blogs');

		$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_bits` SET  `bit_edit` =  $new_id, `bit_edituser` =  '".$_SESSION['user_name']."' , `bit_editwhy` = '".trim($bit_editwhy)."'  WHERE  `bit_rid` = ".$row['bit_rid']." LIMIT 1
";	runQuery($sql,'Blogs');

$preg = array(
//[data]
	'/\[blog(?::\w+)?\](\d+)\[\/blog(?::\w+)?\]/sie'=>"clear_blog_cache(\\1)"
);
$message = @preg_replace(array_keys($preg), array_values($preg), $bit_content);


//$sql = "UPDATE  `blog_com` SET  `com_bit` = $new_id WHERE  `com_bit` = $bit_id";
//runQuery($sql,'Blogs');

updatesidecache();
return $new_id;


}
//edit a blog
function add_blog($bit_blog, $bit_title = NULL, $bit_content = NULL, $bit_meta = NULL, $bit_group = NULL, $fdate = NULL, $fuser = NULL){

global $ct_config;

	if(strlen($fdate)){
		$fdate = ereg_replace( "\;", "", $fdate);
	}else{
		$fdate = "NOW()";
	}
	if(!$fuser)	$fuser = $_SESSION['user_name'];

	$sql = "INSERT INTO  `{$ct_config['blog_db']}`.`blog_bits` (  `bit_id` ,  `bit_user` ,  `bit_title` ,  `bit_content` ,  `bit_meta` ,  `bit_datestamp` ,  `bit_timestamp` ,  `bit_group` ,  `bit_blog` ,  `bit_edit` ,  `bit_editwhy` ) 
VALUES (
0 ,  '".$fuser."',  '".$bit_title."',  '".$bit_content."',  '$bit_meta', $fdate , NOW( ) ,  '".$bit_group."',  '".$bit_blog."',  '0',  '');";	

		runQuery($sql,'Blogs');
		$id = mysql_insert_id();
		$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_bits` SET  `bit_id` =  '$id' WHERE  `bit_rid` = $id ";
		runQuery($sql,'Blogs');

		$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_rid` = $id";
		$tresult = runQuery($sql,'Fetch Page Groups');
    	$row = mysql_fetch_array($tresult);

		$key = md5($row['bit_rid'].$row['bit_rid'].$row['bit_user'].$row['bit_title'].$row['bit_content'].$row['bit_meta'].$row['bit_datestamp'].$row['bit_timestamp'].$row['bit_group'].$row['bit_blog']);

		$url = render_blog_link($id,true);

		$uri = uri_geturi($url);
	
		$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_bits` SET  `bit_md5` = '$key' , `bit_uri` = $uri WHERE  `bit_rid` = $id";
		runQuery($sql,'Blogs');
if($ct_config['blog_enmsg'])
	 	new_item_post($id, $bit_blog);

	$preg = array(
//[data]
	'/\[blog(?::\w+)?\](\d+)\[\/blog(?::\w+)?\]/sie'=>"clear_blog_cache(\\1)"
);
$message = @preg_replace(array_keys($preg), array_values($preg), $bit_content);

updatesidecache();

return $id;

}


function makepostcache(&$post){
		global $ct_config;
			$metadata = readxml($post['bit_meta']);
			if($metadata['METADATA']['META']){
				foreach($metadata['METADATA']['META'] as $key => $value){
					$blogpost .= "<b>".strtotitle(str_replace("_"," ",$key)).":</b> $value<br />\n";
				}
			}
			$blogpost .= bbcode($post['bit_content']);
			$blogpost .=  "<div class=\"postTools\">".list_posts(linked_from($post['bit_id']),$linkeddiv, $post['bit_id'])."</div>\n";
			$blogpost .=  "$linkeddiv\n";
				$tsql = "UPDATE  `{$ct_config['blog_db']}`.`blog_bits` SET  `bit_cache` =  '".addslashes($blogpost)."' WHERE  `blog_bits`.`bit_rid` = {$post['bit_rid']} LIMIT 1 ;";
				runQuery($tsql,'Update Cache');
	
			return 	$blogpost;

}


function linked_from($bit_id){
global $ct_config;

$sql = "SELECT `bit_id` 
FROM   `{$ct_config['blog_db']}`.`blog_bits` 
WHERE  `bit_content` LIKE '%[blog]{$bit_id}[/blog]%' AND  `bit_edit` =0";
	
		$tresult = runQuery($sql,'Fetch Page Groups');
    	while($row = mysql_fetch_array($tresult)){
			$ids[] = $row['bit_id'];
		}

	return $ids;
}

function list_posts($bit_id, &$linked, $post_id){
if(count($bit_id)){
	$linked = "<div class=\"postLinkedItems\" id=\"postLinked_{$post_id}\"><b>This post is linked by:</b><ul>\n";
	foreach($bit_id as $id){
		$linked .= "<li>".render_blog_link($id)."</li>";
	}
	$linked .= "</ul></div>\n";
	return "<div class=\"postLinkedBut\" onclick=\"$('#postLinked_{$post_id}').fadeIn();\">Linked Posts</div>";
}
}






function new_item_post($bit_id, $blog_id){
global $ct_config;

$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_users` INNER JOIN  `blog_sub` ON u_name = sub_username WHERE sub_blog = $blog_id AND u_emailsub > 3";

$result = runQuery($sql,'Sub user for blog');

if(mysql_num_rows($result)){

	$sql = "SELECT *, UNIX_TIMESTAMP(bit_datestamp) as datetime FROM  `{$ct_config['blog_db']}`.`blog_bits` INNER JOIN  `{$ct_config['blog_db']}`.`blog_blogs` ON bit_blog = blog_id WHERE  `bit_id` = $bit_id AND bit_edit = 0";

	$tresult = runQuery($sql,'Sub user for blog');
	$row = mysql_fetch_array($tresult);
	$subject = strip_tags("[{$ct_config['blog_title']}] New Post - {$row['bit_title']}");

	$content_text = "New Post: {$row['bit_title']}\n";
	$content_text .= "by ".get_user_info($row['bit_user'],'name')." as part of the {$row['blog_name']} blog.\n";
	$content_text .= "Posted on ".date("jS F Y @ H:i",$row['datetime'])." \n";
	$content_text .= "\n ". render_blog_link($row['bit_id'],1)." \n";
	$content_text .= "\n\n The Blog Server \n\n\n To adjust your email settings please edit your user setting in the blog.";


	$content_html = "<h2>New Post: <a href=\"".render_blog_link($row['bit_id'],1)."\">{$row['bit_title']}</a></h2>\n";
	$content_html .= "by ".get_user_info($row['bit_user'],'name')." as part of the <a href=\"".render_link($row['blog_sname'])."/\">{$row['blog_name']}</a> blog.<br />\n";
	$content_html .= "Posted on ".date("jS F Y @ H:i",$row['datetime'])."<br /> \n";
	$content_html .= "\n ".render_blog_link($row['bit_id'],1)."<br /> \n";
	$content_html .= "<br />\n The Blog Server <br /><br /><br />\n\n\n To adjust your email settings please edit your user setting in the blog.";

	$key = $ct_config['blog_db']."_post_".$row['bit_id'];

while($row = mysql_fetch_array($result)){

	new_message(addslashes($subject), addslashes($content_text),  addslashes($content_html),  $row['u_name'],  1, $row['u_proflocate'],  $key, 1);

}

}

}


function new_com_post($com_id, $blog_id){
global $ct_config;

$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_users` INNER JOIN  `blog_sub` ON u_name = sub_username WHERE sub_blog = $blog_id AND u_emailsub > 4";

$result = runQuery($sql,'Sub user for blog');

if(mysql_num_rows($result)){

	$sql = "SELECT *, UNIX_TIMESTAMP(com_datetime) as datetime FROM  `{$ct_config['blog_db']}`.`blog_bits` INNER JOIN  `{$ct_config['blog_db']}`.`blog_blogs` ON bit_blog = blog_id INNER JOIN `{$ct_config['blog_db']}`.`blog_com` ON `com_bit` = `bit_id` WHERE  `com_id` = $com_id AND com_edit =0";

	$tresult = runQuery($sql,'Sub user for blog');
	$row = mysql_fetch_array($tresult);
	$subject = strip_tags("[{$ct_config['blog_title']}] New Comment - {$row['com_title']}");

	$content_text = "New Comment: {$row['com_title']}\n";
	$content_text .= "For Post: {$row['bit_title']}\n";
	$content_text .= "by ".get_user_info($row['com_user'],'name')." as part of the {$row['blog_name']} blog.\n";
	$content_text .= "Posted on ".date("jS F Y @ H:i",$row['datetime'])." \n";
	$content_text .= "\n ".render_blog_link($row['bit_id'],1)."#{$row['com_id']} \n";
	$content_text .= "\n\n The Blog Server \n\n\n To adjust your email settings please edit your user setting in the blog.";


	$content_html = "<h2>New Post: <a href=\"".render_blog_link($row['bit_id'],1)."#{$row['com_id']}\">{$row['com_title']}</a></h2>\n";
	$content_html .= "For Post: {$row['bit_title']}<br/>\n";
	$content_html .= "by ".get_user_info($row['com_user'],'name')." as part of the <a href=\"".render_link($row['blog_sname']).">{$row['blog_name']}</a> blog.<br />\n";
	$content_html .= "Posted on ".date("jS F Y @ H:i",$row['datetime'])."<br /> \n";
	$content_html .= "\n ".render_blog_link($row['bit_id'],1)."#{$row['com_id']} <br /> \n";
	$content_html .= "<br />\n The Blog Server <br /><br /><br />\n\n\n To adjust your email settings please edit your user setting in the blog.";

	$key = $ct_config['blog_db']."_comment_".$row['com_id'];

while($row = mysql_fetch_array($result)){

	new_message(addslashes($subject), addslashes($content_text),  addslashes($content_html),  $row['u_name'],  1, $row['u_proflocate'],  $key, 1);

}

}

}


function buildbuttons(){

global $blog_id;

return "<input type=\"button\" value=\"b\" style=\"width:50px;font-weight:bold\" onclick=\"tag('b');\"/>
<input type=\"button\" value=\"i\" style=\"width:50px;font-style:italic\" onclick=\"tag('i');\"/>
<input type=\"button\" value=\"u\" style=\"width:50px;text-decoration:underline\" onclick=\"tag('u');\"/>
<input type=\"button\" value=\"size\" style=\"width:50px\" onclick=\"tag('size');\"/>
<input type=\"button\" value=\"quote\" style=\"width:50px\" onclick=\"tag('quote');\"/>
<input type=\"button\" value=\"code\" style=\"width:50px\" onclick=\"tag('code');\"/>
<input type=\"button\" value=\"url\" style=\"width:50px\" onclick=\"tag('url');\"/><br />
<input type=\"button\" value=\"img\" style=\"width:50px\" onclick=\"tag('img');\"/>
<input type=\"button\" value=\"link to post\" style=\"width:105px\" onclick=\"javascript:window.open('".render_link('linkblog.php',array("blog_id" => $blog_id))."','barcode', 'left=400,top=400,width=450,height=500,toolbar=0,resizable=0,location=0,directories=0,scrollbars=1,menubar=0,status=0'); void(0)\"/>";


}
function get_comment_count($id){

global $ct_config;

$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_com` WHERE  `com_bit` = {$id} AND  `com_edit` =0;";

$result = runQuery($sql,'Sub user for blog');

return mysql_num_rows($result);
}





 $ct_config['bbcode_preg'] = array(
    // Font and text manipulation ( [color] [size] [font] [align] )
    '/\[color=(.*?)(?::\w+)?\](.*?)\[\/color(?::\w+)?\]/si'   => "<span style=\"color:\\1\">\\2</span>",
    '/\[size=(.*?)(?::\w+)?\](.*?)\[\/size(?::\w+)?\]/si'     => "<span style=\"font-size:\\1px\">\\2</span>",
    '/\[font=(.*?)(?::\w+)?\](.*?)\[\/font(?::\w+)?\]/si'     => "<span style=\"font-family:\\1\">\\2</span>",
    '/\[align=(.*?)(?::\w+)?\](.*?)\[\/align(?::\w+)?\]/si'   => "<div style=\"text-align:\\1\">\\2</div>",
    '/\[b(?::\w+)?\](.*?)\[\/b(?::\w+)?\]/si'                 => "<b>\\1</b>",
    '/\[i(?::\w+)?\](.*?)\[\/i(?::\w+)?\]/si'                 => "<i>\\1</i>",
    '/\[u(?::\w+)?\](.*?)\[\/u(?::\w+)?\]/si'                 => "<u>\\1</u>",
    '/\[center(?::\w+)?\](.*?)\[\/center(?::\w+)?\]/si'       => "<div style=\"text-align:center\">\\1</div>",
    '/\[code(?::\w+)?\](.*?)\[\/code(?::\w+)?\]/si'           => "<span class=\"code\"><pre>\\1</pre></span>",
    '/\[code=(.*?)(?::\w+)?\](.*?)\[\/code(?::\w+)?\]/sie'    => "code_render_inline('\\2',\"\\1\")",
	// [email]
    '/\[email(?::\w+)?\](.*?)\[\/email(?::\w+)?\]/si'         => "<a href=\"mailto:\\1\" class=\"ng_email\">\\1</a>",
    '/\[email=(.*?)(?::\w+)?\](.*?)\[\/email(?::\w+)?\]/si'   => "<a href=\"mailto:\\1\" class=\"ng_email\">\\2</a>",
    // [url]
    '/\[url(?::\w+)?\]www\.(.*?)\[\/url(?::\w+)?\]/si'        => "<a href=\"http://www.\\1\" class=\"ng_url\">\\1</a>",
    '/\[url(?::\w+)?\]((?:http|https|news|ftp)\:\/\/.*?)\[\/url(?::\w+)?\]/si'             => "<a href=\"\\1\" class=\"ng_url\">\\1</a>",
    '/\[url=((?:http|https|news|ftp)\:\/\/.*?)(?::\w+)?\](.*?)\[\/url(?::\w+)?\]/si'       => "<a href=\"\\1\" class=\"ng_url\">\\2</a>",
	'/\[url=(.*?)(?::\w+)?\](.*?)\[\/url(?::\w+)?\]/si'       => "<a href=\"http://\\1\" class=\"ng_url\">\\2</a>",
	'/\[url(?::\w+)?\](mailto\:.*?)\[\/url(?::\w+)?\]/si'             => "<a href=\"\\1\" class=\"ng_url\">\\1</a>",
    '/\[url=(mailto\:.*?)(?::\w+)?\](.*?)\[\/url(?::\w+)?\]/si'       => "<a href=\"\\1\" class=\"ng_url\">\\2</a>",
    // [img]
    '/\[img(?::\w+)?\]((?:http|https|ftp)\:\/\/.*?)\[\/img(?::\w+)?\]/si'             => "<img src=\"\\1\" border=\"0\" alt=\"image\"/>",
    '/\[img=(.*?)x(.*?)(?::\w+)?\]((?:http|https|ftp)\:\/\/.*?)\[\/img(?::\w+)?\]/si' => "<img width=\"\\1\" height=\"\\2\" src=\"\\3\" border=\"0\" alt=\"image\"/>",
   '/\[img=center(?::\w+)?\]((?:http|https|ftp)\:\/\/.*?)\[\/img(?::\w+)?\]/si' => "<center><img width=\"\\1\" height=\"\\2\" src=\"\\3\" border=\"0\" alt=\"image\"/></center>",
    // [quote]
    '/\[quote(?::\w+)?\](.*?)\[\/quote(?::\w+)?\]/si'         => "<blockquote>\\1</blockquote>",
    '/\[quote=(?:&quot;|"|\')?(.*?)["\']?(?:&quot;|"|\')?\](.*?)\[\/quote(?::\w+)?\]/si'   => "<div class=\"ng_quote\">Quote \\1:<div class=\"ng_quote_body\">\\2</div></div>",
    // [list]
    '/\[\*(?::\w+)?\]\s*([^\[]*)/si'                          => "<li class=\"ng_list_item\">\\1</li>",
    '/\[list(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/si'           => "<ul class=\"ng_list\">\\1</ul>",
    '/\[list(?::\w+)?\](.*?)\[\/list:u(?::\w+)?\]/s'          => "<ul class=\"ng_list\">\\1</ul>",
    '/\[list=1(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/si'         => "<ol class=\"ng_list\" style=\"list-style-type: decimal;\">\\1</ol>",
    '/\[list=i(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type: lower-roman;\">\\1</ol>",
    '/\[list=I(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type: upper-roman;\">\\1</ol>",
    '/\[list=a(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type: lower-alpha;\">\\1</ol>",
    '/\[list=A(?::\w+)?\](.*?)\[\/list(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type: upper-alpha;\">\\1</ol>",
    '/\[list(?::\w+)?\](.*?)\[\/list:o(?::\w+)?\]/s'          => "<ol class=\"ng_list\" style=\"list-style-type: decimal;\">\\1</ol>",
    // the following lines clean up our output a bit
    '/<ol(.*?)>(?:.*?)<li(.*?)>/si'         => "<ol\\1><li\\2>",
    '/<ul(.*?)>(?:.*?)<li(.*?)>/si'         => "<ul\\1><li\\2>",

	'/\[pb\]/si' => "<!--page-->",
        
	//[data]
	
	'/\[data(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie'=>"'<div style=\"float:left;\">'.getdata(\\1).'</div>'",
'/\[data=text(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "getdata(\\1,1)",
	'/\[data=text(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "getdata(\\1,1)",
        '/\[data=size\:(.*?)(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "getdata(\\2,2,'\\1')",
        '/\[data=(.*?)(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "'<div style=\"float:\\1;\">'.getdata(\\2).'</div>'",
	'/\[blog(?::\w+)?\](\d+)\[\/blog(?::\w+)?\]/sie'=>"render_blog_link(\\1)",
	'/\[blog(?::\w+)?\]\[\/blog(?::\w+)?\]/si'=>"",
//pubmed
	'/\[pubmed(?::\w+)?\](.*?)\[\/pubmed(?::\w+)?\]/si'=>"<a href=\"http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&db=pubmed&dopt=Abstract&list_uids=\\1\" target=_blank class=ext_link>PMID: \\1</a>",

	//[table]
	'/\[table(?::\w+)?\](.*?)\[\/table(?::\w+)?\]/si'=>"<table class=\"table_st\" cellspacing=\"0\">\\1</table>",
	'/\[row(?::\w+)?\](.*?)\[\/row(?::\w+)?\]\s*/si'=>"<tr><td class=\"table_st\">\\1</td></tr>",
	'/\[col(?::\w+)?\]/si'=>"</td><td class=\"table_st\">",	
	'/\[col=(.*?)(?::\w+)?\]/si'     => "</td><td class=\"table_st\" align=\"\\1\">",
	'/\[mrow(?::\w+)?\](.*?)\[\/mrow(?::\w+)?\]\s*/si'=>"<tr class=\"table_title\"><td class=\"table_st\">\\1</td></tr>",
	'/\[mcol(?::\w+)?\]/si'=>"</td><td class=\"table_st\">",


	
// smilies
/*
   '/\:angry:/si'  => "<img src='smilies/angry.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:arrow:/si'  => "<img src='smilies/arrow.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:blink:/si'  => "<img src='smilies/blink.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:blush:/si'  => "<img src='smilies/blush.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:bow:/si'  => "<img src='smilies/bow.gif' border=\"0\" alt=\"smiley\"/>",
  '/\://si' => "<img src='smilies/confused.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:-//si' => "<img src='smilies/confused.gif' border=\"0\" alt=\"smiley\"/>",
  '/\8-\)/si' => "<img src='smilies/cool1.gif' border=\"0\" alt=\"smiley\"/>",
  '/\8\)/si' => "<img src='smilies/cool1.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:\'-\(/si' => "<img src='smilies/cry.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:\'\(/si' => "<img src='smilies/cry.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:devil:/si'  => "<img src='smilies/devil.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:evil:/si'  => "<img src='smilies/evil.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:evilmad:/si'  => "<img src='smilies/evilmad.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:fun:/si'  => "<img src='smilies/fun.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:-D/si' => "<img src='smilies/grin.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:D/si' => "<img src='smilies/grin.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:idea:/si'  => "<img src='smilies/idea.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:innocent:/si'  => "<img src='smilies/innocent.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:kiss:/si'  => "<img src='smilies/kiss.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:lol:/si'  => "<img src='smilies/laugh.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:look:/si'  => "<img src='smilies/look.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:love:/si'  => "<img src='smilies/love.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:ninja:/si'  => "<img src='smilies/ninja.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:no:/si'  => "<img src='smilies/no.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:-\|/si' => "<img src='smilies/noexpression.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:\|/si' => "<img src='smilies/noexpression.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:nugget:/si'  => "<img src='smilies/nugget.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:-O/si' => "<img src='smilies/ohmy.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:O/si' => "<img src='smilies/ohmy.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:ras:/si'  => "<img src='smilies/ras.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:rolleyes:/si'  => "<img src='smilies/rolleyes.gif' border=\"0\" alt=\"smiley\"/>",
  '/\::\)/si'  => "<img src='smilies/rolleyes.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:-\(/si' => "<img src='smilies/sad.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:\(/si' => "<img src='smilies/sad.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:sick:/si'  => "<img src='smilies/sick.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:slap:/si'  => "<img src='smilies/slap.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:smartass:/si'  => "<img src='smilies/smartass.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:-\)/si' => "<img src='smilies/smile1.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:\)/si' => "<img src='smilies/smile1.gif' border=\"0\" alt=\"smiley\"/>",  
  '/\:thumbsup:/si'  => "<img src='smilies/thumbsup.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:thumbsdown:/si'  => "<img src='smilies/thumbsdown.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:-P/si' => "<img src='smilies/tongue.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:P/si' => "<img src='smilies/tongue.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:unsure:/si'  => "<img src='smilies/unsure.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:w00t:/si'  => "<img src='smilies/w00t.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:wall:/si'  => "<img src='smilies/wall.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:wave:/si'  => "<img src='smilies/wave.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:wavecry:/si'  => "<img src='smilies/wavecry.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:weirdo:/si'  => "<img src='smilies/weirdo.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:whistle:/si'  => "<img src='smilies/whistle.gif' border=\"0\" alt=\"smiley\"/>",
  '/\;-\)/si' => "<img src='smilies/wink.gif' border=\"0\" alt=\"smiley\"/>",
  '/\;\)/si' => "<img src='smilies/wink.gif' border=\"0\" alt=\"smiley\"/>",
  '/\:wub:/si'  => "<img src='smilies/wub.gif' border=\"0\" alt=\"smiley\"/>"
*/
  );


?>