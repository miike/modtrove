<?php

	$dir = dirname(__FILE__);
	$dirup = dirname($dir);
	
	chdir($dir);

    if($argv[1] == "auto")
        `rsync --exclude-from updatefilter.txt -ac --delete rsync.labtrove.org::labtrove-auto $dirup`;
    else
    	`rsync --exclude-from updatefilter.txt -ac --delete rsync.labtrove.org::labtrove $dirup`;

?>