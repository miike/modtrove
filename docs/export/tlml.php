<?php

$tlcolors = array("red","blue","green","gray","deeppink","orange","purple","cyan","goldenrod","light-gray","black");

$cache = readxml($blog['blog_infocache']);
$i=0;
foreach($cache['METADATA']['SECTIONS'] as $val){
	$sections[$val['NAME']] = $tlcolors[$i];
	$i = ($i+1) % count($tlcolors);
}


$timemin = time();
$timemax = 0;

$posts = new SimpleXMLExtend("<?xml version=\"1.0\" encoding=\"UTF-8\"?"."><data/>");
//$posts->addChild('sql', $sql);

$tresult = runQuery($sql,'Fetch Page Groups');
$noofposts = mysql_num_rows($tresult);
while($post = mysql_fetch_array($tresult)){

$urllink = render_blog_link($post['bit_id'],ture );
$xpost = @$posts->addChild('event',"to view post <a href=\"{$urllink}\">". $post['bit_title']."</a>");

$xpost-> addAttribute('title', $post['bit_title']);
$xpost-> addAttribute('start', date("r",$post['datetime']));
$xpost-> addAttribute('link', $urllink );
$xpost-> addAttribute('color',  $sections[$post['bit_group']]);
$xpost-> addAttribute('icon',  "{$ct_config['blog_path']}inc/timeline/imgs/{$sections[$post['bit_group']]}-circle.png");

$timemin = min($timemin, $post['datetime']);
$timemax = max($timemax, $post['datetime']);

}


$posts->addAttribute('to', date("r",$timemax));
$posts->addAttribute('from', date("r",$timemin));

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