<?php

	if(!isset($nosession) || !$nosession){
	$sess_time = 480;
	session_start();
	ini_set('session.gc_maxlifetime',$sess_time*60);
	}
	
	/* Used in setting titles on pages, rss feeds etc*/
	$ct_config['blog_title'] = "";
	$ct_config['blog_desc'] = "An out of the box instance of ModTrove";
	
	/* Sets the default style the labtrove instance */
	/* See wiki for 'how to' to customise */
	$ct_config['blog_style'] = "modtrove";
	
	/* Setting the defaults for the url of the blog */
	$ct_config['this_server'] = "";
	$ct_config['blog_path'] = "/";
	$ct_config['blog_protocol'] = "http"; /* Change to https if over https*/
	$ct_config['blog_url'] = "{$ct_config['blog_protocol']}://{$ct_config['this_server']}{$ct_config['blog_path']}";

	/* Set for path for the site title (this is the title top left, it may be changed to the institution website) */
	$ct_config['blog_site_url'] = $ct_config['blog_path'];

	/* Enter the usernames of the webmaster and contact */
	$ct_config['blog_webmaster'] = '';
	$ct_config['blog_contact'] = '';

	/* database connections*/
	$ct_config['blog_host'] = "localhost";
	$ct_config['blog_user'] = "dbuser";
	$ct_config['blog_pass'] = "dbpassword";
	$ct_config['blog_db'] = "labtrove";

	/* Security for whole blog, 0 = public, 1 = Logged in users only */
	/* other zones are possible but the need to set up */
	$ct_config['blog_zone'] = 0;
 
	/* number of post/page */
	$ct_config['no_blogs_page'] = 5;
	
	$ct_config['uri_server'] = $ct_config['this_server'];
	$ct_config['uri_db'] = $ct_config['blog_db'] ;
	
	/* any plugins */
	/* you change login_openid to login_ldap or login_local */
	$ct_config['plugins'] = array('login_openid', 'uri_samedb'); 
	
	/* what's new plugin */
	$ct_config['recentmode'] = "limit"; //either "latest" or "limit", latest is posts from the last 24 hours, limit uses the limits below
	
	$ct_config['newposts'] = 5;
	$ct_config['newcomments'] = 5; //number of comments to display on what's new page
	
	/* advanced editor (tinyMCE) plugin */
	$ct_config['editor_enabled'] = true;
	
	/* ChemSpider API functionality*/
	$ct_config['enablepredictive'] = true;
	$ct_config['chemspiderAPIkey'] = "yourkeyhere";
	$ct_config['wolframappID'] = "yourappidhere";
	
	
	/* user/members list options */
	$ct_config['userlistenabled'] = true;
	$ct_config['viewlevel'] = 3; //sets permissions for who can view the member's list (0=all,1=normal user,2=moderator,3=admin)
	
	/* enable/disable sortable tables */
	$ct_config['sortabletables'] = true;
	
	/* support for sharing links (fb, twitter, citeulike) */
	$ct_config['sharelinks'] = true;
	
	/* if you are using openid what can a new user do */
	$ct_config['openid']['default_user_type'] = 1;

	/* location of cache dir */
	$ct_config['cache_dir'] = "cache"; 

	/* Email settings, Comming soon */
	$ct_config['blog_enmsg'] = false;
	$ct_config['blog_msgdb'] = $ct_config['uri_db'];
	
	
	/* if dont have these installed then the blog wont try and autoconvert items */
	$ct_config['autoconv']['ffmpeg2theora'] = 1;
	$ct_config['autoconv']['ffmpeg'] = 1;
	$ct_config['autoconv']['convert'] = 1;


?>
