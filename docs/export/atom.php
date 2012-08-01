<?php

$atompagelimit = 25;
$tresult = runQuery($sqlb,'Fetch Page Count');
$row = mysql_fetch_array($tresult);
$countblog = $row['bcount'];

if(!isset($limitt)){
if($countblog > $atompagelimit){
	if($request['page']){
		$limitt = $atompagelimit*($request['page']-1).", ".$atompagelimit;	
		}else{
		$limitt = $atompagelimit;
	}
}else{
	$limitt = $atompagelimit;
}
}

if($limitt){
	$limittext = "Limit $limitt";
}

$sql .= "\n ORDER BY  `bit_datestamp` DESC, `bit_timestamp` DESC $limittext ";

$postnumb = 0;
$tresult = runQuery($sql,'Fetch Page Groups');
$noofposts = mysql_num_rows($tresult);


// /xmlns="http://www.w3.org/2005/Atom"/
$feed = new SimpleXMLExtend(<<<END
<?xml version="1.0" encoding="UTF-8"?>
<feed />
END
);


if($request['page']==1){ unset($request['page']); }
$url = render_link($blog['blog_sname'],$request);
if(!$request['page']) $request['page'] = 1;
//$id = atomtag("{$url}/index.atom",substr($blog['blog_created'],0,10));
$id = atomtag("{$url}/index.atom","2005");

$feed->addChild('id', $id);
$link = $feed->addChild('link');
	$link->addAttribute('type', "text/html");
	$link->addAttribute('href', "$url");
	$link->addAttribute('rel', "alternate");
$link = $feed->addChild('link');
	$link->addAttribute('type', "application/atom+xml");
	$link->addAttribute('href', "{$url}/index.atom");
	$link->addAttribute('rel', "self");
$feed->addChild('title', $blog['blog_name'].' - '.strip_tags($ct_config['blog_title']));
$feed->addChild('updated', date(DATE_RFC3339));

$request['page']=$request['page']-1;
if($request['page'] > 0){
	$link = $feed->addChild('link');
		$link->addAttribute('type', "application/atom+xml");
		$turl = render_link($blog['blog_sname'],$request);
		$link->addAttribute('href', "{$turl}/index.atom");
		$link->addAttribute('rel', "previous");
}
$request['page']=$request['page'] +2;
if(($request['page']-1)<($countblog/$atompagelimit)){
	$link = $feed->addChild('link');
		$link->addAttribute('type', "application/atom+xml");
		$turl = render_link($blog['blog_sname'],$request);
		$link->addAttribute('href', "{$turl}/index.atom");
		$link->addAttribute('rel', "next");
}
$request['page'] = $request['page']-1;

while($post = mysql_fetch_array($tresult)){
	$entry = $feed->addChild('entry');
	$eurl = render_blog_link($post['bit_id'],true);
	$eid = atomtag($eurl,date("Y-m-d",$post['datetime']));
	$entry->addChild('id', $eid);
	$entry->addChild('published', date(DATE_RFC3339, $post['datetime'] ));	
	$link = $entry->addChild('link');
		$link->addAttribute('type', "text/html");
		$link->addAttribute('href', "{$eurl}");
		
		
	$entry->addChild('title', $post['bit_title']);
	if(!$post['bit_cache'] || $_REQUEST['nocache']){
		$post['content'] = makepostcache($post);
	}else{
		$post['content'] =  $post['bit_cache'];
	}
	$content = $entry->addCData('content', $post['content'], array("type"=>"html","xml:base"=>$ct_config['blog_url'] ));
	$entry->addChild('updated', date(DATE_RFC3339, $post['timestamp'] ));	
	
	$user = $entry->addChild('author');
		$user->addChild('username',$post['bit_user']);
		$user->addChild('name',get_user_info($post['bit_user'],"name"));
		$user->addChild('uri',$ct_config['blog_url']."user/{$post['bit_user']}");
	
	$link = $entry->addChild('link');
			$link->addAttribute('type', "image/png");
			$link->addAttribute('href', substr($eurl,0,-4)."png");
			$link->addAttribute('rel', "alternate");

}



//$posts->addChild('sql', $sql);

