<?php

//Run Scripts (ideally once every hour)

include("../default_config.php");


chdir(dirname(__FILE__));

//Runs Garbage collection 1in24 (ideally once a day)

if(rand(0,23) == 1){
	`php garbage.php;`;
}


//Makes Sitemaps
if(rand(0,23) == 2){
	`php msitemaps.php;`;
}

?>