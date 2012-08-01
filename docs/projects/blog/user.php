<?php
include('../../../config.php');
include('../../../lib/functions.php');

$pathinfo = $_SERVER['PATH_INFO'];
if($pathinfo{0} == '/'){
	$pathinfo = substr($pathinfo,1);
	$pathinfo = explode("/",$pathinfo);

	while($request[array_shift($pathinfo)] = addslashes(array_shift($pathinfo)));

}

header("Location: {$ct_config['blog_path']}user/{$request['user']}",TRUE,301);

?>
