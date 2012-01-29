<?php
//Add Data

function add_data_by_filename($data_type, $data_filename)
{
	global $ct_config;
	$size = filesize($data_filename);

	// if threshold is defined and its not zero (disabled) and size is bigger then...
	if(isset($ct_config['uploads_threshold']) && $ct_config['uploads_threshold'] != 0 && $size > $ct_config['uploads_threshold'])
	{
		// get checksum, write to disk, put reference in database
		$checksum = md5_file($data_filename);
		$filepath = tempnam($ct_config['uploads_dir'], 'upload_');
		move_uploaded_file($data_filename, $filepath);
		$new_id = db_add_data_to_database_by_reference($data_type, $size, $checksum, $filepath);
	}
	// elseif use cloud ...
	else // put it all in the database
	{
		$data = file_get_contents($data_filename);
		$new_id = db_add_data_to_database_by_value($data_type, $size, $data);
	}

	return $new_id;
}

function add_data($data_type, $data_data)
{
	global $ct_config;
	$size = strlen($data_data); // unencoded size

	// if threshold is defined and its not zero (disabled) and size is bigger then...
	if(isset($ct_config['uploads_threshold']) && $ct_config['uploads_threshold'] != 0 && $size > $ct_config['uploads_threshold'] && $data_type!='data_meta')
	{
		// get checksum, write to disk, put reference in database
		$checksum = md5($data_data);
		$filepath = tempnam($ct_config['uploads_dir'], 'upload_');
		file_put_contents($filepath, $data_data);
		$data_data = ''; // we dont want huge strings hanging around any longer that we need them 
		$new_id = db_add_data_to_database_by_reference($data_type, $size, $checksum, $filepath);
	}
	// elseif use cloud ...
	else // put it all in the database
	{
		$new_id = db_add_data_to_database_by_value($data_type, $size, $data_data);
	}

	return $new_id;
}


function add_data_meta($title, $data, $main=NULL, $postid=NULL){
global $ct_config;
if(count($data)==1){
	$main = key($data);
}

if($main)

if( $data[$main]['type']=="local") {
	$path = "http://".$ct_config['this_server'].$ct_config['blog_path']."getdata.php?bit=".$data[$main]['id'];
}else{
	 $path = $data[$main]['url'];
}

switch(strtolower($main)){
	case "bmp":
	case "tiff":
	case "tif":
	case "eps":
	case "epsf":
	if(!isset($data['jpg']) && $ct_config['autoconv']['convert']){
		$id = add_data('jpg', data_make_jpg_img($path, strtolower($main) ));
		$data['jpg']['type'] = "local";
		$data['jpg']['volatile'] = 1;
		$data['jpg']['id'] = $id;
		if(strlen($data[$main]['name']))
			$data['jpg']['name'] = substr($data[$main]['name'],0,-3)."jpg";
		$data[strtolower($main)]['original']= 1;
		$main = 'jpg';
	}
	break;

	case "avi":
	case "mpg":
	case "mov":
	case "dv":
	case "flv":
	if(!isset($data['ogg']) && $ct_config['autoconv']['ffmpeg2theora']){
		$addtoque[] = array("name"=>"make_ogg","path"=>$path,"ext"=>strtolower($main));
	}
	if(!isset($data['m4v']) && $ct_config['autoconv']['ffmpeg']){
		$addtoque[] = array("name"=>"make_m4v","path"=>$path,"ext"=>strtolower($main));
	}
break;
}
foreach($data as $keys => $vals){
$main_met .= "<data_{$keys}>";
foreach($vals as $key=>$val){
$main_met .= "<{$key}>".htmlspecialchars($val)."</{$key}>";
}
if($main==$keys) $main_met .= "<main>1</main>";
$main_met .= "</data_{$keys}>\n";
}

$metadata = "<metadata>
  <title>".htmlspecialchars($title)."</title>
  $main_met
</metadata>";


$theid = add_data('data_meta', $metadata);

if(isset($addtoque))
foreach($addtoque as $que){
	$que['path'] = escapeshellarg($que['path']);
	$exc=  "cd {$ct_config['pwd']}/lib/scripts/proc; nice php {$que['name']}.php {$que['path']} {$que['ext']} {$theid} &";
	`$exc`;
}


if($postid){
	setposttodata($theid,$postid);
}

return $theid;
	
}


