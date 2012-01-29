<?php
include('../../../config.php');
include('../../../lib/functions.php');
include_once('../../../lib/functions_database.php');

$pathinfo = $_SERVER['PATH_INFO'];
if($pathinfo{0} == '/'){
	$pathinfo = substr($pathinfo,1);
	$pathinfo = explode("/",$pathinfo);

	while($request[array_shift($pathinfo)] = addslashes(array_shift($pathinfo)));

}


//Load Blog info
if($request['blog_id']){
	$_SESSION['blog_id'] = (int)$request['blog_id'];
	$blog_id = (int)$request['blog_id'];
}else if($request['bit_id']){
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE `bit_id` = ".(int)$request['bit_id'];
$result = runQuery($sql,'Get blog Id');
$rowb = mysql_fetch_array($result);
	$_SESSION['blog_id'] = (int)$rowb['bit_blog'];
	$blog_id = (int)$rowb['bit_blog'];
}else if($_REQUEST['blog_id']){
	$blog_id = (int)$_REQUEST['blog_id'];
}else if($_SESSION['blog_id']){
	$blog_id = $_SESSION['blog_id'];
}else{
	header('Location: /projects/blog/');
	exit();
}


$_SESSION['blog_id'] = $blog_id;
$blog = db_get_blog_by_id($blog_id);

if(!$request['bit_id']){
	$url =  $ct_config['blog_path'].$blog['blog_sname'];
}else{
	$url = render_blog_link($request['bit_id'],true);
}

header("Location: $url",TRUE,301);

function render_link($page,$varibles = NULL){
global $ct_config;

$url = $ct_config['blog_path'].$page;
unset($varibles['blog_sname']);
unset($varibles['blog_id']);
if(isset($varibles['bit_id'])){
	$url .= "/{$varibles['bit_id']}";
	unset($varibles['bit_id']);
}

if(is_array($varibles))
foreach($varibles as $key => $value){
if(strlen($key)!=0 && strlen($value)!=0)
	$url .= "/$key/$value";
}

$url = str_replace("//","/",$url);
return $url;
}

function render_blog_link($id,$url_only=false){
global $ct_config;

	$sql = "SELECT `blog_bits`.*,`blog_blogs`.`blog_sname`  FROM `{$ct_config['blog_db']}`.`blog_bits` INNER JOIN `{$ct_config['blog_db']}`.`blog_blogs` ON `blog_bits`.`bit_blog` = `blog_blogs`.`blog_id` WHERE `bit_id` = $id OR (`bit_id` = 0 AND `bit_rid` = $id)";
$result = runQuery($sql,'Blogs');
$rowb = mysql_fetch_array($result);

	$name = ereg_replace( "[^A-Za-z0-9\ \_]", "", strip_tags($rowb['bit_title']));
	$name = str_replace(" ","_",$name);
	$url = render_link($rowb['blog_sname'],array('bit_id' => $id))."/{$name}.html";
	if($url_only) return $url; else
	return "<a href=\"{$url}\">".$rowb['bit_title']."</a>"; 



}
?>
