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


$url = render_link($blog['blog_sname']);

$json['url'] = render_link($blog['blog_sname'],$request);
$json['title'] = $blog['blog_name'];
$json['description'] = $blog['blog_desc'];
$json['items'] = array();


while($post = mysql_fetch_array($tresult)){
	$item = array();
	
	
	$item['id'] = $post['bit_id'];
	$item['title'] = $post['bit_title'];
	$item['author']['username'] = $post['bit_user'];
	$item['author']['name'] = get_user_info($post['bit_user'],"name");
	$item['author']['uri'] = $ct_config['blog_url']."user/{$post['bit_user']}";
	
	$item['published'] = date(DATE_RFC3339, $post['datetime']);
	$item['updated'] = date(DATE_RFC3339, $post['updated']);
	
	$eurl = render_blog_link($post['bit_id'],true);
	$item['url'] = $eurl; 
	
	if(!isset($_REQUEST['skipcontent']) || !$_REQUEST['skipcontent']){	
	
		if(!$post['bit_cache'] || $_REQUEST['nocache']){
			$post['content'] = makepostcache($post);
		}else{
			$post['content'] =  $post['bit_cache'];
		}
		$item['bbcode'] = $post['bit_content'];
		$item['content'] = $post['content'];	
	}
 	$item['url_json'] = substr($eurl,0,-4)."json";
	
	$json['items'][] = $item;
	
	
}

/*
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


*/

 echo json_encode($json);


exit();
?>