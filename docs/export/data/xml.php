<?php

$dataset = new SimpleXMLExtend("<?xml version=\"1.0\" encoding=\"UTF-8\"?"."><dataset/>");

if(!$metadata['METADATA']['TITLE']){
		foreach($metadata['METADATA'] as $key=>$val){
			if(substr($key,0,5)=="DATA_"){
				if(!$metadata['METADATA']['TITLE']){
					$metadata['METADATA']['TITLE'] = $val['NAME'];
				}
				if($val['MAIN']){
					$metadata['METADATA']['TITLE'] = $val['NAME'];
				}
			}
		}
}
$dataset->addChild('id', $row['data_id']);
$dataset->addChild('uploaded', date("c",strtotime($row['data_datetime'])));
$dataset->addChild('title', $metadata['METADATA']['TITLE']);
if($row['data_post'])
	$dataset->addChild('post', substr(render_blog_link($row['data_post'],true),0,-4)."xml");

$dataitems= $dataset->addChild('dataitems');

foreach($metadata['METADATA'] as $key=>$value){
			if(substr($key,0,5)=="DATA_"){
				if( strtolower($value['TYPE'])=='local'){
			
					if(!strlen($value['NAME']))
						$url = "http://".$ct_config['this_server'].$ct_config['blog_path']."data/files/".$value['ID'].".".strtolower(substr($key,5));
	       			else 
						$url = "http://".$ct_config['this_server'].$ct_config['blog_path']."data/files/".$value['ID']."/".$value['NAME'];
	       			}else{
						 $url = $value['URL']; 
					}

				$dataitem = $dataitems->addChild('dataitem',$url);
				if($value['MAIN'])
				$dataitem->addAttribute('main', 'true');
				if($value['NAME'])
				$dataitem->addAttribute('filename', $value['NAME']);
				else
				$dataitem->addAttribute('filename', $value['ID'].".".strtolower(substr($key,5)));
				
				$dataitem->addAttribute('type', strtolower(substr($key,5)));
			}
}



header ("content-type: text/xml");

echo "";
echo $dataset->asXML();


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