<?php

$ct_config['protected_paths'][] = "uri";

function resolve_uri($id, $type = "hex"){
	global $ct_config;
if($type=="hex") $id = hexdec($id);

$sql = "SELECT * FROM  `{$ct_config['uri_db']}`.`uri` WHERE  `uri_id` = {$id} LIMIT 1";
					$tresulta = runQuery($sql,'Fetch Page Groups');
 					$line = mysql_fetch_array($tresulta);
	if($line['uri_id']){
		return $line['uri_url'];
	}else{
		return false;
	}

}


function getbituri($id){
global $ct_config;
$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_id` = $id AND  `bit_edit` =0";
	$result = runQuery($sql,'Fetch Page Groups');
	$srow = mysql_fetch_array($result);

	return $srow['bit_uri'];

}
function get_uri_url($id){
global $ct_config;
return "http://{$ct_config['uri_server']}{$ct_config['blog_path']}uri/".dechex(getbituri($id));
}

function get_uri_labelpage($id){
global $ct_config;
return "<a href=\"javascript:window.open('{$ct_config['blog_path']}uri/print.php?id=".getbituri($id)."&type=blog_bit','_blank', 'left=400,top=20,width=550,height=200,toolbar=0,resizable=0,location=0,directories=0,scrollbars=0,menubar=0,status=0'); void(0)\">URI Label</a>";
}

function get_uri_qrcode($id){
global $ct_config;
return "<img src=\"http://{$ct_config['uri_server']}{$ct_config['blog_path']}uri/qr/qr_img.php?d=".urlencode(get_uri_url($id))."\">";
}

// URI Stuff

function uri_geturi($url){
global $ct_config;
	$sql =" INSERT INTO `{$ct_config['uri_db']}`.`uri` VALUES ( '', '".addslashes($url)."');";
        runQuery($sql,'insert uri');
		$id = mysql_insert_id();
		return $id;
}
?>
