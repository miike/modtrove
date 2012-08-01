<?php

include("../lib/default_config.php");

$pathinfo = $_SERVER['LABTROVE_REQUEST_PATH'];

if($pathinfo){
	$pathinfo = explode("/",$pathinfo);
	if($pathinfo[0] == "data"){
		array_shift($pathinfo);
	}
	if($pathinfo[0] == "files"){
		array_shift($pathinfo);
	}
	$ext = pathinfo($pathinfo[0] , PATHINFO_EXTENSION);
	$id = (int)pathinfo($pathinfo[0] , PATHINFO_BASENAME);
}else{
	$id = (int)$_REQUEST['id'];
}




$sql = "SELECT   `data_id` ,  `data_post` ,  `data_datetime` ,  `data_type`  
		FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = '$id'";
		
$result = runQuery($sql,'Fetch Page Groups');
$datainfo = mysql_fetch_array($result);

if($datainfo['data_post']){
	$sql = "SELECT `bit_blog` FROM `{$ct_config['blog_db']}`.`blog_bits` WHERE `bit_id` = '{$datainfo['data_post']}' AND `bit_edit` = 0";
	$result = runQuery($sql,'Fetch Page Groups');
	$postid = mysql_fetch_array($result);
	$_SESSION['blog_id'] = $postid['bit_blog'];
}

checkblogconfig($_SESSION['blog_id']);

if($_SESSION['blog_id']){
	$sql = "SELECT *,'data_pre' FROM  `{$ct_config['blog_db']}`.`blog_blogs` WHERE `blog_id` = '{$_SESSION['blog_id']}'";
	$result = runQuery($sql,'Blogs');
	$blog = mysql_fetch_array($result);

	if(!checkzone($blog['blog_zone'],0,$blog['blog_id'])){
		header("Location: {$ct_config['blog_path']}?msg=Forbidden!&turl=".urlencode($_SERVER["REQUEST_URI"]));
		exit();
	}
}

if(!checkzone($ct_config['blog_zone'])){
	header("Location: {$ct_config['blog_path']}?msg=Forbidden!&turl=".urlencode($_SERVER["REQUEST_URI"]));
	exit();
}



$postTo = render_link("parser.php");

