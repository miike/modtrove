<?php

include("../../lib/default_config.php");



checkblogconfig($_SESSION['blog_id']);


if($_REQUEST['uri']){

if($url = resolve_uri($_REQUEST['uri'])){
		header("Location: {$url}");
		exit();
	}else{
		echo "Not Found";
	}


}
$title = $ct_config['blog_title'];
$desc = $ct_config['blog_desc'];
include("../style/{$ct_config['blog_style']}/blogstyle.php");
$blogpost = NULL;
//include('functions.php');
$bodytag = " onLoad=\"javascript: document.uriform.uri.focus()\"";
$blogpost['title'] = "Blog URI Resolver";

$blogpost['post'] = "<form action=\"/uri/index.php\" name=uriform>";

$blogpost['post'] .= "URI: <input type=text name=uri  style=\"font-size:100%\"> <input type=submit value=Go! style=\"font-size:100%\">";

$blogpost['post'] .= "</form>";


			$body .= "<span  style=\"font-size:150%\">".blog_style_post(&$blogpost)."</span>";


include('../page.php');

?>
