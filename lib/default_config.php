<?php

	/* default config */
	/* DO NOT EDIT: you can overide anything in ../config.php */
	/* This will get over written by any update! */
	
	$ct_config['label_server'] = false;
	
	/* southampton uni chemtool login system only */
	$ct_config['soap_host'] = "";
	$ct_config['soap_login'] = "";
	
	/* order for side bar */
	$ct_config['blog_infobar'] = array("search","thispost","thisblog","archives","authors","sections","meta","tools");
	
	/* list of no nos for the blog short name */
	$ct_config['protected_paths'] = array("api","data", "search","style","inc","admin","user","java");
	
	/* garbage collection */
	$ct_config['gc']['blogdumps'] = 48; //hours
	$ct_config['gc']['blogimgs'] = 30; //days
	$ct_config['gc']['dataitems'] = 200; //days
	
	/* remember me cookie length */
	$ct_config['rememberme']['time'] = 30; //days
	$ct_config['rememberme']['salt'] = md5("labtrovesalt");
	
	/* caching of user info (Some login pliugins only) */
	$ct_config['usercache']['enable'] = 0;
	$ct_config['usercache']['limit'] = 604800; //seconds (7 days = 604800)
	
	/* legacy */
	$ct_config['timeplot'] = "http://middleware.chem.soton.ac.uk/data/chemtools_labrecall.php";
	
	/* dev mode */
	$ct_config['devo'] = 0;
	
	$ct_config['pwd'] = dirname(dirname(__FILE__));
	
	/* use mysql full text search engine, requires "alter table blog_bits add fulltext (bit_content, bit_title);" */
	/* disabled=0, enabled=1, enabled with stemmed queries=2 */
	$ct_config['use_mysql_fulltext_search'] = 0;
	
        /* This section controls the way larger file uploads are treated
	 * - You may need to upgrade your database schema to use this feature
         * - You will need to update php.ini to allow for larger file transfers, see the following settings
             max_execution_time, max_input_time, memory_limit, post_max_size, upload_max_filesize
         */
	/* has the database schema been updated? */
	$ct_config['uploads_db_update'] = false;
	/* the file size where upon the file is stored on the filesystem rather than the database, set to 0 for always in database */
	// $ct_config['uploads_threshold'] = 1024 * 1024; // 1Mb
	$ct_config['uploads_threshold'] = 0;
	/* where uploaded files are stored on the filesystem - make sure this exists and is writable */
	$ct_config['uploads_dir'] = "{$ct_config['pwd']}/docs/uploads";
	/* the max size an upload can be */
	$ct_config['uploads_max_size'] = 1024 * 1024 * 1024 ; // 1Gb

	// sets config file if envset
	if($configstr = getenv("LABTROVE_CONFIG")){
		if(file_exists($configstr)){
			$ct_config['config_file'] = $configstr;
		}else{
			die("LABTROVE ERROR: config file \"{$configstr}\" does not exist! (Set by \$LABTROVE_CONFIG)");
		}
	}else{
		$configstr = $ct_config['pwd']."/config.php";
		if(file_exists($configstr)){
			$ct_config['config_file'] = $configstr;
		}else{
			die("LABTROVE ERROR: config file \"{$configstr}\" does not exist! (No env \$LABTROVE_CONFIG set assumed its $configstr)");
		}
	}
	
	// sets config file if envset
	if($configstr = getenv("LABTROVE_CONFIG_DIR")){
		if(file_exists($configstr)){
			$ct_config['config_dir'] = $configstr;
		}else{
			die("LABTROVE ERROR: config directory \"{$configstr}\" does not exist! (Set by \$LABTROVE_CONFIG_DIR)");
		}
	}else{
		$configstr = $ct_config['pwd']."/config";
		if(file_exists($configstr)){
			$ct_config['config_dir'] = $configstr;
		}else{
			die("LABTROVE ERROR: config directory \"{$configstr}\" does not exist! (No env \$LABTROVE_CONFIG_DIR set assumed its $configstr)");
		}
	}
	
	
	include("{$ct_config['config_file']}");
	include("{$ct_config['pwd']}/lib/functions.php");
	include("{$ct_config['pwd']}/lib/functions_blog.php");
	include_once("{$ct_config['pwd']}/lib/functions_database.php");
	
	
	prep_get();
	
	// Labtrove requires magic quotes for the moment.
	// Will skip magic quotes if $ct_config['skip_magic_quotes']
	if (!get_magic_quotes_gpc()) {
		if(!isset($ct_config['skip_magic_quotes']) || !$ct_config['skip_magic_quotes']){
	    	real_db_escape($_GET);
	    	real_db_escape($_POST);
	    	real_db_escape($_COOKIE);
	    	real_db_escape($_REQUEST);
	    }
		// does not include $_FILES
	}else{
		if(isset($ct_config['skip_magic_quotes']) && $ct_config['skip_magic_quotes']){
	    	real_db_unescape($_GET);
	    	real_db_unescape($_POST);
	    	real_db_unescape($_COOKIE);
	    	real_db_unescape($_REQUEST);
	    }	
	}
	
	
?>
