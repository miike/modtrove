<?php
include("../../default_config.php");

$path = $argv[1];
$ext= $argv[2];
$id = (int)$argv[3];


	$tmpfname_a = secure_tmpname(".$ext", "blog_", "/tmp");
	$tmpfname_b = secure_tmpname(".ogg","blog_","/tmp");
		file_put_contents ( $tmpfname_a, file_get_contents($path));
	`ffmpeg2theora --videoquality 7 --audioquality 1 --output={$tmpfname_b} {$tmpfname_a} &> /dev/null`;
	$newid = add_data('ogg',file_get_contents($tmpfname_b));
	unlink($tmpfname_a);
	unlink($tmpfname_b);
	
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_data` WHERE  `data_id` = {$id}";
	$tresult = runQuery($sql,'');
	$data = mysql_fetch_array($tresult);
	$mdata = readxml($data['data_data']);
	
	$ikey = "DATA_".strtoupper($ext);
	unset($mdata['METADATA'][$ikey]['MAIN']);
	if($mdata['METADATA'][$ikey]['NAME']){
		$mdata['METADATA']['DATA_OGG']['NAME'] = pathinfo($mdata['METADATA'][$ikey]['NAME'],PATHINFO_FILENAME).".ogg";
	}
		$mdata['METADATA']['DATA_OGG']['TYPE'] = "local";
		$mdata['METADATA']['DATA_OGG']['ID'] = $newid;
		$mdata['METADATA']['DATA_OGG']['VOLATILE'] = 1;
	

	$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_data` SET  `data_data` =  '".addslashes(writexml($mdata))."' WHERE  `blog_data`.`data_id` = {$id} LIMIT 1 ;";
	runQuery($sql,'');
	
?>
