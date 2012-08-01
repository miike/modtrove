<?php

include("../lib/default_config.php");


checkblogconfig($_SESSION['blog_id']);

include("style/{$ct_config['blog_style']}/blogstyle.php");

	if(is_array($_REQUEST['metat_key']))
	foreach($_REQUEST['metat_key'] as $key => $keyn){
					if($keyn && $_REQUEST['metat_value'][$key]){
							$metadata['METADATA']['META'][strtoupper(str_replace(" ","_",$keyn))] = addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['metat_value'][$key])));
					}
	}

	$blogpost['title'] = stripslashes($_REQUEST['comment_title']);
	$blogpost['date'] = date("jS F Y @ H:i");


	if($metadata['METADATA']['META']){
		foreach($metadata['METADATA']['META'] as $key => $value){
			$blogpost['post'] .= "<b>".strtotitle(str_replace("_"," ",$key)).":</b> $value<br />";
		}
	}
	
	$blogpost['post'] .= bbcode(stripslashes($_REQUEST['text']))."\n";
	
	
	
	$body = "<div id=\"minipage\">".blog_style_post(&$blogpost)."</div>";

$minipage = 1;
include('page.php');

?>