function resize_data($path,$size,$type,$idS){
		
global $ct_config;
$togo = "cache/{$ct_config['blog_db']}_{$idS}_$size.$type";
		if(!file_exists($togo)){
		$tmpfname = tempnam("/tmp", "blog_");
		file_put_contents ( $tmpfname, file_get_contents($path));
		`convert $tmpfname -resize $size $togo`;
		unlink($tmpfname);
		}
		return file_get_contents($togo);

}

function data_make_jpg_img($path, $ext){
		
		global $ct_config;

		$tmpfname_a = secure_tmpname(".$ext", "blog_", "/tmp");
		$tmpfname_b = secure_tmpname(".jpg","blog_","/tmp");
		file_put_contents ( $tmpfname_a, file_get_contents($path));
		`convert $tmpfname_a $tmpfname_b `;
		$new = file_get_contents($tmpfname_b);
		unlink($tmpfname_a);
		unlink($tmpfname_b);
		return $new;

}



function secure_tmpname($postfix = '.tmp', $prefix = 'tmp', $dir = null) {
    // validate arguments
    if (! (isset($postfix) && is_string($postfix))) {
        return false;
    }
    if (! (isset($prefix) && is_string($prefix))) {
        return false;
    }
    if (! isset($dir)) {
        $dir = getcwd();
    }

    // find a temporary name
    $tries = 1;
    do {
        // get a known, unique temporary file name
        $sysFileName = tempnam($dir, $prefix);
        if ($sysFileName === false) {
            return false;
        }

        // tack on the extension
        $newFileName = $sysFileName . $postfix;
        if ($sysFileName == $newFileName) {
            return $sysFileName;
        }

        // move or point the created temporary file to the new filename
        // NOTE: these fail if the new file name exist
        $newFileCreated =  @link($sysFileName, $newFileName);
        if ($newFileCreated) {
	        @unlink ($sysFileName);
            return $newFileName;
        }

        @unlink ($sysFileName);
        $tries++;
    } while ($tries <= 5);

    return false;
}


function data_get_item($data_id){
	$data = db_get_data_by_id($data_id);
	return $data['data_data'];
}


