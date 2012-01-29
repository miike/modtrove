<?php

include("../lib/default_config.php");

$pathinfo = $_REQUEST['uri'];

$id = (int)$_REQUEST['id'];

$sql =	"SELECT  * FROM  `{$ct_config['blog_db']}`.`blog_bits` ";
	$sql .= "WHERE `bit_id` = ".$id." AND `bit_edit` = 0";
$tresult = runQuery($sql,'Blogs');
$post = mysql_fetch_array($tresult);

$blog = db_get_blog_by_id($post['bit_blog']);
$blog_id = $blog['blog_id'];

$user_can_edit = 0;
checkblogconfig($blog['blog_id']);

if(!checkzone($blog['blog_zone'],0,$blog['blog_id']) || !checkzone($ct_config['blog_zone'])){
	header("Location: {$ct_config['blog_path']}?msg=Forbidden!&turl=/".urlencode($_REQUEST['uri']));
	exit();
}
if(($_SESSION['user_name']!=$post['bit_user']) && !$user_can_edit && (lookup_or_default('user_admin', $_SESSION, 0) < 3)){
	header("Location: {$ct_config['blog_path']}?msg=Forbidden!&turl=/".urlencode($_REQUEST['uri']));
	exit();	
}

$body .= <<<END
	<div class="postTitle" align="center">New Sketch Title: <input type="text" id="sketch_title"></div><br>
		<div class=imagep_box id=imagep_box style="visibility:visible;">
        <div id="layer_container"><canvas id="layer" width="640" height="480"></canvas></div>


					Colours: 
			        <input type="button" value="Black" style="color:black" onClick="setStyle('black')">
			        <input type="button" value="Blue" style="color:blue" onClick="setStyle('blue')">
			        <input type="button" value="Red" style="color:red" onClick="setStyle('red')">
			        <input type="button" value="Green" style="color:green" onClick="setStyle('green')">
				 	<input type="button" value="Eraser" style="color:purple" onClick="setStyle('erase')">
			        <input type="button" style="float:right;" value="Add Sketch" onClick="postSketch({$id})">	
			        <input type="button" style="float:right;" value="Cancel Sketch" onClick="canSketch()">	</div>

	
END;


$head .= '<meta content="width=675px" name="viewport" />';

$jquery['set'] = 1;

$jquery['function'] .= <<<END

if(!isCanvasSupported()) { 
  $('#page').html('<div id="messages"><div class="msg warning" id="msg_0">It looks like your browser doesn\'t support HTML5 Canvas, so the sketch function won\'t work.</div></div>');
}

END;

$jquery['code'] = "
function isCanvasSupported(){
  var elem = document.createElement('canvas');
  return !!(elem.getContext && elem.getContext('2d'));
}

";

$head .= '<style type="text/css">  
		            #layer_container { cursor: crosshair; position: relative;  width: 640px; height: 480px; border: 1px solid black; z-index: 10; }	
				
					#layer_container:focus{cursor: crosshair,move;}
		            #source_container { width: 640px; border: 1px solid blue; }
		        </style>
					<script type="text/javascript" src="'.$ct_config['blog_path'].'inc/CbS.js"></script>
					<script type="text/javascript" src="'.$ct_config['blog_path'].'inc/Base64.js"></script>
					';
		
		
$bodytag .= "style=\"width: 665px; margin:auto;\" onload=\"init();\" $body_tag";
$minipage = 1;
include('page.php');

?>
