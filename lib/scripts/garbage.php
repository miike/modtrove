<?php

chdir(dirname(__FILE__));

include("../default_config.php");


if(!$ct_config['gc']['blogdumps']) $ct_config['gc']['blogdumps'] = 48;
if(!$ct_config['gc']['blogimgs']) $ct_config['gc']['blogimgs'] = 30;
if(!$ct_config['gc']['dataitems']) $ct_config['gc']['dataitems'] = 200;
// blogdumps;
$datapath = "{$ct_config['pwd']}/docs/cache/blogdumps/";
$files = scandir($datapath);
foreach ($files as $file){
	if($file{0}==".") continue;
	if(filectime($datapath.$file) > (time()-(3600*$ct_config['gc']['blogdumps']))) continue;
	`rm -fr {$datapath}{$file};`;
}

// blogimgs;
$datapath = "{$ct_config['pwd']}/docs/cache/blogimgs/";
$files = scandir($datapath);
foreach ($files as $file){
	if($file{0}==".") continue;
	if(is_dir($datapath.$file)) continue;
	if(filectime($datapath.$file) > (time()-(3600*24*$ct_config['gc']['blogimgs']))) continue;
	`rm -f {$datapath}{$file};`;
}


// datacache;
$datapath = "{$ct_config['pwd']}/docs/cache/";
$files = scandir($datapath);
foreach ($files as $file){
	if($file{0}==".") continue;
	if(is_dir($datapath.$file)) continue;
	if(filectime($datapath.$file) > (time()-(3600*24*$ct_config['gc']['dataitems']))) continue;
	`rm -f {$datapath}{$file};`;
}
?>