<?php 

include('../../../functions.php');
include('../functions.php');


	$dir = "html";
	$d = dir("$dir");
	while (false !== ($entry = $d->read())) {
   		if(strtoupper(substr($entry,-4))=="HTML"){
			$index = $entry;
		}elseif(strtoupper(substr($entry,-3))=="PNG"){
			$images[] = $entry;
		}
		
	}
	$d->close();

	$content = file_get_contents("$dir/$index");

	$start = stripos($content, "<pre class=\"codeinput\">",1);
	$end = stripos($content, "</pre>",$start);
	$htmlcode = substr($content,$start, ($end-$start + 6));

	$start = stripos($content, "##### SOURCE BEGIN #####",1);
	$end = stripos($content, "##### SOURCE END #####",$start);
	$mcode = substr($content,($start + 24), ($end-$start - 24));

	$title = substr($index,0,(strlen($index)-5));	
	
	
	$html = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 TRANSITIONAL//EN\">
<html>

	<head>
		<title>$title</title>
		<link rel=\"stylesheet\" type=\"text/css\" href=\"http://chemtools.chem.soton.ac.uk/projects/blog/styles/mlab.css\"/>
	</head>
	<body>
		$htmlcode
	</body>
</html>		

	";

$mcode_id = add_data("m", $mcode);

$html_id = add_data("html", $html);

	$metadata_code ="<metadata>
  <title>$title</title>
<type>m</type>
  <data_html>
    <main>1</main>
    <type>local</type>
    <id>$html_id</id>
  </data_html>
  <data_m>
    <type>local</type>
    <id>$mcode_id</id>
  </data_m>
</metadata>";

$ids[] = add_data("data_meta", $metadata_code);

foreach($images as $image){

$type = strtolower(substr($image,-3));

$image_id = add_data($type, file_get_contents("$dir/$image"));

	$metadata_code ="<metadata>
  <title>$image</title>
  <data_$type>
    <main>1</main>
    <type>local</type>
    <id>$image_id</id>
  </data_$type>
</metadata>";

$ids[] = add_data("data_meta", $metadata_code);

}

$tids = implode(",",$ids);

if(!$_REQUEST['bit_group']){
	$bit_group = "MatLab Publish";
}else{
	$bit_group=$_REQUEST['bit_group'];
}

if(add_blog($_REQUEST['blog_id'], $title, addslashes("[code]{$mcode}[/code]"), "<METADATA>\n<DATA>{$tids}</DATA>\n</METADATA>", $bit_group)) echo "Success!";
?>