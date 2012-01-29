<?php

function render_link($page,$varibles = NULL){
global $ct_config;

$url = $page;
unset($varibles['blog_sname']);
unset($varibles['blog_id']);
if(isset($varibles['bit_id'])){
	$url .= "/{$varibles['bit_id']}";
	unset($varibles['bit_id']);
}

if(is_array($varibles))
foreach($varibles as $key => $value){
if(strlen($key)!=0 && strlen($value)!=0)
	$url .= "/$key/$value";
}
if($url{0} == "/") $url = substr($url,1);

$url = $ct_config['blog_url'].str_replace("//","/",$url);
return $url;
}



function bbcode($message) {

global $ct_config;



	$message = @preg_replace(array_keys($ct_config['bbcode_preg']), array_values($ct_config['bbcode_preg']), $message);



// make clickable() :
/**
 * Rewritten by Nathan Codding - Feb 6, 2001.
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 * 	to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 * 	to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *		to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 */

	// pad it with a space so we can match things at the start of the 1st line.
	$ret = ' ' . $message;

	// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
	// xxxx can only be alpha characters.
	// yyyy is anything up to the first space, newline, comma, double quote or <
	$ret = preg_replace("#([\t\r\n ])([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="\2://\3">\2://\3</a>', $ret);

	// matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
	// zzzz is optional.. will contain everything up to the first space, newline,
	// comma, double quote or <.
	$ret = preg_replace("#([\t\r\n ])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="http://\2.\3">\2.\3</a>', $ret);

	// matches an email@domain type address at the start of a line, or after a space.
	// Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
	$ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);

	// Remove our padding..
	$ret = substr($ret, 1);


	return (str_replace("\n", "<br style=\"clear:left;\"/>",str_replace("\r", "",$ret)));
}




function render_blog_link($id, $url_only = false, $suffix = ".html")
{
	global $ct_config;

	$rowb = db_get_blog_link_info($id);
	$name = ereg_replace( "[^A-Za-z0-9\ \_]", "", strip_tags($rowb['bit_title']));
	$name = str_replace(" ", "_", $name);
	$url = render_link($rowb['blog_sname'], array('bit_id' => $id))."/{$name}{$suffix}";

	if($url_only)
	{
		return $url;
	}
	else
	{
		return "<a href=\"{$url}\">".$rowb['bit_title']."</a>";
	}
}


function getdata($bit, $text=0,$size=null){
global $ct_config;

$bit = (int)$bit;
if(!$bit) return "ERROR";
$width = 100; $height =75;
if($text==2){
list($width,$height) = split("x",$size);
}
$sql = "SELECT data_type FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $bit";
$tresult = runQuery($sql,'Fetch Page Groups');
$row = mysql_fetch_array($tresult);



$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $bit";
	$result = runQuery($sql,'Fetch Page Groups');
	$srow = mysql_fetch_array($result);
	
	$metadata = NULL;
	$metadata = readxml($srow['data_data']);
	if(is_array($metadata['METADATA']))
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
if($text==3 || $text==1){
	if($text == 1) $metadata['METADATA']['TITLE'] = "Data: ".$metadata['METADATA']['TITLE'];

   return "<a href=\"javascript:var blob = window.open('{$ct_config['blog_path']}data/{$bit}.html','_blank','scrollbars=1;menubar=no,height=750,width=680,resizable=yes,toolbar=no,location=no,status=no')\">".$metadata['METADATA']['TITLE']."</a>";

}


switch ($row[0]) {
case 'blog_link':
	global $data_type, $blog;
	$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $bit";
	$result = runQuery($sql,'Fetch Page Groups');
	$srow = mysql_fetch_array($result);

	$metad = readxml($srow[0]);
	$link = (int)($metad['METADATA']['ID']);
	$data_type = array("type" => "blog", "id" => (int)($metad['METADATA']['ID']));
	
   return "<div style=\"display:inline;\"><table width=$width height=$height class=\"dataPic\"><tr><td>".render_blog_link($link)."</td></tr></table></div>";
   break;

case 'jpg':
   return "<a href=\"{$ct_config['blog_path']}getdata.php?bit=$bit\" target=\"_blank\"><img src=\"{$ct_config['blog_path']}getdata.php?bit=$bit\" width=$width height=$height class=\"dataPic\"></a>";
   break;
case 'png':
   return "ssss<a href=\"{$ct_config['blog_path']}getdata.php?bit=$bit\" target=\"_blank\"><img src=\"{$ct_config['blog_path']}getdata.php?bit=$bit\" width=$width height=$height class=\"dataPic\"></a>";
   break;
case 'gif':
   return "<a href=\"{$ct_config['blog_path']}getdata.php?bit=$bit\" target=\"_blank\"><img src=\"{$ct_config['blog_path']}getdata.php?bit=$bit\" width=$width height=$height class=\"dataPic\"></a>";
   break;

case 'img_url';
	$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $bit";
	$result = runQuery($sql,'Fetch Page Groups');
	$srow = mysql_fetch_array($result);

   return "<img src=\"".$srow[0]."\" width=$width height=$height class=\"dataPic\">";
   break;
case 'data_meta';

$distype = NULL;

if( $width>150 && $height>=100){
	$distype = "preview";
}
if($distype=="preview"){
	$img = drawdatabox($metadata, $distype, false, $width,$height );
}
$onclick = "javascript:var blob = window.open('{$ct_config['blog_path']}data/{$bit}.html','_blank','scrollbars=1;menubar=no,height=750,width=680,resizable=yes,toolbar=no,location=no,status=no');";

if($distype=="preview"){
		$ret = "<div class=\"dataPic datathumb\" style=\"width:{$width}px; height:auto;\"><div style=\"background-repeat: no-repeat;  width:{$width}px; \">{$img}</div><a href=\"#\" onclick=\"{$onclick};return false;\">{$metadata['METADATA']['TITLE']}</div>";
		return $ret;
}else{
  	$ret = "<div class=\"dataPic datathumb\"  onclick=\"$onclick\" style=\"width:{$width}px; height:auto;\"><div style=\"background-repeat: no-repeat; background-position: center center; background-image: url({$ct_config['blog_path']}getdata.php?bit=$bit&width=$width&height=$height&thumb=1); width:{$width}px; height:{$height}px; \"></div>{$metadata['METADATA']['TITLE']}</div>";
		return $ret;
}
break;
case 'img_meta';
	$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $bit";
	$result = runQuery($sql,'Fetch Page Groups');
	$srow = mysql_fetch_array($result);
	
	$metadata = NULL;
	$metadata = readxml($srow['data_data']);

   return "<a href=\"javascript:var blob = window.open('{$ct_config['blog_path']}data/{$bit}.html','_blank','scrollbars=1;menubar=no,height=750,width=680,resizable=yes,toolbar=no,location=no,status=no')\"><img src=\"".$metadata['METADATA']['THUMB_SRC']."\" class=\"dataPic\"></a>";
   break;
case 'asc':
   return "<embed src=\"{$ct_config['blog_path']}getdata.php?bit=$bit&width=$width&height=$height\" width=$width height=$height  type=\"image/svg+xml\" class=\"dataPic\">";
   break;

case 'eprint';
	$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $bit";
	$result = runQuery($sql,'Fetch Page Groups');
	$srow = mysql_fetch_array($result);

   return "<a href=\"".$srow[0]."\" target=_new><img src=\"inc/eprint.jpg\" width=$width height=$height
	class=\"dataPic\"></a>";
   break;



default:
	return "";
}



}

function checkOverlay($bit){

	global $ct_config;
	global $body;

	$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $bit";
	$tresult = runQuery($sql,'Fetch Page Groups');
	$row = mysql_fetch_array($tresult);

	$metadata = readxml($row['data_data']);

	if ( isset($metadata['METADATA']['OVERLAY']) ) { return true; }

}

function writexml($meta){

	$xml = "";
		
	foreach($meta as $key => $value ){
		
		$xml .= "<$key>";
		if(is_array($value)){
			$xml .=	writexml($value);
		}else{
			$xml .= htmlspecialchars($value);
		}

		$xml .=	"</$key>\n";

	}

	return $xml;

}


function file_img($in_file) {
global $ct_config;

				switch(strtolower($in_file)) {
					case "wmv";
						$filetype = "dvd";
						break;
					case "png":
					case "bmp":
					case "jpg":
					case "jpeg":
					case "gif":
					case "tiff":
					case "tif":
					case "psd":
						$filetype = "picture";
						break;
					case "eps":	
					case "ps":
					case "svg":
						$filetype = "vector";
						break;
					case "php":
					case "php3":
						$filetype = "php";
						break;
					case "zip":	
					case "tar":
					case "gz":
					case "sit":
						$filetype = "compressed";
						break;
					case "html":
					case "htm":
					case "url":
						$filetype = "world";
						break;
					case "ini":
					case "htaccess":
					case "htpasswd":
					case "db":
						$filetype = "gear";
						break;
					case "txt":
					case "text":
					case "asc":
						$filetype = "text";
						break;
					case "js":
						$filetype = "script";
						break;
					case "c";
					case "m":
						$filetype = "code";
						break;
					case "xls":
					case "xlsx":
						$filetype = "excel";
					break;
					case "doc":
					case "docx":
						$filetype = "word";
					break;
					case "ppt":
					case "pptx":
						$filetype = "powerpoint";
					break;
					case "bash":
					case "sh":
						$filetype = "tux";
					break;
					case "avi":
					case "mpg":
					case "mov":
					case "dv":
					case "flv":
					case "m4v":
					case "ogg":
					case "wmv":
						$filetype = "dvd";
					break;
					
					case "pdf":
					case "pdfa":
					case "pdfx":
						$filetype = "acrobat";
					break;
					
					default:
						$filetype = false;
				}
			if($filetype) $filetype = "_".$filetype;
return "{$ct_config['blog_path']}inc/icons/page_white$filetype.png";
}



function geteditinfo($id){
global $ct_config;
$sql = "SELECT `bit_edit`, `bit_id`, `bit_edit`, `bit_edituser`, `bit_editwhy` FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE `bit_edit` = $id";
	$result = runQuery($sql,'Fetch Page Groups');
	$srow = mysql_fetch_array($result);

	return $srow;

}


function getdata_overlay($bit){
$test = checkOverlay($bit);
		if ( $test ) { $body .= "<span class=comment>"; }
		$body .= getdata($bit);
		if ( $test ) { $body .= "</span>"; }
		return $body;
}


function makesidecache(){
global $blog, $user_can_post, $blog_id, $ct_config,$request;

$xml = "<METADATA>\n";

$xml .= "\t<ARCHIVES>\n";

$sql = "SELECT count(  `bit_id` ) AS count, month(  `bit_datestamp` ) AS month , year(  `bit_datestamp` ) AS year FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE `bit_blog` = ".$blog['blog_id']."  AND bit_edit = 0 GROUP BY MONTH , year ORDER BY year DESC, MONTH DESC";
$tresult = runQuery($sql,'Fetch Page Groups');
    while($row = mysql_fetch_array($tresult)){
                $mtime = mktime(0,0,0,$row['month'],1,$row['year']);
				$xml .= "\t\t<t{$mtime}>{$row['count']}</t$mtime>\n";
        }
$xml .= "\t</ARCHIVES>\n";

//Sections

$xml .= "\t<SECTIONS>\n";

$sql = "SELECT count(  `bit_id` ) AS count,`bit_group` FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE `bit_blog` = ".$blog['blog_id']." AND bit_edit = 0 GROUP BY bit_group ";
	$ii =0;
$tresult = runQuery($sql,'Fetch Page Groups');
    while($row = mysql_fetch_array($tresult)){
				$row['bit_group'] = htmlentities($row['bit_group']);
				$xml .= "\t\t<i$ii><name>{$row['bit_group']}</name><count>{$row['count']}</count></i$ii>\n";
    	$ii++;    
	}
$xml .= "\t</SECTIONS>\n";
$xml .= "\t<META>\n";
$xml .= meta_metac($blog_id);
$xml .= "\t</META>\n";

$xml .= "\t<USERS>\n";

$sql = "SELECT count(  `bit_id` ) AS count,`bit_user` FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE `bit_blog` = ".$blog['blog_id']." AND bit_edit = 0 GROUP BY bit_user ";
	$ii =0;
$tresult = runQuery($sql,'Fetch Page Users');
    while($row = mysql_fetch_array($tresult)){
				$xml .= "\t\t<u{$ii}><user>{$row['bit_user']}</user><name>".htmlentities(get_user_info($row['bit_user'],'name'))."</name><count>{$row['count']}</count></u{$ii}>\n";
    	$ii++;    
	}
$xml .= "\t</USERS>\n";

$xml .= "</METADATA>\n";
$blog['blog_infocache'] = $xml;
return $xml;
}

function updatesidecache(){
global $blog,$ct_config;
	$xml = makesidecache();

	$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_blogs` SET  `blog_infocache` =  '".addslashes($xml)."' WHERE  `blog_blogs`.`blog_id` = {$blog['blog_id']} LIMIT 1 ";
	runQuery($sql,'Update Blog side bar Cache');

}


function makesidebar(){
global $blog, $user_can_post, $blog_id, $ct_config,$request;

if(!$blog['blog_infocache']) updatesidecache();
$cache = readxml($blog['blog_infocache']);

$_body = "\t<div class=\"info\">\n";

foreach($ct_config['blog_infobar'] as $key){
switch($key){
case "search":
$_body .= "
<form action=\"".render_link('',array('search' => $blog['blog_sname'] ))."\" method=\"post\">
<input class=\"searchBox\" type=\"text\" value=\"Search\" name=\"q\" size=\"15\"/>
</form>";
global $jquery;
	if(!isset($jquery['function'])){ $jquery['function']= ''; }
	$jquery['function'] .= "
		$('.searchBox').focus(function() {
			var q = $(this).val();
			if(q=='Search')	$(this).val('');
    });
		$('.searchBox').blur(function() {
				var q = $(this).val();
				if(q=='')	$(this).val('Search');
	      });
";

break;
case "thispost";
if( isset($request['bit_id']) && $request['bit_id'] ){
	
    $link = render_blog_link($request['bit_id'],true);
	$_body .= "<div class=\"infoSection\">This Post</div>";
	$_body .= "<a href=\"".$link."\">Permalink</a><br/>";
	$_body .= "<a href=\"".get_uri_url($request['bit_id'])."\">URI</a><br/>";
    $_body .= get_uri_labelpage($request['bit_id'])."<br/>";
	$_body .= "<a href=\"".$link."?revisions\">Revisions</a><br/>";
	$_body .= "Export:<br/>";
	$_body .= "<a href=\"".substr($link,0,-4)."xml\">XML</a> (<a href=\"".substr($link,0,-4)."xml?inline=yes\">With Files</a>)<br/>";
	$_body .= "<a href=\"".substr($link,0,-4)."png\">PNG Image</a><br/>";	
}
break;
case "thisblog";

if($_SESSION['user_name'] && ($_SESSION['user_admin'] > 1 || $user_can_post)){
	$_body .= "<div class=\"infoSection\">This Blog</div>";
	$_body .= "\t\t<a href=\"".render_link($blog['blog_sname'])."?add_blog=1\">New Post</a> <br/>\n";
	if($_SESSION['user_name']==$blog['blog_user'] || $_SESSION['user_admin'] > 1)
	$_body .= "\t\t<a href=\"".render_link("settings.php")."?blog={$blog['blog_sname']}\">Blog Settings</a> <br/>\n";
	$_body .= "\t\t<a href=\"".render_link($blog['blog_sname'])."/timeline.html\">Timeline View</a> <br/>\n";
	$_body .= "\t\t<a href=\"".render_link($blog['blog_sname'])."/exhibit.html\">Exhibit View</a> <br/>\n";
	$_body .= "\t\t<a href=\"".render_link("export.php?blog=".$blog['blog_sname'])."\">Export Blog</a><br/>\n";
	
	
}
break;
case "archives";
//Archives
$_body .= "\t<div class=\"infoSection\">Archives</div>\n";

if(is_array($cache['METADATA']['ARCHIVES']))
foreach($cache['METADATA']['ARCHIVES'] as $key=>$val){
                $mtime = substr($key,1);
		if(!isset($request['month']) || (isset($request['month']) && ($mtime != $request['month'])))
                $_body .= "\t\t<a href=\"".render_link($blog['blog_sname'],array('blog_id' => $blog['blog_id'], 'month'=> $mtime ))."\">".date('F Y',$mtime)."</a> <span class=\"num_posts\">(".$val.")</span><br/>\n";
        	else
		$_body .= date('F Y',$mtime)." <span class=\"num_posts\">(".$val.")</span><br/>\n";
	}

//Sections
break;
case "authors";
//Archives
$_body .= "\t<div class=\"infoSection\">Authors</div>\n";

if(is_array($cache['METADATA']['USERS']))
foreach($cache['METADATA']['USERS'] as $key=> $val){
       	if(isset($request['byuser']) && ($val['NAME']==$request['byuser']))
		$_body .= "\t\t".$val['NAME']." <span class=\"num_posts\">(".$val['COUNT'].")</span><br/>\n";
                else
		$_body.= "\t\t<a href=\"".render_link($request['blog_sname'],array('byuser'=>$val['USER']))."\">".$val['NAME']."</a> <span class=\"num_posts\">(".$val['COUNT'].")</span><br/>\n";
        }

//Sections
break;

case "sections";

global $bloggroups;
$_body .= "\t<div class=\"infoSection\">Sections</div>\n";
if(is_array($cache['METADATA']['SECTIONS']))
foreach($cache['METADATA']['SECTIONS'] as $val){
                $bloggroups[] = $val['NAME'];
                if(isset($request['group']) && ($val['NAME']==$request['group']))
		$_body .= "\t\t".$val['NAME']." <span class=\"num_posts\">(".$val['COUNT'].")</span><br/>\n";
                else
		$_body.= "\t\t<a href=\"".render_link($request['blog_sname'],array('group'=>$val['NAME']))."\">".$val['NAME']."</a> <span class=\"num_posts\">(".$val['COUNT'].")</span><br/>\n";
        }

break;
case "meta";
if(is_array($cache['METADATA']['META']))
foreach($cache['METADATA']['META'] as $key=>$val){
	foreach($val as $vv)
		$construct[$key][$vv['NAME']] = $vv['COUNT'];
}
$_body .= meta_meta($blog_id, NULL, isset($construct)?$construct:NULL );
break;
case "tools";

$_body .= "<div class=\"infoSection\">Tools</div>";
if(isset($request['bit_id']) && $request['bit_id']){
$_body .= "\t\t<a href=\"".render_link($blog['blog_sname'],$request)."?flip_qr=1\">Show/Hide QR Code</a><br/>\n";
}
$_body .= "\t\t<a href=\"".render_link($blog['blog_sname'],$request)."?flip_keys=1\">Show/Hide Keys</a>\n";

if( (isset($_COOKIE['showqr']) && $_COOKIE['showqr']) && (isset($request['bit_id']) && $request['bit_id']) ){

        $_body .= "<div class=\"infoSection\">QR Code</div>";

$_body .= get_uri_qrcode($request['bit_id']);

}

}

}
$_body .= "</div>";
//$body .= "</div>";
return $_body;
}

function clear_blog_cache($id){
global $ct_config;
$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_bits` SET `bit_cache` =  '' WHERE  `blog_bits`.`bit_id` = {$id} AND `bit_edit` = 0 LIMIT 1 ;";
runQuery($sql,'Clear Cache');
}

include('functions/metameta.php');
include('functions/livecopy.php');
include('functions/blog.php');
include('functions/zone.php');
include('functions/addmetadata.php');
include('functions/data.php');
include('functions/geshi.php');
?>