/*

$tresult = runQuery($sql,'Fetch Page Groups');
$noofposts = mysql_num_rows($tresult);

while($post = mysql_fetch_array($tresult)){

$xpost = $posts->addChild('post');

if(!$post['bit_cache']) $post['bit_cache'] = makepostcache($post);

$xpost->addChild('id', $post['bit_id']);
$xpost->addChild('rid', $post['bit_rid']);
$xpost->addChild('title', $post['bit_title']);
$xpost->addChild('section', $post['bit_group']);
$user = $xpost->addChild('author');
$user->addChild('username',$post['bit_user']);
$user->addChild('name',get_user_info($post['bit_user'],"name"));
$xpost->addCData('content', $post['bit_content']);
$xpost->addCData('html', $post['bit_cache']);
$xpost->addChild('datestamp', date("c",$post['datetime']));
$xpost->addChild('timestamp', date("c",$post['timestamp']));
$xpost->addChild('blog', $post['bit_blog']);
$xpost->addChild('key', $post['bit_md5']);

$timemin = min($timemin, $post['datetime']);
$timemax = max($timemax, $post['datetime']);

if($post['bit_meta']){
	$metaxml = readxml($post['bit_meta']);
	if(is_array($metaxml['METADATA']['META'])){
		$metadata = $xpost->addChild('metadata');
		foreach($metaxml['METADATA']['META'] as $met=>$key)
			$metadata->addChild(strtolower($met),$key);
	}
	if(isset($metaxml['METADATA']['DATA'])){
		$metadata = $xpost->addChild('attached_data');
		foreach(split(",",$metaxml['METADATA']['DATA']) as $key)
			$metadata->addChild('data',render_link("data/".$key.".xml"));
	}

	





}

$urllink = render_blog_link($post['bit_id'],ture );

$links = $xpost->addChild('links'); 
$links->addChild('uri', get_uri_url($post['bit_id']));
$links->addChild('permalink', $urllink);
$formats = $xpost->addChild('formats');
$fmat = $formats->addChild('format', $urllink);
$fmat->addAttribute('type', "text/html"); 
$fmat = $formats->addChild('format', substr($urllink,0,-4)."xml");
$fmat->addAttribute('type', "text/xml");
if($_REQUEST['inline']){
	$fxml = 1;	
	include('png.php');
	$fmat = $formats->addChild('format', base64_encode(file_get_contents($fname)));
	$fmat->addAttribute('encoding', "base64");
	$fmat->addAttribute('type', "image/png");
}else{
$fmat = $formats->addChild('format', substr($urllink,0,-4)."png");
$fmat->addAttribute('type', "image/png");
}



$comments = $xpost->addChild('comments');

	$sql = "SELECT *,UNIX_TIMESTAMP(  `com_datetime` ) AS datetime FROM  `{$ct_config['blog_db']}`.`blog_com` 
				WHERE  `com_bit` = ".(int)$request['bit_id']."   AND `com_edit` =0 ORDER BY `com_datetime` ASC";
		$result = runQuery($sql,'Fetch Comments');
		while($comts = mysql_fetch_array($result)){
				$comment = $comments->addChild("comment");
				$comment->addChild('id', $comts['com_id']);
				$user = $comment->addChild('author');
					$user->addChild('username',$comts['com_title']);
					$user->addChild('name',get_user_info($comts['com_title'],"name"));
				$comment->addChild('title', $comts['com_title']);
				$comment->addChild('content', $comts['com_cont']);
				$comment->addChild('html', bbcode($comts['com_cont']));
				$comment->addChild('datestamp', date("c",$comts['datetime']));
		}

}

*/
//$posts->addAttribute('to', date("c",$timemax));
//$posts->addAttribute('from', date("c",$timemin));

header ("content-type: text/xml");

echo "";
echo $feed->asXML();


class SimpleXMLExtend extends SimpleXMLElement
{
  public function addCData($nodename,$cdata_text, $arr = array())
  {
    $node = $this->addChild($nodename); //Added a nodename to create inside the function
	foreach($arr as $k=>$v)
		 $node->addAttribute($k,$v);
    $node = dom_import_simplexml($node);
    $no = $node->ownerDocument;
    $node->appendChild($no->createCDATASection($cdata_text));
	return $node;
  }
} 

function atomtag($url,$date){
	$pos = strpos($url,"//")+2;
	$tag = substr($url,$pos);
	$pos = strpos($tag,"/");
	$tag = "tag:".substr($tag,0,$pos).",{$date}:".substr($tag,$pos);
	return $tag;
}


exit();
?>