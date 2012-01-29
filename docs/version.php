<?php
	
 header ("content-type: text/plain");

 echo @file_get_contents("../install/version");

?>
