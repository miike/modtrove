<?php
//globals
$imagemeta = array(); //contains unique absolute URLs for all images
$bitids = array();
$ids = array();

//connect to the database
$username = "dbuser";
$password = "exclamation12";
$dbname = "labtrove";
$link = mysql_connect('localhost', $username, $password);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_select_db($dbname);


//first find all the posts with an img tag (SQL)
$searchterm = "%img%";
$sql = "SELECT `bit_id`, `bit_content` FROM `blog_bits` WHERE `bit_content` LIKE '" . $searchterm . "'"; //revisions!
//iterate through and find all instances
$imgregex = '/img src=\".*\"/';
$result = mysql_query($sql);
echo "Scanning for images...</br>";

//scan();
insert(3, "test");

function scan(){ //finds images in the SQL result, uses osra.php to retrieve metadata and then places it into the global arrays.

	while ($row = mysql_fetch_assoc($result)){ //iterate through all posts containing an img tag
		$postcontent = $row['bit_content'];
		$bitid = $row['bit_id'];
		preg_match_all($imgregex, $postcontent, $matches);
		foreach($matches as $url){ //find all img tags within a post
			$split = explode('"', $url[0]);
			$acturl = $split[1];
			
			//get the id of the url
			$split2 = explode("/", $acturl);
			$id = $split2[sizeof($split2)-2];
			
			//if it isn't a repeat then push it to the metadata array
			if (in_array($id, $ids) === false){
				//then add to the metadata array
				array_push($imagemeta, $acturl);
				array_push($bitids, $bitid);
			}
			
			array_push($ids, $id);
			
		}
	}
	mysql_close($link);
	echo "Found " . sizeof($imagemeta) . " images.</br>";

	print_r($imagemeta);
	//begin curl session
	$ch = curl_init();
	echo "Starting to resolve structures...</br>";
	foreach($imagemeta as $structure){
		echo "Resolving " . $structure . "...</br>";
		curl_setopt($ch, CURLOPT_URL, "http://103.1.186.131/osra/osra.php?pass=osraread&url=" . $structure);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($ch);
		echo $result . "</br>----------------</br>";
	}
	curl_close($ch);
}

function insert($postid, $text){ #insert metadata into a given post using $postid
	//need to do something with bit_cache?
	//bit_cache is just an html version of exactly the same stuff.
	//!!!note we need to fetch the first row of the most recent revision...!!!, below probably does this.
	$contentsql = "SELECT `bit_content` FROM `blog_bits` WHERE `bit_id` = " . $postid . " ORDER BY `bit_rid` DESC LIMIT 1";
	$result = mysql_query($contentsql);
	$row = mysql_fetch_assoc($result);
	$content = $row['bit_content'];
	#//ust append demo content for the moment.
	$text = "<p>Chemical information</p>";
	$updatesql = "UPDATE `blog_bits` SET `bit_content`='" . $content . $text . "' WHERE `bit_id` = " . $postid;
	echo $updatesql;
	//execute sql
	//mysql_query($updatesql);
}


?>