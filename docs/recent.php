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
	$result = runQuery($query);
	$row = mysql_fetch_assoc($result);
	return $row['COUNT(`com_id`)'];	
}

include("style/{$ct_config['blog_style']}/blogstyle.php"); //this needs to be done first.

$blogpost['title'] = "What's new";
$blogpost['post'] = ""; //set empty

$query = "SELECT DISTINCT  `blog_bits`.`bit_title` ,  `blog_blogs`.`blog_sname` ,  `blog_bits`.`bit_uri`, `blog_bits`.`bit_timestamp`
FROM  `blog_blogs` ,  `blog_bits` 
WHERE  `blog_bits`.`bit_blog` =  `blog_blogs`.`blog_id` 
AND  `blog_bits`.`bit_edit` =0
ORDER BY  `blog_bits`.`bit_timestamp` DESC 
LIMIT " . $ct_config['newposts'];

$result = runQuery($query, "recent posts");
//echo $query;
while ($row = mysql_fetch_assoc($result)){ //this selects the most recent blog posts
	$blogpost['post'] .= "<a href='uri/" . dechex($row['bit_uri']) . "'>" . $row['bit_title'] . "</a>   " . nicetime($row['bit_timestamp']);
	$blogpost['post'] .= "</br>";
}


//and this loop gets the most recent comments
$body .= blog_style_post(&$blogpost); //this should be called after every "post" (2 posts on this page)

$blogpost['post'] = "";
$blogpost['title'] = "Recent comments";
$query = "SELECT  `com_id` ,  `com_bit` ,  `com_title` ,  `com_datetime` ,  `com_cont`, `bit_uri`
FROM  `blog_com` 
INNER JOIN  `blog_bits` ON  `com_bit` =  `bit_rid` 
ORDER BY  `com_datetime` DESC 
LIMIT ". $ct_config['newcomments'];
$result = runQuery($query, "recent comments");
while ($row = mysql_fetch_assoc($result)){
	$number = nocomments($row['com_bit']);
	$blogpost['post'] .= "<a href='uri/" . dechex($row['bit_uri']) . "'>" . $row['com_title'] . " (" . $number . " comments)</a>   " . nicetime($row['com_datetime']);
	$blogpost['post'] .= "</br>";
}

$body .= blog_style_post(&$blogpost); //this should be called after every "post" (2 posts on this page)



include("page.php");


?>