function drawdatabox($metadata, &$distype, $datapage=true, $x="630px",$y="660px" ){
		
		
		global $not_viewable,$ct_config;
		
		if($datapage)
				global $datalist;
		foreach($metadata['METADATA'] as $key=>$value){
	
			if(substr($key,0,5) == 'DATA_' || substr($key,0,5) == 'PLOT_'){
				if(!$defaulttype_bk)
					$defaulttype_bk = $key;
				
				switch($key){
					case 'DATA_OGG':
					case 'DATA_ASC':
					case 'DATA_JPG':
					case 'DATA_JPEG':
					case 'DATA_GIF':
					case 'DATA_PNG':
					case 'DATA_PDF':
					case 'DATA_PL':
					case 'DATA_PHP':
					case 'DATA_ASP':
					case 'DATA_SH':
					case 'DATA_C':
					case 'DATA_CPP':
					case 'DATA_CSS':
					case 'DATA_URL':
					case 'DATA_M':
					case 'DATA_SQL':
					case 'DATA_RB':
					case 'DATA_TXT':
					case 'DATA_XML':
					case 'DATA_RDF':
					case 'DATA_JAVA':
					case 'DATA_KML':
					case 'DATA_AVI':
					case 'DATA_MP4':
					case 'DATA_M4V':
					case 'DATA_FLV':
					case 'DATA_MOV':
					case 'DATA_MPG':
					case 'DATA_OGG':
					case 'DATA_WMV':
					if(!$defaulttype)
					$defaulttype = $key;
				if($value['MAIN']==1){
					$defaulttype = $key;
				}

				}

				$datalist[$key] = $value;

			}

		}	
			if(!$defaulttype) $defaulttype = $defaulttype_bk;
		

		if( $metadata['METADATA'][$defaulttype]['ID'] + 0 ){
			 $file_id = (int)$metadata['METADATA'][$defaulttype]['ID'];
			 $path = render_link('')."data/files/{$metadata['METADATA'][$defaulttype]['ID']}.".strtolower(substr($defaulttype,5));
		}

		else {
			$file_id = 0;
			$path = htmlspecialchars_decode($metadata['METADATA'][$defaulttype]['URL']);
		}
		
		
		switch ($defaulttype) {
			/*case 'DATA_ASC':
				if( is_int( ($metadata['METADATA']['DATA_ASC']['ID']+0)) ){ $path .= "&fdata_type=text"; }
				$data = file_get_contents($path);
				$title['y'] = $metadata['METADATA']['DATA_YLABEL'];
				$title['x'] = $metadata['METADATA']['DATA_XLABEL'];
				include_once('functions/ascii_img.php');
				acsii_img($data, 'png' ,$title);
			break;*/

			
			case 'DATA_AVI':
			case 'DATA_MP4':
			case 'DATA_M4V':
			case 'DATA_FLV':
			case 'DATA_MOV':
			case 'DATA_MPG':
			case 'DATA_OGG':
			case 'DATA_WMV':
			
			$img = '<video preload="true" width="'.$x.'" poster="" controls="controls">';
			
			foreach(array("DATA_OGG"=>"ogg","DATA_M4V"=>"mp4") as $mkey=>$ext){
			if(!isset($metadata['METADATA'][$mkey])) continue;
			if( $metadata['METADATA'][$mkey]['ID'] + 0 ){
				$src= render_link('')."data/files/{$metadata['METADATA'][$mkey]['ID']}.".strtolower(substr($mkey,5));
			}else {
				$src = htmlspecialchars_decode($metadata['METADATA'][$mkey]['URL']);
			}
			$img .= "<source src=\"{$src}\" type=\"video/$ext\" />";
	 			$vidfound = 1;
			}
			$img .= ' </video>';
			if(!$vidfound) 	$img =  "<div class=\"postTitle\" align=\"center\">No Viewable Data Type, Please download to view.</div>";
			$not_viewable = 1;

			break;
			case 'DATA_JPG':
			case 'DATA_JPEG':
			case 'DATA_GIF':
			case 'DATA_PNG':
				$distype="img";
				if(!$metadata['METADATA'][$defaulttype]['SIZE_X'] || !$metadata['METADATA'][$defaulttype]['SIZE_Y']){
					if($file_id){
						$tmpf = secure_tmpname(".$ext", "blog_", "/tmp");
						file_put_contents($tmpf, data_get_item($file_id));
						list($metadata['METADATA'][$defaulttype]['SIZE_X'], $metadata['METADATA'][$defaulttype]['SIZE_Y']) = getimagesize($tmpf);
						unlink($tmpf);
					}else{
						list($metadata['METADATA'][$defaulttype]['SIZE_X'], $metadata['METADATA'][$defaulttype]['SIZE_Y']) = getimagesize($path);
					}
					$metadata['METADATA']['SIZE_X'] = $metadata['METADATA'][$defaulttype]['SIZE_X'];
				$metadata['METADATA']['SIZE_Y'] = $metadata['METADATA'][$defaulttype]['SIZE_Y'];
	
				}
				if($datapage)
		   		global $imagepath;
					$imagepath = $path;
				break;

			case 'PLOT_TIME':
				if($datapage){
				$distype="timeplot";

				$topic = $metadata['METADATA']['PLOT_TIME']['TOPIC'];

				if ( strlen($topic) == 0 ) {
					$building = $metadata['METADATA']['PLOT_TIME']['BUILDING'];
					$room = $metadata['METADATA']['PLOT_TIME']['ROOM'];

				}

				else { 
					list ($building, $room) = split(":", $topic);
					$building = substr($building, 1);
					$room = substr($room, 0, -2);
				}

				$startTime = $metadata['METADATA']['PLOT_TIME']['START'];
				$endTime = $metadata['METADATA']['PLOT_TIME']['END'];
				}else{
					$img =  "<div class=\"postTitle\" align=\"center\">No Viewable Data Type, Please download to view.</div>";
					$not_viewable = 1;
				}
				break;
				

			case 'DATA_PDF':
				$img =  "<EMBED src=\"$path\" width=\"{$x}\" height=\"{$y}\"></EMBED>";
				$not_viewable = 1;
				break;
				
			case 'DATA_URL':
				$data = $path;
				$img =  "<iframe src=\"$data\" width=\"{$x}\" height=\"{$y}\"></iframe>";
				$not_viewable = 1;
				break;
			case 'DATA_ASC':
				if($file_id) $data = data_get_item($file_id); else
					$data = file_get_contents($path."&data_type=text");
				$img =  code_render_datapre($data,"txt");
				$not_viewable = 1;
				break;
			case 'DATA_PL':
			case 'DATA_PHP':
			case 'DATA_ASP':
			case 'DATA_SH':
			case 'DATA_C':
			case 'DATA_CPP':
			case 'DATA_CSS':
			case 'DATA_M':
			case 'DATA_SQL':
			case 'DATA_RB':
			case 'DATA_TXT':
			case 'DATA_XML':
			case 'DATA_RDF':
			case 'DATA_JAVA':
			if($file_id) $data = data_get_item($file_id); else
				$data = file_get_contents($path."&data_type=fdown");
				$img =  code_render_datapre($data,$ct_config['geshi']['lang_map'][$defaulttype]);
				$not_viewable = 1;
				break;

			case 'DATA_KML':

			if($ct_config['blog_map_key'] && $datapage){
				if($file_id) $data = data_get_item($file_id); else
				$data = $path."&filename=".rawurlencode($metadata['METADATA'][$defaulttype]['NAME']);
				
				$kml =  file_get_contents($data);

				preg_match_all("/(<coordinates>)([^<]*)(<\/coordinates>)/", $kml, $matchs, PREG_PATTERN_ORDER);
				foreach( $matchs[0] as $part){
					foreach(preg_split("/\s+/",strip_tags($part)) as $chunk){
						$chunk = preg_replace ("/\s+/", "", $chunk);
						if(strlen($chunk)){
						list($lng,$lat,$alt) = preg_split("/\s*,\s*/", $chunk);
						$longs[] = $lng;
						$lats[] = $lat;
						}
					}

				}
				$ave_lat = array_sum($lats) / count( $lats );
				$ave_lng = array_sum($longs) / count( $longs );
				
					
			
			$head .= '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$ct_config['blog_map_key'].'" type="text/javascript"></script>
<script type="text/javascript">

//<![CDATA[

function load() {
  if (GBrowserIsCompatible()) {
    var map = new GMap2(document.getElementById("map"));

  map.addControl(new GLargeMapControl());
  map.addControl(new GMapTypeControl());
	var rectObj = new GLatLngBounds(new GLatLng('.min($lats).','.min($longs).'), new GLatLng('.max($lats).','.max($longs).'));
	var zm = map.getBoundsZoomLevel(rectObj);	

  map.setCenter(new GLatLng('.$ave_lat.', '.$ave_lng.'), zm);

   var kml = new GGeoXml("'.$data.'");
	map.addOverlay(kml)
  }
}

//]]>
</script>';

		$body_tag = 'onload="load()" onunload="GUnload()"';
				$img =  '   <div id="map" style="width: {$x}; height: {$y}"></div>';
				$not_viewable = 1;
			}else{
				$img =  "<div class=\"postTitle\" align=\"center\">No Viewable Data Type, Please download to view.</div>";
				$not_viewable = 1;
			}				
				break;
			
			default:
				$img =  "<div class=\"postTitle\" align=\"center\">No Viewable Data Type, Please download to view.</div>";
				$not_viewable = 1;
				break;

		}

		return $img;
}



function setposttodata($dataid,$post){
	
	global $ct_config;		
	$sql = "SELECT `data_post`, `data_id`, `data_type` FROM  `{$ct_config['blog_db']}`.`blog_data` 	WHERE  `data_id` =$dataid";
	$result = runQuery($sql,'Fetch Data Item');
	if($datainfo = mysql_fetch_array($result)){
		if(!$datainfo['data_post']){
			$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_data` SET  `data_post` =  '$post' WHERE  `blog_data`.`data_id` = $dataid LIMIT 1;";
			runQuery($sql,'Update blog post id');
		}
		if($datainfo['data_type']=="data_meta"){
				$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_data` 	WHERE  `data_id` =$dataid";
				$result = runQuery($sql,'Fetch Data Item');
				$datainfo = mysql_fetch_array($result);
				$metadata = readxml($datainfo['data_data']);
			
				foreach($metadata['METADATA'] as $key=>$value){
					if((int)$value['ID']) setposttodata((int)$value['ID'],$post);
				}
		}
	}
	
	
	
}

?>
