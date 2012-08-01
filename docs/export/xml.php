<?php



$timemin = time();
$timemax = 0;

$posts = new SimpleXMLExtend("<?xml version=\"1.0\" encoding=\"UTF-8\"?"."><posts/>");
//$posts->addChild('sql', $sql);

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

$urllink = render_blog_link($post['bit_id'], true );

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

$revisions = $xpost->addChild('revisions');
$sql = "SELECT * FROM  `blog_bits` 	WHERE  `bit_id` = '{$post['bit_id']}'";
$result = runQuery($sql,'Fetch Revisions');
$xmllink =  substr($urllink,0,-4)."xml";
while($rev = mysql_fetch_array($result)){
	$revi = $revisions->addChild("revision", $xmllink."?revision={$rev['bit_rid']}");
	if($rev['bit_rid'] == $post['bit_rid'])
		$revi->addAttribute('current','true');
}

if($post['bit_edit']){
	$edited = $xpost->addChild('edited');
	$edited->addChild('reason', $post['bit_editwhy']);
	$edited->addChild('user', $post['bit_edituser']);
	$edited->addChild('post',  $xmllink."?revision={$post['bit_edit']}");
}


$comments = $xpost->addChild('comments');

	$sql = "SELECT *,UNIX_TIMESTAMP(  `com_datetime` ) AS datetime FROM  `{$ct_config['blog_db']}`.`blog_com` 
				WHERE  `com_bit` = '".(int)$request['bit_id']."'   AND `com_edit` =0 ORDER BY `com_datetime` ASC";
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


$posts->addAttribute('to', date("c",$timemax));
$posts->addAttribute('from', date("c",$timemin));

header ("content-type: text/xml");

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
?>