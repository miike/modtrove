<?php

include("../default_config.php");


$sql = "SELECT `bit_id`,`bit_meta` FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_edit` = 0";

$result = runQuery($sql,'Fetch Data Item');
while($post = mysql_fetch_array($result)){

	$metadata = readxml($post['bit_meta']);
	if($metadata['METADATA']['DATA']){
		$datas = NULL;
		$datas = split(",",$metadata['METADATA']['DATA']);
		foreach($datas as $bit){
			setposttodata((int)$bit,$post['bit_id']);
			echo "$bit=>{$post['bit_id']}\n";
		}
	}


}


?>
