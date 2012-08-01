<?php

include("../lib/default_config.php");


if(isset($_REQUEST['uri']))
{
	$pathinfo = $_REQUEST['uri'];

	$ext = pathinfo($pathinfo , PATHINFO_EXTENSION);

	if($pathinfo == pathinfo($pathinfo , PATHINFO_FILENAME)){
		$_REQUEST['bit'] = (int)pathinfo($pathinfo , PATHINFO_BASENAME);
	}else{
		$pathinfos = explode("/",$pathinfo);
		$_REQUEST['bit'] = (int)pathinfo($pathinfos[0] , PATHINFO_BASENAME);
	}
		$_REQUEST['filename'] = pathinfo($pathinfo , PATHINFO_FILENAME);
}else{
	$_REQUEST['bit'] = (int)$_REQUEST['bit'];
}

//$sql = "SELECT * FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = '".(int)$_REQUEST['bit']."'";
//$tresult = runQuery($sql,'Fetch Page Groups');
$row = db_get_data_by_id($_REQUEST['bit']);





if($row['data_post']){
	$sql = "SELECT `bit_blog` FROM `{$ct_config['blog_db']}`.`blog_bits` WHERE `bit_id` = '{$row['data_post']}' AND `bit_edit` = 0";
	$result = runQuery($sql,'Fetch Page Groups');
	$postid = mysql_fetch_array($result);
	$_SESSION['blog_id'] = $postid['bit_blog'];
}

checkblogconfig($_SESSION['blog_id']);

if($_SESSION['blog_id']){

$blog = db_get_blog_by_id($_SESSION['blog_id']);

	if(!checkzone($blog['blog_zone'],0,$blog['blog_id'])){
		header("Location: {$ct_config['blog_path']}?msg=Forbidden!&turl=".urlencode($_SERVER["REQUEST_URI"]));
		exit();
	}
}


function emit_file($info)
{
	if( !isset($info['mode']) || $info['mode'] == 'database') // its in the database either implicitly or explicitly
	{
		echo $info['data_data'];
	}
	// elseif use cloud...
	else // its on the filesystem
	{
		header("Content-length:" . $info['filesize']);
		$fp = fopen($info['filepath'], 'rb');
		//fpassthru($fp); // takes a lot of memory
		while(!feof($fp))
		{
			$buffer = fread($fp, 10240); // 10k, upping this number lowers cpu load but raises memory footprint
			print $buffer;
		}
		fclose($fp);
	}
}


//$sql = "SELECT * FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = ".(int)$_REQUEST['bit'];
//$tresult = runQuery($sql,'Fetch Page Groups');
//$row = mysql_fetch_array($tresult);

if($_REQUEST['width']) $width = $_REQUEST['width']; else $width = 100;
if($_REQUEST['height']) $height = $_REQUEST['height'] ; else $heigth = 75;

if(isset($_REQUEST['fdata_type']) && strlen($_REQUEST['fdata_type'])) { $row['data_type']=$_REQUEST['fdata_type']; }

