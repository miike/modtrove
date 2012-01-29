<?php

$tresult = runQuery($sql,'Fetch Page Groups');

while($post = mysql_fetch_array($tresult)){
	
$urllink = render_blog_link($post['bit_id'],ture );

if(!$post['bit_cache']) $post['bit_cache'] = makepostcache($post);
$item = array(
	'label' => $post['bit_title'],
	'author' => get_user_info($post['bit_user'],"name"),
	'authoruri' => render_link('',array('user' => $post['bit_user'])),
	'section' => $post['bit_group'],
	'uri' => $urllink,
	'month' => date("F Y",$post['datetime']),
	'date' => date("c",$post['datetime']),
	'html' => $post['bit_cache']

);

if($post['bit_meta']){
	$metaxml = readxml($post['bit_meta']);
	if(is_array($metaxml['METADATA']['META'])){
		foreach($metaxml['METADATA']['META'] as $met=>$key)
			$item[strtolower($met)] = $key;
	}
}

$data['items'][] = $item;

}

$data['types']['Post'] = array("pluralLabel"=>"Posts","uri" => render_link($blog['blog_sname']));

header ("content-type: text/plain");

echo json_encode($data); 

exit();
/*
$timemin = time();
$timemax = 0;

$posts = new SimpleXMLExtend("<?xml version=\"1.0\" encoding=\"UTF-8\"?"."><posts/>");
//$posts->addChild('sql', $sql);

$tresult = runQuery($sql,'Fetch Page Groups');
$noofposts = mysql_num_rows($tresult);
while($post = mysql_fetch_array($tresult)){

$xpost = $posts->addChild('post');

$xpost->addChild('id', $post['bit_id']);
$xpost->addChild('rid', $post['bit_rid']);
$xpost->addChild('title', $post['bit_title']);
$xpost->addChild('section', $post['bit_group']);
$user = $xpost->addChild('author');
$user->addChild('username',$post['bit_user']);
$user->addChild('name',get_user_info($post['bit_user'],"name"));
$xpost->addCData('content', $post['bit_content']);
$xpost->addCData('html', bbcode($post['bit_content']));
$xpost->addChild('datestamp', date("c",$post['datetime']));
$xpost->addChild('timestamp', date("c",$post['timestamp']));
$xpost->addChild('blog', $post['bit_blog']);
$xpost->addChild('key', $post['bit_md5']);

$timemin = min($timemin, $post['datetime']);
$timemax = max($timemax, $post['datetime']);

if($post['bit_meta']){
	$metaxml = readxml($post['bit_meta']);
	if(isset($metaxml['METADATA']['META'])){
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
$fmat->addAttribute('type', "html"); 
$fmat = $formats->addChild('format', substr($urllink,0,-4)."xml");
$fmat->addAttribute('type', "xml");
$fmat = $formats->addChild('format', substr($urllink,0,-4)."png");
$fmat->addAttribute('type', "png");
}


$posts->addAttribute('to', date("c",$timemax));
$posts->addAttribute('from', date("c",$timemin));



echo "";
echo $posts->asXML();


class SimpleXMLExtend extends SimpleXMLElement
{
  public function addCData($nodename,$cdata_text)
  {
    $node = $this->addChild($nodename); //Added a nodename to create inside the function
    $node = dom_import_simplexml($node);
    $no = $node->ownerDocument;
    $node->appendChild($no->createCDATASection($cdata_text));
  }
} 


exit();

*/
?>