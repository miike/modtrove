<?php


include('../../config.php');
include('../../lib/functions.php');
include('../../lib/functions_blog.php');

header("Content-Type: application/xml;charset=utf-8");
//header("Content-Type: text/plain");
$supports = array("Posts");



if(isset($_REQUEST['uri'])){
	$uri = explode("/",$_REQUEST['uri']);
}else{
	header("HTTP/1.1 404 Not Found");
	exit();
}

if(strlen($uri[0])){
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_blogs` WHERE `blog_sname` = '".addslashes($uri[0])."'";
	$result = runQuery($sql,'Blogs');
	if(!$blog = mysql_fetch_array($result)){
		header("HTTP/1.1 404 Not Found");
		exit();
	}
	
	if(!checkzone($blog['blog_zone'],0,$blog['blog_id']) || !checkzone($ct_config['blog_zone'])){
		header("Location: {$ct_config['blog_path']}?msg=Forbidden!&turl=/".urlencode($_REQUEST['uri']));
		exit();
	}
	
}else{	
	header("HTTP/1.1 404 Not Found");
	exit();
}

$nouri = count($uri);

if(!strlen($uri[1])){
		$odata_baseurl = "{$ct_config['blog_url']}odata/{$blog['blog_sname']}/";
	   $source = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<service xml:base="{$odata_baseurl}" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns="http://www.w3.org/2007/app">
	<workspace>
	</workspace>
</service>
XML;

   	$doc = new DOMDocument( '1.0' );
	$doc->loadXML( $source );

	$service = $doc->getElementsByTagName('workspace')->item(0);
	$service->appendChild($doc->createElement("atom:title",$blog['blog_name']));
	foreach($supports as $coll){
		$collection = $doc->createElement("collection");
		$collection->setAttribute("href", $coll);
		$collection->appendChild($doc->createElement("atom:title",$coll));
		$service->appendChild($collection);
	}	
	print $doc->saveXML() . "\n";
	exit();
}

if($uri[$nouri-1]{0}=="$"){
	switch($uri[$nouri-1]){
		case "\$metadata":
			getmetadata();
		break;
	}
}

if(stristr($uri[1],"(")===false){
	$collection = $uri[1];
	$entry = 'all';
}

$odata_baseurl = "{$ct_config['blog_url']}odata/{$blog['blog_sname']}/";

if(in_array($collection, $supports)){
	switch($collection){
		case "Posts": 
			
			switch($entry){
				case "all":
					$where = "WHERE bit_edit = 0";
					$type = "feed";
				break;
			}
			
			$sqlb = "FROM  `{$ct_config['blog_db']}`.`blog_bits` 
			INNER JOIN  `{$ct_config['blog_db']}`.`blog_blogs` ON  `blog_bits`.`bit_blog` =  `blog_blogs`.`blog_id` 
				$where
			ORDER BY  `bit_datestamp` DESC";
			 
			$sql = "SELECT  `bit_id`  ,  `bit_user` ,  `bit_title` ,  `bit_content` , UNIX_TIMESTAMP(`bit_datestamp`) AS datetime, UNIX_TIMESTAMP(`bit_timestamp`) AS timestamp, `blog_blogs`.`blog_zone`, `blog_blogs`.`blog_id`, `bit_meta`, `bit_group`, `bit_cache`
				$sqlb";
		
		
			$source = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
		<{$type} xml:base="{$odata_baseurl}" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns="http://www.w3.org/2005/Atom">
		</{$type}>
XML;
			$doc = new DOMDocument( '1.0' );
			$doc->loadXML( $source );	
		
			
		
		
		break;
	}	
}

if(isset($_REQUEST['$top']))
	$atompagelimit = (int)$_REQUEST['$top'];
else
	$atompagelimit = 10;


if(isset($_REQUEST['$skip']))
	$skip = (int)$_REQUEST['$skip'];
else
	$skip = 0;

$tresult = runQuery("SELECT count(*) as bcount $sqlb",'Fetch Page Count');
$row = mysql_fetch_array($tresult);
$countblog = $row['bcount'];

$sql .= "\n LIMIT $skip,$atompagelimit";

/*
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
*/


switch($type){
	case "feed":
		

		$feed = $doc->getElementsByTagName('feed')->item(0);
		
		$feed->appendChild($doc->createElement("title","$collection"));
		$feed->appendChild($doc->createElement("id","{$odata_baseurl}{$collection}"));
		$feed->appendChild($doc->createElement("updated",date("c")));
		$feed->appendChild($link = $doc->createElement("link"));
				$link->setAttribute("rel", "self");
				$link->setAttribute("title", "{$collection}");
				$link->setAttribute("href", "{$collection}");
		
		$feed->appendChild($doc->createElement("m:count",$countblog));
		
		$tresult = runQuery($sql,'Fetch Page Groups');
		while($row = mysql_fetch_array($tresult)){
			$feed->appendChild($entries = $doc->createElement("entry"));
			create_entities($doc, $entries, $collection, $row );
		}
	break;
}



print $doc->saveXML() . "\n";
exit();


function create_entities(&$doc, &$tag, $collection, &$data ){
	global $odata_baseurl;
	switch($collection){
		case "Posts":
				
				$tag->appendChild($doc->createElement("id", "{$odata_baseurl}{$collection}({$data['bit_id']})"));
				$tag->appendChild($doc->createElement("updated", date("c", $data['datetime'])));
				$tag->appendChild($doc->createElement("title", $data['bit_title']));
				$user = $tag->appendChild($doc->createElement('author'));
					$user->appendChild($doc->createElement('username',$data['bit_user']));
					$user->appendChild($doc->createElement('name',get_user_info($data['bit_user'],"name")));
				$tag->appendChild($cat = $doc->createElement("category"));
					$cat->setAttribute("term", "LabTroveOData.Post");
					$cat->setAttribute("scheme", "http://schemas.microsoft.com/ado/2007/08/dataservices/scheme");
				$tag->appendChild($content = $doc->createElement("content"));
					$content->setAttribute("type", "application/xml");
					$content->appendChild($props = $doc->createElement('m:properties'));
					$props->appendChild($prop = $doc->createElement('d:ID', $data['bit_id']));
						$prop->setAttribute("m:type", "Edm.Int32");
					$props->appendChild($prop = $doc->createElement('d:Title', $data['bit_title']));
						$prop->setAttribute("m:type", "Edm.String");
						
					
					$props->appendChild($prop = @$doc->createElement('d:Section', $data['bit_group']));
							$prop->setAttribute("m:type", "Edm.String");
						
					/*	$blogcontent = $data['bit_cache'];
					$props->appendChild($prop = $doc->createElement('d:Text'));
						$prop->setAttribute("m:type", "Edm.String");
						$prop->appendChild($doc->createCDATASection(strip_tags($blogcontent)));
					$props->appendChild($prop = $doc->createElement('d:HTML'));
						$prop->setAttribute("m:type", "Edm.String");
						$prop->appendChild($doc->createCDATASection($blogcontent));
					*/	
					$props->appendChild($prop = $doc->createElement('d:Created', date("c",$data['datetime'])));
						$prop->setAttribute("m:type", "Edm.DateTime");
					$props->appendChild($prop = $doc->createElement('d:Updated', date("c",$data['timestamp'])));
						$prop->setAttribute("m:type", "Edm.DateTime");
					
					$props->appendChild($prop = $doc->createElement('d:Author'));
						$prop->setAttribute("m:type", "LabTroveOData.Author");
							$prop->appendChild($doc->createElement('d:Username',$data['bit_user']));
							$prop->appendChild($doc->createElement('d:Name',get_user_info($data['bit_user'],"name")));
						
						
		break;
	}
}


function getmetadata(){
	echo <<<END
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<edmx:Edmx Version="1.0" xmlns:edmx="http://schemas.microsoft.com/ado/2007/06/edmx">
  <edmx:DataServices xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" m:DataServiceVersion="2.0">
    <Schema Namespace="LabTroveOData" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns="http://schemas.microsoft.com/ado/2007/05/edm">
      <EntityType Name="Post">
        <Key>
          <PropertyRef Name="ID" />
        </Key>
        <Property Name="ID" Type="Edm.Int32" Nullable="false" />
        <Property Name="Title" Type="Edm.String" Nullable="true" m:FC_TargetPath="SyndicationTitle" m:FC_ContentKind="text" m:FC_KeepInContent="false" />
      	<Property Name="Text" Type="Edm.String" Nullable="true" m:FC_TargetPath="SyndicationSummary" m:FC_ContentKind="text" m:FC_KeepInContent="false" />
		<Property Name="HTML" Type="Edm.String" Nullable="true"/>
		<Property Name="Section" Type="Edm.String" Nullable="true"/>
		<Property Name="Created" Type="Edm.DateTime" Nullable="true"/>
		<Property Name="Updated" Type="Edm.DateTime" Nullable="true"/>
		<Property Name="Author" Type="Edm.DateTime" Nullable="true"/>
	</EntityType>
	<ComplexType Name="Author">
		<Property Name="Name" Type="Edm.String" Nullable="true"/>
		<Property Name="UserName" Type="Edm.String" Nullable="false"/>
	</ComplexType>
	  <EntityContainer Name="DemoService" m:IsDefaultEntityContainer="true">
		<EntitySet Name="Posts" EntityType="LabTroveOData.Post"/>
	  </EntityContainer>
    </Schema>
  </edmx:DataServices>
</edmx:Edmx>
END;
exit();
}



?>