switch (strtolower($row['data_type'])) {

	case 'fdown':
		$tmpfname = tempnam("/tmp", "blog_");
		file_put_contents ( $tmpfname, $row['data_data']);
		$type = `file -ib $tmpfname`;
		unlink($tmpfname);

		header('Content-type: $type');
		// It will be called downloaded.pdf
		header('Content-Disposition: attachment; filename="'.$_REQUEST['filename'].'"');
		emit_file($row);
		break;
	case 'pdf':
		header('Content-type: application/pdf');
		emit_file($row);
	break;
		case 'jpg':
		case 'jpeg':
		header('Content-type: image/jpeg');
		if($_REQUEST['thumb']) echo resize_data($path,"{$width}x{$height}","jpg",$row['data_id']); else
		emit_file($row);
		break;

	case 'gif':
		header('Content-type: image/gif');
		if($_REQUEST['thumb']) echo resize_data($path,"{$width}x{$height}","gif",$row['data_id']); else
		emit_file($row);
		break;

	case 'png':
		if($_REQUEST['thumb']) echo resize_data($path,"{$width}x{$height}","png",$row['data_id']);

		else header('Content-type: image/png');

		emit_file($row);
		break;

	case 'text':

		header('Content-type: text/plain');
		emit_file($row);
		break;

	case 'data_meta':
			
		$metadata = readxml($row['data_data']);
	foreach($metadata['METADATA'] as $key=>$value){
		
				if(substr($key,0,5) == 'DATA_' || substr($key,0,5) == 'PLOT_'){
					if(!isset($defaulttype_bk) || !$defaulttype_bk)
						$defaulttype_bk = $key;
					
					switch($key){
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
						case 'DATA_M':
						case 'DATA_SQL':
						case 'DATA_RB':
						case 'DATA_TXT':
						case 'DATA_XML':
						case 'DATA_RDF':
						case 'DATA_JAVA':
						case 'DATA_KML':
					if(!isset($defaulttype) || !$defaulttype)
						$defaulttype = $key;
					if($value['MAIN']==1){
						$defaulttype = $key;
					}

					}

					$datalist[$key] = $value;

				}

			}	
				if(!isset($defaulttype)) { $defaulttype = $defaulttype_bk; }


		if( is_int( ($metadata['METADATA'][$defaulttype]['ID']+0)) ) {
	
			$path = "http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?bit=".$metadata['METADATA'][$defaulttype]['ID'];
		}
		
		else { $path = $metadata['METADATA'][$defaulttype]['URL']; }


		if (isset($_REQUEST['resolve']) && $_REQUEST['resolve']) {

			echo file($path);
			exit();

		}

		if($_REQUEST['thumb']) {

			switch (strtoupper($defaulttype)) {
	
			case 'M':
				header('Location: inc/mlab.jpg');
				exit();
		
			case 'DATA_PDF':
				header('Location: inc/pdf.jpg');
				exit();
				break;

				case 'DATA_KML':
				header('Location: inc/kml.jpg');
				exit();
				break;

			case 'DATA_PPT':
				header('Location: inc/ppt.jpg');
				exit();
				break;
		
			case 'DATA_DOC':
			case 'DATA_DOCX':
				header('Location: inc/doc.jpg');
				exit();
				break;
		
			case 'DATA_XLS':
			case 'DATA_XLSX':
				header('Location: inc/xls.jpg');
				exit();
				break;
		
			case 'DATA_EPS':
				header('Location: inc/eps.jpg');
				exit();
				break;
		
			case 'DATA_JPG':
			case 'DATA_JPEG':
				header('Content-type: image/jpeg');
		   		echo resize_data($path,"{$width}x{$height}","jpg",$row['data_id']);
  				exit();
				break;
		    
			case 'DATA_GIF':
				header('Content-type: image/gif');
				echo resize_data($path,"{$width}x{$height}","gif",$row['data_id']);
				exit();
			   	break;
		
			case 'DATA_PNG':
				header('Content-type: image/png');
		   		echo resize_data($path,"{$width}x{$height}","png",$row['data_id']);
				exit();
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
				case 'DATA_XML':
				case 'DATA_RDF':
				case 'DATA_ASC':
				case 'DATA_JAVA':
				
				header('Location: inc/code.jpg');
				exit();
			   	break;
			case 'DATA_AVI':
			case 'DATA_MP4':
			case 'DATA_M4V':
			case 'DATA_FLV':
			case 'DATA_MOV':
			case 'DATA_MPG':
			case 'DATA_OGG':
			case 'DATA_WMV':	
				header('Location: inc/video.jpg');
				exit();
			   	break;
				
			default:
				header('Location: inc/data.jpg');
				exit();

			}
		
		}
		
		else {

			switch ($defaulttype) {
	

			case 'DATA_JPG':
				header('Content-type: image/jpeg');
		   		echo file_get_contents($path);
				break;

		    case 'DATA_GIF':
				header('Content-type: image/gif');
		  		echo file_get_contents($path);
			   	break;
		
			case 'DATA_PNG':
				header('Content-type: image/png');
		   		echo file_get_contents($path);
			   	break;

			default:
				echo "No Suitible Data Type";
				break;
			
			}
	
		}
	 	break;

	case 'img_meta':

		$metadata = readxml($row['data_data']);

		$path = $metadata['METADATA']['PREVIEW_SRC'];

		echo file_get_contents($path);
		exit();
		break;

	default:

		$tmpfname = tempnam("/tmp", "blog_");
		file_put_contents ( $tmpfname, $row['data_data']);
		$type = `file -ib $tmpfname`;
		unlink($tmpfname);
		header('Content-type: $type');
//		header('Content-Disposition: attachment');

		//header('Content-Length: '.strlen($row['data_data']));
		emit_file($row);
		break;
}

?>
