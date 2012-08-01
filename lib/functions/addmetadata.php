<?php
@include_once('../../../functions.php');
@include_once('../functions.php');
function addmetadata($data_blob,$bit_id){
global $ct_config;
		$data_blob = str_replace("\n","",$data_blob);
		$metad = base64_decode($data_blob);
		$metada = readxml($metad);

		if($metada['METADATA']['TYPE']){
			$dtype = $metada['METADATA']['TYPE'];
		}else{
			$dtype = 'img_meta';
		}	
		
		$metad = $metada;
		foreach($metad['METADATA'] as $key=>$value){
	
			if(substr($key,0,5) == 'DATA_'){
	
				if($value['TYPE']=="inline" && strlen($value['DATA'])){
						$sql = "INSERT INTO `{$ct_config['blog_db']}`.`blog_data` (`data_id`, `data_datetime`, `data_type`, `data_data`) VALUES (NULL, NOW(), '".strtolower(substr($key,5))."', '".addslashes(base64_decode($value['DATA']))."');";
					runQuery($sql,'add inline data');
					$id = mysql_insert_id();
					$metada['METADATA'][$key]['TYPE']="local";
					unset($metada['METADATA'][$key]['DATA']);
					$metada['METADATA'][$key]['ID']=$id;
				}
			}

		}

		$metad = writexml($metada);

		$sql = "INSERT INTO `{$ct_config['blog_db']}`.`blog_data` (`data_id`, `data_datetime`, `data_type`, `data_data`) VALUES (NULL, NOW(), '".addslashes($dtype)."', '".addslashes($metad)."');";

		runQuery($sql,'Blogs');

		$id = mysql_insert_id(); 

		$sql =	"SELECT  * FROM  `{$ct_config['blog_db']}`.`blog_bits` ";
		$sql .= "WHERE `bit_id` = ".$bit_id." AND `bit_edit` = 0";

		$tresult = runQuery($sql,'Blogs');

		$row = mysql_fetch_array($tresult);

		$metadata = readxml($row['bit_meta']);
		
		if(isset($metadata['METADATA']['DATA'])){
		$metadata['METADATA']['DATA'] .= ",".$id;
		}else{
		$metadata['METADATA']['DATA'] = $id;
		}

		$meta = null;
		$metad = writexml($metadata);
		
		$new_id = edit_blog($bit_id, 'Added Data', NULL, NULL, $metad, NULL);
		return $new_id;
}

?>