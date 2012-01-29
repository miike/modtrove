<?php

include("../lib/default_config.php");


if($_SESSION['user_admin'] > 1){
	echo "Not allowed!";
	exit();
}


	$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_bits` SET  `bit_cache` =  '' WHERE 1;";
	runQuery($sql,'Blogs');
	echo "I have cleared:".mysql_affected_rows();
		
?>