if(1){

	$sql = "SELECT data_data, `data_post`, data_type, data_id, data_datetime FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $id";
	$result = runQuery($sql,'Fetch Page Groups');
	$row = mysql_fetch_array($result);
	
	switch ($row['data_type']) {

		case 'jpg':
		case 'jpeg':
			header('Content-type: image/jpeg');
			echo $row['data_data'];
			$sql = "SELECT data_data, data_type FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = '$id'";break;
	
		case 'gif':
			header('Content-type: image/gif');
			echo $row['data_data'];
			break;
	
		case 'png':
			header('Content-type: image/png');
			echo $row['data_data'];
			break;
	
		case 'text':
			header('Content-type: text/plain');
			echo $row['data_data'];
			break;
	
		case 'data_meta':
			$metadata = readxml($row['data_data']);
			
	
		if($ext && $ext != "html"){
			if(file_exists("export/data/{$ext}.php")){
				include("export/data/{$ext}.php");
			}
		}
			
			
			$distype = "preview";
			$img = drawdatabox($metadata, $distype, true);
				break;

			case 'img_meta':
				$metadata = readxml($row['data_data']);
				$distype="img";
				$imagepath = $metadata['METADATA']['PREVIEW_SRC'];
				break;

			case 'asc':
				break;

			default:
		}

	$limitx = 640;
	$limity = 480;

	// image data stuff
	if($distype=="img"){
		if(!$metadata['METADATA']['SIZE_X'] || !$metadata['METADATA']['SIZE_Y']){
			list($metadata['METADATA']['SIZE_X'], $metadata['METADATA']['SIZE_Y']) = getimagesize($imagepath);
		}
		

		if($metadata['METADATA']['SIZE_X']>$limitx || $metadata['METADATA']['SIZE_Y']>$limity){
			if(($metadata['METADATA']['SIZE_X']/$limitx)>($metadata['METADATA']['SIZE_Y']/$limity)){
				$sizex = $limitx; 
				$sizey = (int)(($metadata['METADATA']['SIZE_Y']/$metadata['METADATA']['SIZE_X'])*$limitx);
				$imgheight = (int)(($limity-$sizey)/2);
				
				$imgheightb = $imgheight;
				
				if((2*$imgheight)!=($limity-$sizey))
					$imgheightb+=1;
			}
			
			else {
				$sizey = $limity; 
				$sizex = (int)(($metadata['METADATA']['SIZE_X']/$metadata['METADATA']['SIZE_Y'])*$limity);
				$imgheight = 0;
				$imgheightb = $imgheight;
			}

		}
		
		else {

			//no resizings needed
			$sizex = $metadata['METADATA']['SIZE_X'];
			$sizey = $metadata['METADATA']['SIZE_Y'];
			$imgheight = (int)(($limity- $metadata['METADATA']['SIZE_Y'])/2);
			$imgheightb = $imgheight;
			if((2*$imgheight)!=($limity- $metadata['METADATA']['SIZE_Y']))
				$imgheightb+=1;
		}  
	
		if ( $_REQUEST['comment'] && $_SESSION['user_name']) {

			$sql = "SELECT data_data, data_type FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = '$id'";
			$result = runQuery($sql,'Fetch Page Groups');
			$row = mysql_fetch_array($result);
			$metadata = readxml($row['data_data']);

			if($metadata['METADATA']['OVERLAY']) { 

				$datas = split(",",$metadata['METADATA']['OVERLAY']);

				foreach($datas as $bit){

					$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = '".$bit."'";
					$result = runQuery($sql,'Fetch Page Groups');
					$srow = mysql_fetch_array($result);

					$overlay= NULL;
					$overlay = readxml($srow['data_data']);

					$overlayIDs[] = $overlay['METADATA']['ID'];

				}

			}



			$jquery['function'] .= <<<END

			if(!isCanvasSupported()) { 
			  $('#imagep_box').html('<div id="messages"><div class="msg warning" id="msg_0">It looks like your browser doesn\'t support HTML5 Canvas, so the sketch function won\'t work.</div></div>');
			}
END;

			$jquery['code'] = "
			function isCanvasSupported(){
			  var elem = document.createElement('canvas');
			  return !!(elem.getContext && elem.getContext('2d'));
			}

			";

		$img = ' <div id="source_container" align="center" style="padding-top:'.$imgheight.'px; padding-bottom:'.$imgheightb.'px;"><img src="'.$imagepath.'" width="'.$sizex.'" height="'.$sizey.'" alt="source image" /></div>
		        <div id="layer_container"><canvas id="layer" width="640" height="480"></canvas></div>

				
				Colours: 
		        <input type="button" value="Black" style="color:black" onClick="setStyle(\'black\')">
		        <input type="button" value="Blue" style="color:blue" onClick="setStyle(\'blue\')">
		        <input type="button" value="Red" style="color:red" onClick="setStyle(\'red\')">
		        <input type="button" value="Green" style="color:green" onClick="setStyle(\'green\')">
			 	<input type="button" value="Eraser" style="color:purple" onClick="setStyle(\'erase\')">
		        <input type="button" style="float:right;" value="Post Comment" onClick="postComment('.$id.')">	
		        <input type="button" style="float:right;" value="Cancel Comment" onClick="canComment('.$id.')">';

			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/CbS.js\"></script>\n";
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/Base64.js\"></script>\n";
	
			$bodytag .= "onload=\"init();\"";
		$head .= '<style type="text/css">  
		            #layer_container { cursor: crosshair; position: relative; margin-top: -482px; width: 640px; height: 480px; border: 1px solid black; z-index: 10; }	
				
					#layer_container:focus{cursor: crosshair,move;}
		            #source_container { width: 640px; border: 1px solid blue; }
		        </style>';


	
		}else{
		
		$img = "<div align=\"center\" style=\"padding-top:".$imgheight."px;padding-bottom:".$imgheight."px;\"><img src=\"".$imagepath."\" width=\"$sizex\" height=\"$sizey\"></div>\n\n";
		}	
	}

	elseif ($distype=="timeplot") {

		$img = "<iframe src=\"".$ct_config['timeplot']."?building=$building&room=$room&chemtools=true&start=".$startTime."&end=".$endTime."\" width=\"635\" height=\"475\"><iframe>";

	}


	//Overlays

	//overlay stuff
	$overlayIDs = array();

	if($metadata['METADATA']['OVERLAY']) {
	
		$overlay_links = "\t\t<span>This Image has overlays</span>\n\n";
		$datas = split(",",$metadata['METADATA']['OVERLAY']);
		$table_cols = 3;
		$col = 0;

		$overlay_links .= "<table width=640><tr>";
		foreach($datas as $bit){
			$col++;

			$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = ".$bit;
			$result = runQuery($sql,'Fetch Page Groups');
			$srow = mysql_fetch_array($result);
	
			$overlay= NULL;
			$overlay = readxml($srow['data_data']);

			$overlayIDs[] = $overlay['METADATA']['ID'];

			$overlay_links .= "\t\t<td><input type=\"checkbox\" name=\"overlay\" onClick=\"toggleDisplay(".$overlay['METADATA']['ID'].")\"> ".$overlay['METADATA']['TITLE']." (".$overlay['METADATA']['USER'].") </td>\n\n";

			if($col == 	$table_cols){
				$col = 0;
				$overlay_links .= "</tr><tr>";
			}
	
		}
		$overlay_links .= "</tr></table>";
		
	
	}
	//Archives
	$body .= "\t\t<div class=\"postTitle\" align=\"center\">".$metadata['METADATA']['TITLE']."</div><br>\n";

	$f = 0;	
	// overlay stuff
	if($metadata['METADATA']['OVERLAY']) {

		$body .= "\t<div class=image_over_box id=image_over_box style=\"visibility:visible;\">\n\n";

		foreach ($overlayIDs as $overlayID) {
	
			$body.= "\t\t<div id=$overlayID align=\"center\"  align=\"center\" style=\" display:none; position: relative; z-index:".(10+($f++))."; height:0px;\">
						<img src=\"{$ct_config['blog_path']}data/files/{$overlayID}.png\"></div>\n\n";

		}
		
		$body .= "\t</div>\n\n";
	
	}

//image
	$body .= "\t<div class=imagep_box id=imagep_box style=\"visibility:visible;\">\n\n\t\t$img\t</div>\n\n";


	$head .= "<script type=\"text/javascript\">

	function toggleDisplay(divId) {

		var div = document.getElementById(divId);

		if ( div.style.display == 'none' ) {

			div.style.display = 'block';

		}

		else { 

			div.style.display = 'none';

		}

	}

	</script>";



$body .= "\t<br><div class=\"info_image\" style=\"width:640px;\">\n\n";

	
	if($metadata['METADATA']['PICTURE_URL'])
		$body .= "\t\t<a href=\"".$metadata['METADATA']['PICTURE_URL']."\" target=\"_blank\">Hosted Page</a><br/><br/>\n\n";

		$body  .= $overlay_links;

	if ($_SESSION['user_name'] && $distype != "timeplot" && !$not_viewable) {

		$body .="\t\t<a href=\"{$ct_config['blog_path']}data/$id.html?comment=1\">Comment</a><br><br/>";

	}

	if(isset($datalist)){
	
		$body .= "\t\t<div style=\"\">Download as:<br />";
	
		foreach( $datalist as $key => $value){
		
			if( strtolower($value['TYPE'])=='local'){
			
				if(!strlen($value['NAME']))
					$url = render_link('')."data/files/".$value['ID'].".".strtolower(substr($key,5));
	       		else 
					$url = render_link('')."data/files/".$value['ID']."/".$value['NAME'];
	       
				$body .= "\t\t<a href=\"{$url}";
			}
		
			else { $body .= "\t\t<a href=\"".$value['URL']; }
		
			$body .= "\"><img src=\"".file_img(substr($key,5))."\" style=\"vertical-align:middle;\"> ".substr($key,5)."</a>&nbsp; ";
	
		}

		$body .= "\t\t</div>\n\n";

	}



	$body .= "\t</div>\n\n";
 

}

$head .= '<meta content="width=675px" name="viewport" />';
$bodytag .= "style=\"width: 665px; margin:auto;\" $body_tag";
$minipage = 1;
include('page.php');

?>	
