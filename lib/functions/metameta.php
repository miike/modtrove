<?php
//Live copy stuff
function meta_meta($blog_id, $pagename = "", $construct = NULL){

global $ct_config,$request;

if(!$pagename) $pagename = $request['blog_sname'];



$ret = "";
if(!$construct)
$construct = meta_metas($blog_id);

if(is_array($construct))
foreach(@$construct as $key=>$value){
	
	if(!@in_array(strtolower($key), $ct_config['blog_hide_meta'] )){

	$meta_part = strtotitle(str_replace("_"," ",$key));
$ret .= "<div class=\"infoSection\">".$meta_part."</div>";

	foreach($value as $val=>$count){
		$vals = ucwords(str_replace("_"," ",$val));
		if($request['meta']==$key && $request['value']==$val)
		$ret .= "\t\t".$vals." <span class=\"num_posts\">(".$count.")</span><br/>\n";
		else
		$ret .= "\t\t<a href=\"".render_link($pagename,array('meta' => $key , 'value' => $val))."\">".$vals."</a> <span class=\"num_posts\">(".$count.")</span><br/>\n";

	}

	}

}


return $ret;
}


function meta_metas($blog_id){

global $ct_config;

$sql = "SELECT `bit_meta` FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_meta` LIKE '%<meta>%</meta>%' AND  `bit_blog` = $blog_id AND bit_edit = 0";

$tresult = runQuery($sql,'Fetch Page Groups');
    while($row = mysql_fetch_array($tresult)){

		$metadata = readxml($row['bit_meta']);
		$metadata = $metadata['METADATA']['META'];	
		if(is_array($metadata))
		foreach($metadata as $key => $value){
			 $splitvalue = split(";",$value);
                foreach($splitvalue as $thebit)
                  $construct[$key][$thebit] = $construct[$key][$thebit] +1;
		}
}

return isset($construct)?$construct:NULL;
}

function meta_metac($blog_id){

	global $ct_config;

			$construct = meta_metas($blog_id);
			if(is_array($construct))
			foreach($construct as $key => $value){
				$xml .= "\t\t<$key>\n";
					$i = 0;				
					foreach($value as $kk=>$vv){
						$xml .= "\t\t\t<i$i><name>$kk</name><count>$vv</count></i$i>\n";
						$i++;
					}
				$xml .= "\t\t</$key>\n";
			}

return $xml;
}

?>
