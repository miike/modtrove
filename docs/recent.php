<?
//code for what's new
include("../config.php");
include("../lib/functions_database.php");
include("../lib/functions_blog.php");
include("../lib/functions.php");

function nicetime($date)
{
    if(empty($date)) {
        return "No date provided";
    }
    
    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60","60","24","7","4.35","12","10");
    
    $now             = time();
    $unix_date         = strtotime($date);
    
       // check validity of date
    if(empty($unix_date)) {    
        return "Bad date";
    }

    // is it future date or past date
    if($now > $unix_date) {    
        $difference     = $now - $unix_date;
        $tense         = "ago";
        
    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }
    
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
    
    $difference = round($difference);
    
    if($difference != 1) {
        $periods[$j].= "s";
    }
    
    return "$difference $periods[$j] {$tense}";
}

function nocomments($comid){
	//returns the number of comments on apost;
	$query = "SELECT COUNT(`com_id`) FROM `blog_com` WHERE `com_bit` = $comid LIMIT 100";
	$result = runQuery($query, "get number of comments");
	$row = mysql_fetch_assoc($result);
	return $row['COUNT(`com_id`)'];	
}

function getName($username){ //gets full name based on a username
	//can't escape username otherwise it will malform openID characters
	$query = "SELECT `user_fname` FROM `users` WHERE `user_name` = '$username' LIMIT 1";
	$result = runQuery($query, "get fullname");
	$row = mysql_fetch_assoc($result);
	$name = $row['user_fname'];
	return $name;
}

function blogName($blogid){//gets blog information based off a blog id
	$query = "SELECT `blog_name`, `blog_sname`, `blog_desc` FROM `blog_blogs` WHERE `blog_del` = 0 AND `blog_id` = $blogid LIMIT 1";
	$result = runQuery($query, "get blog details");
	$row = mysql_fetch_assoc($result);
	$details = array("name"=>$row['blog_name'], "sname"=>$row['blog_sname'], "desc"=>$row['blog_desc']);
	return $details;
}

include("style/{$ct_config['blog_style']}/blogstyle.php"); //this needs to be done first.

$blogpost['post'] = ""; //set empty

if ($ct_config['recentmode'] == "latest"){ //displa posts from last 24 hours
	$query = "SELECT DISTINCT  `blog_bits`.`bit_title` ,  `blog_blogs`.`blog_sname` ,  `blog_bits`.`bit_uri`, `blog_bits`.`bit_timestamp`, `blog_bits`.`bit_user`, `blog_bits`.`bit_blog`
	FROM  `blog_blogs` ,  `blog_bits` 
	WHERE  `blog_bits`.`bit_blog` =  `blog_blogs`.`blog_id` 
	AND  `blog_bits`.`bit_edit` =0 AND `bit_timestamp` > DATE_SUB( NOW(), INTERVAL 24 HOUR)
	ORDER BY  `blog_bits`.`bit_timestamp` DESC"; 
	$blogpost['title'] = "What's new (last 24 hours)";
}
else{ //diplay posts using limits
	$blogpost['title'] = "What's new";
	$query = "SELECT DISTINCT  `blog_bits`.`bit_title` ,  `blog_blogs`.`blog_sname` ,  `blog_bits`.`bit_uri`, `blog_bits`.`bit_timestamp`, `blog_bits`.`bit_user`, `blog_bits`.`bit_blog`
	FROM  `blog_blogs` ,  `blog_bits` 
	WHERE  `blog_bits`.`bit_blog` =  `blog_blogs`.`blog_id` 
	AND  `blog_bits`.`bit_edit` =0
	ORDER BY  `blog_bits`.`bit_timestamp` DESC 
	LIMIT " . $ct_config['newposts'];
}


$result = runQuery($query, "recent posts");
while ($row = mysql_fetch_assoc($result)){ //this selects the most recent blog posts
	$name = getName($row['bit_user']);
	$blogdetails = blogName($row['bit_blog']);
	$blogpost['post'] .= "<a href='user/" . $row['bit_user'] . "'>" . $name . "</a> posted " . "<a href='uri/" . dechex($row['bit_uri']) . "'>" . $row['bit_title'] . "</a>   " . nicetime($row['bit_timestamp']) . " in <a href='/" . $blogdetails['sname'] . "' alt='" . $blogdetails['desc'] . "'>" . $blogdetails['name']. "</a>";
	
	$blogpost['post'] .= "</br>";
}


//and this loop gets the most recent comments
$body .= blog_style_post(&$blogpost); //this should be called after every "post" (2 posts on this page)

$blogpost['post'] = "";
$blogpost['title'] = "Recent comments";
$query = "SELECT  `com_id` ,  `com_bit` ,  `com_title` ,  `com_datetime` ,  `com_cont`, `bit_uri`, `com_user`
FROM  `blog_com` 
INNER JOIN  `blog_bits` ON  `com_bit` =  `bit_rid` 
ORDER BY  `com_datetime` DESC 
LIMIT ". $ct_config['newcomments'];
$result = runQuery($query, "recent comments");
while ($row = mysql_fetch_assoc($result)){
	$number = nocomments($row['com_bit']);
	$name = getName($row['com_user']);
	$blogpost['post'] .= "<a href='user/" . $row['com_user'] . "'>" . $name . "</a> commented on <a href='uri/" . dechex($row['bit_uri']) . "'>" . $row['com_title'] . " (" . $number . " comments)</a>   " . nicetime($row['com_datetime']);
	$blogpost['post'] .= "</br>";
}

$body .= blog_style_post(&$blogpost); //this should be called after every "post" (2 posts on this page)



include("page.php");


?>
