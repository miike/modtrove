<?php



chdir(dirname(__FILE__));

include("../default_config.php");


$blog_id = (int)$argv[1];
$indexpost = (int)$argv[2];
$depth = (int)$argv[3];
$hashkey = $argv[4];


if(!$blog_id || !$indexpost || !$depth || !$hashkey){
	echo "Error";
	exit(1);
}

$datapath = "{$ct_config['pwd']}/docs/cache/blogdumps/{$hashkey}";
$blog = db_get_blog_by_id($blog_id);

file_put_contents("$datapath/created",time());

$datapath_export = $datapath."/{$blog['blog_sname']}";
 @mkdir($datapath_export);

if($indexpost!=-1){
	$posts = array($indexpost);
}else{
	$sql = "SELECT `bit_id`
	FROM  `{$ct_config['blog_db']}`.`blog_bits` 
	WHERE  `bit_blog` = {$blog_id}
	AND  `bit_edit` =0";
	$result = runQuery($sql,'Blogs');
	$posts[] = -1;
	while($post = mysql_fetch_array($result))
		$posts[] = $post['bit_id'];
}

@mkdir("{$datapath_export}/style");
`cp ../../docs/style/style.css $datapath_export/style/.`;

@mkdir("{$datapath_export}/inc");
@mkdir("{$datapath_export}/inc/icons");
foreach(array("link.png","attach.png") as $img)
	`cp ../../docs/inc/icons/$img $datapath_export/inc/icons/.`;


	$blog_id = $blog['blog_id'];
	$title = $blog['blog_name'];
	$desc = $blog['blog_desc'];
	$request['blog_sname'] = $blog['blog_sname'];
	$title_url = "index.html";


if(file_exists("../../config/blog_{$blog_id}.php"))
	include("../../config/blog_{$blog_id}.php");
include("../../docs/style/{$ct_config['blog_style']}/blogstyle.php");
`rsync -a --exclude=*.php --exclude=.svn ../../docs/style/default $datapath_export/style/.`; //`rm -rf $datapath_export/style/.svn`;
if($ct_config['blog_style']!="default")
`rsync -a --exclude=*.php --exclude=.svn ../../docs/style/{$ct_config['blog_style']} $datapath_export/style/.`; //`rm -rf $datapath_export/style/.svn`;
$postsscaned = array();


for($i=0;$i<$depth;$i++){
	$posts2 = $posts;
	foreach($posts2 as $postid){
		if(!in_array($postid,$postsscaned)){
			$sql = "SELECT  * FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_id` = {$postid} AND  `bit_edit` = 0";
			$tresult = runQuery($sql,'Fetch Page Count');
			if($post = mysql_fetch_array($tresult)){
				$subposts = array();
					preg_replace('/\[blog(?::\w+)?\](\d+)\[\/blog(?::\w+)?\]/sie', "\$subposts[] = '\\1';", $post['bit_content'] );
				foreach($subposts as $id){
					if(!in_array($id,$posts)){
							$sql = "SELECT  bit_id FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_id` = {$postid} AND  `bit_edit` = 0 AND `bit_blog` = $blog_id";
							$result = runQuery($sql,'Fetch Page Count');
							if(mysql_num_rows($result))
								$posts[] = $id;
					}
				}
			}
		}
		$postsscaned[] =$postid; 
	}
}

$totolp = count($posts); 
$postnumb = 0;
foreach($posts as $postid){

	$postnumb++;

	$resp = "Proc: $postid ($postnumb/$totolp)";
	$resp .= "<br><small>Please don't close this window until export is complete</small>";
	file_put_contents("$datapath/status",$resp);

	if( $postid==-1){
		$post = array();
		$post['bit_id'] = -1;
		$post['bit_user'] = $blog['blog_user'];
		$post['bit_title'] = "Index of {$blog['blog_name']}" ;
		$post['bit_blog'] = $blog['blog_id'];
		$post['bit_group'] = "Index";
		$post['datetime'] = time();
		$post['timestamp'] = time();
		
		
		$sql = "SELECT `bit_id`, UNIX_TIMESTAMP(  `bit_datestamp` ) AS datetime
		FROM  `{$ct_config['blog_db']}`.`blog_bits` 
		WHERE  `bit_blog` = {$blog_id}
		AND  `bit_edit` =0
		ORDER BY  `blog_bits`.`bit_datestamp` ASC ";
		$result = runQuery($sql,'Blogs');
		$posts[] = -1;
			while($xpost = mysql_fetch_array($result)){ 
				$month = date("F Y", $xpost['datetime']);
				if($lastmo != $month){
					$post['bit_content'] .= "\n[b]{$month}[/b]\n";
				}
				$post['bit_content'] .= "[blog]{$xpost['bit_id']}[/blog]\n";
				$lastmo = $month;
			}
			
	}else{
		$sql = "SELECT  `bit_id` ,  `bit_user` ,  `bit_title` ,  `bit_content` ,  `bit_meta` ,  `bit_datestamp` ,  `bit_timestamp` ,  `bit_group` ,  `bit_blog` ,  `bit_edit` , `bit_edituser`, `bit_editwhy` , UNIX_TIMESTAMP(  `bit_datestamp` ) AS datetime,  UNIX_TIMESTAMP(  `bit_timestamp` ) AS timestamp, `bit_md5` , `bit_cache`, `bit_rid`, `bit_edituser` FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_id` = {$postid} AND  `bit_edit` = 0";
		$tresult = runQuery($sql,'Fetch Page Count');
		if(($post = mysql_fetch_array($tresult))===false){
			continue;
		}
	}

		/*Data Search*/
		$dataparse = array('/\[data(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie'=>"'<div style=\"float:left;\">'.exgetdata(\\1).'</div>'",
			'/\[data=text(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "exgetdata(\\1,1)",
			'/\[data=text(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "exgetdata(\\1,1)",
	        '/\[data=size\:(.*?)(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "exgetdata(\\2,2,'\\1')",
        	'/\[data=(.*?)(?::\w+)?\](.*?)\[\/data(?::\w+)?\]/sie' => "'<div style=\"float:\\1;\">'.exgetdata(\\2).'</div>'",
		);

			$post['bit_content'] = preg_replace(array_keys($dataparse),array_values($dataparse), $post['bit_content']);



		$name = ereg_replace( "[^A-Za-z0-9\ \_]", "", strip_tags($post['bit_title']));
		$name = str_replace(" ","_",$name);
		$fname = $post['bit_id']."-".substr($name,0,20).".html";

		$post['coments'] = get_comment_count($post['bit_id']);
	
		$blogpost = NULL;	
		$blogpost['title'] = $post['bit_title'];
		$metadata = readxml($post['bit_meta']);
		$blogpost['date'] = date("jS F Y @ H:i",$post['datetime']);
		$blogpost['url'] = render_blog_link($post['bit_id'],true);

		if($metadata['METADATA']['META']){
			foreach($metadata['METADATA']['META'] as $key => $value){
				$blogpost['post'] .= "<b>".strtotitle(str_replace("_"," ",$key)).":</b> $value<br />\n";
			}
		}
		$linkeddiv = "";
		$blogpost['post'] .= bbcode($post['bit_content']);
		$blogpost['post']  .=  "<div class=\"postTools\">".list_posts(linked_from($post['bit_id']),$linkeddiv, $post['bit_id'])."</div>\n";
		$blogpost['post']  .=  "$linkeddiv\n";
			if($metadata['METADATA']['DATA']){
			$blogpost['data_title'] = "Attached Files"; 
			$datas = NULL;
			$datas = split(",",$metadata['METADATA']['DATA']);
			foreach($datas as $bit){
				$test = checkOverlay($bit);
				if ( $test ) { $blogpost['data'] .= "<span class=comment>"; }
				$blogpost['data'] .= exgetdata($bit);
				if ( $test ) { $blogpost['data'] .= "</span>"; }
			}
			$blogpost['data'] .= "<div style=\"clear:left;\"></div>";
		}
		
		$blogpost['footer'] .= "\t\t\t<a href=\"".render_link('', array('user' => $post['bit_user']))."\">".get_user_info($post['bit_user'],'name')."</a> | $insuser <a href=\"".render_link($blog['blog_sname'],array('blog_id' => $blog['blog_id'], 'group'=> $post['bit_group']))."\">".$post['bit_group']."</a> | <a class=\"gray\" href=\"#com\">Comments (".$post['coments'].")</a>\n";
		if($post['bit_id']!=-1){
		$uri = "{$ct_config['blog_url']}uri/". dechex(getbituri($post['bit_id']));
		$blogpost['footer'] .= "<br />Uri:<a href=\"$uri\">$uri</a>
			<br/>Key:{$post['bit_md5']} <br /> Last Updated:".date("jS F Y @ H:i",$post['timestamp']);
		}
//		http://blog_dev.sidious.chem.soton.ac.uk/neutral_drift/534/Betaglu_rev.html
		
;
		
		$body = blog_style_post(&$blogpost);


		

		$sql = "SELECT *,UNIX_TIMESTAMP(  `com_datetime` ) AS datetime FROM  `{$ct_config['blog_db']}`.`blog_com` 
					WHERE  `com_bit` = ".(int)$post['bit_id']."   AND `com_edit` =0 ORDER BY `com_datetime` ASC";

		$tresult = runQuery($sql,'Fetch Page Comments');
	
		if( mysql_num_rows($tresult)) {
			$body .= "<div class=\"containerComments\">";
			$body .= "<div class=\"infoSection\"><a name=\"com\"></a>Comments</div>\n";
	   		while($comment = mysql_fetch_array($tresult)){

				$comment['com_url'] = $blogpost['url'].'#'.$comment['com_id'];
				$comment['com_user'] = "<span><a href=\"".render_link('',array('user' => $comment['com_user']))."\">".get_user_info($comment['com_user'],'name')."</a> $insuser</span><br/>\n";
				$comment['com_rdate'] = date("jS F Y @ H:i",$comment['datetime']);
				$comment['com_html'] = bbcode($comment['com_cont']);

				$body .= blog_style_comment(&$comment);

			}
			$body .= "</div>";
		}
		
	
			$server_name_reg = str_replace('.', '\.',$ct_config['this_server']); 
		$body = preg_replace('/http\:\/\/'.$server_name_reg.'\/'.$request['blog_sname'].'\/([0-9]*)\/[\w]*\.html/ei', "proclink('\\1','\\0');", $body );
		
	//	$body = preg_replace('/data/ei', "proclink('\\1','\\0');", $body );

		$body .= '<div class="containerPost"><div class="infoBox">This is a snapshot of the blog taken on '.date("jS F Y @ H:i").'. To view the blog in full please go here: '."<a href=\"{$blogpost['url']}\">{$blogpost['url']}</a>".'</div></div>';



		$body = preg_replace('/\/data\/([0-9]+)\.html/i',"{$ct_config['blog_url']}data/\\1.html", $body);
		$body = preg_replace('/url\(\/getdata\.php([^\)]+)\)/i',"url({$ct_config['blog_url']}getdata.php\\1)", $body);


		if($postid != -1){
			pageify(&$body,"{$datapath_export}/{$fname}");	
		}
		

		if($postid == $indexpost){
			pageify(&$body,"{$datapath_export}/{$title_url}");
		}

}

function procdata($link){

	return $link;

}

function proclink($link,$url){

	global $posts,$ct_config;
	$link = (int)$link;

	if(in_array($link,$posts)){
		$sql = "SELECT `blog_bits`.*  FROM `{$ct_config['blog_db']}`.`blog_bits`WHERE `bit_id` = $link AND `bit_edit` = 0";
		$result = runQuery($sql,'Blogs');
		$rowb = mysql_fetch_array($result);
		$name = ereg_replace( "[^A-Za-z0-9\ \_]", "", strip_tags($rowb['bit_title']));
		$name = str_replace(" ","_",$name);
		$fname = $link."-".substr($name,0,20).".html\" class=\"intlink";
		return $fname;
	}else{
		return $url;
	}
}

function pageify($body, $path){

global $ct_config, $title;
if($ct_config['blog_style']!="default")
$extrahead  = "<link rel=\"stylesheet\" type=\"text/css\" href=\"style/{$ct_config['blog_style']}/style.css\"/>";

$page = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="padding-top: 20px;">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>$title</title>
<link rel="stylesheet" type="text/css" href="style/style.css"/>
<link rel="stylesheet" type="text/css" href="style/default/style.css"/>
{$extrahead}
<style>
	.containerPost .postLinkedItems{
		display:block;
	}
	.intlink{
		color:#012659;
	}
</style>
<body class="body_main">

<div id="page">
	<div id="top_bit" style="text-align:right;">
		<span >
		
			<a href="{$ct_config['blog_url']}">All Blogs</a> | <a href="https://sourceforge.net/apps/mediawiki/labtrove/index.php?title=Main_Page" title="The Help Guide to the blog">Help</a>
</span></div>

	<div id="header">
		<div id="sitetitle" onclick="javascript:location.href='{$ct_config['blog_url']}'">{$ct_config['blog_title']}</div>
		<div id="blogtitle">
			<h1><a href="{$title_url}" id="white">{$title}</a></h1>
			<span class="description">{$desc}</span></div>
	</div>
		<div id="content">
		{$body}
		<div class="clear"></div>
	</div>
<div id="footer">
Powered by <a href="http://labtrove.org">labtrove 2.2</a> &copy; University of Southampton
</div>
</div>

EOT;

file_put_contents($path, $page);

}




function exgetdata($bit, $text=0,$size=null){







global $datapath_export;
@mkdir("{$datapath_export}/data");
@mkdir("{$datapath_export}/data/thumbs");
@mkdir("{$datapath_export}/data/files");
@mkdir("{$datapath_export}/data/icons");

global $ct_config;
$width = 100; $height =75;
if($text==2){
list($width,$height) = split("x",$size);
}
$sql = "SELECT data_type FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $bit";
$tresult = runQuery($sql,'Fetch Page Groups');
$row = mysql_fetch_array($tresult);

global $datatypes_d;


if(!$datatypes_d[$bit])
makedatapage($bit);

$datatypes_d[$bit] = 1;

$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $bit";
	$result = runQuery($sql,'Fetch Page Groups');
	$srow = mysql_fetch_array($result);
	
	$metadata = NULL;
	$metadata = readxml($srow['data_data']);

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

   return "<a href=\"javascript:var blob = window.open('data/{$bit}.html','popup','scrollbars=1;menubar=no,height=750,width=680,resizable=yes,toolbar=no,location=no,status=no')\">".$metadata['METADATA']['TITLE']."</a>";

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
   return "<a href=\"{$ct_config['blog_path']}getdata.php?bit=$bit\" target=\"_blank\"><img src=\"{$ct_config['blog_path']}getdata.php?bit=$bit\" width=$width height=$height class=\"dataPic\"></a>";
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
	copy("{$ct_config['blog_url']}getdata.php?bit=$bit&width=$width&height=$height&thumb=1","{$datapath_export}/data/thumbs/$bit.thumb");
   $onclick = "javascript:var blob = window.open('data/{$bit}.html','popup','scrollbars=1;menubar=no,height=750,width=680,resizable=yes,toolbar=no,location=no,status=no');";
		$ret = "<div class=\"dataPic datathumb\"  onclick=\"$onclick\" style=\"width:{$width}px; height:".($height + 20)."px;\"><div style=\"background-repeat: no-repeat; background-position: center center; background-image: url('data/thumbs/$bit.thumb'); width:{$width}px; height:{$height}px; \"></div>{$metadata['METADATA']['TITLE']}</div>";
		return $ret;
   break;
case 'img_meta';
	$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $bit";
	$result = runQuery($sql,'Fetch Page Groups');
	$srow = mysql_fetch_array($result);
	
	$metadata = NULL;
	$metadata = readxml($srow['data_data']);

   return "<a href=\"javascript:var blob = window.open('/data/{$bit}.html','popup','scrollbars=1;menubar=no,height=750,width=680,resizable=yes,toolbar=no,location=no,status=no')\"><img src=\"".$metadata['METADATA']['THUMB_SRC']."\" class=\"dataPic\"></a>";
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

function makedatapage($id){

global $ct_config;

global $datapath_export;

	$sql = "SELECT data_data, data_type, data_id, data_datetime FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $id";
	$result = runQuery($sql,'Fetch Page Groups');
	$row = mysql_fetch_array($result);


switch ($row['data_type']) {

		case 'jpg':
			header('Content-type: image/jpeg');
			echo $row['data_data'];
			$sql = "SELECT data_data, data_type FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = $id";break;
	
		case 'gif':
			header('Content-type: image/gif');
			echo $row['data_data'];
			break;
	
		case 'png':
			header('Content-type: image/png');
			echo $row['data_data'];
			break;
	
		case 'text':
			header('Content-type: text/plain');
			echo $row['data_data'];
			break;
	
		case 'data_meta':
			$metadata = readxml($row['data_data']);
			
		if($ext && $ext != "html"){
			if(file_exists("export/data/{$ext}.php")){
				include("export/data/{$ext}.php");
			}
		}
			
			foreach($metadata['METADATA'] as $key=>$value){
		
				if(substr($key,0,5) == 'DATA_' || substr($key,0,5) == 'PLOT_'){
					if(!$defaulttype_bk)
						$defaulttype_bk = $key;
					
					switch($key){
						case 'DATA_ASC':
						case 'DATA_JPG':
						case 'DATA_GIF':
						case 'DATA_PNG':
						case 'DATA_PDF':
						case 'DATA_PL':
						case 'DATA_PHP':
						case 'DATA_ASP':
						case 'DATA_SH':
						case 'DATA_C':
						case 'DATA_CPP':
						case 'DATA_CSS':
						case 'DATA_M':
						case 'DATA_SQL':
						case 'DATA_RB':
						case 'DATA_TXT':
						case 'DATA_XML':
						case 'DATA_RDF':
						case 'DATA_JAVA':
						case 'DATA_KML':
					if(!$defaulttype)
						$defaulttype = $key;
					if($value['MAIN']==1){
						$defaulttype = $key;
					}

					}

					$datalist[$key] = $value;

				}

			}	
				if(!$defaulttype) $defaulttype = $defaulttype_bk;
			

			if( $metadata['METADATA'][$defaulttype]['ID'] + 0 ){
				 if(!strlen($metadata['METADATA'][$defaulttype]['NAME']))
						$path = render_link('')."data/files/{$id}/".$metadata['METADATA'][$defaulttype]['ID'].".".strtolower(substr($key,5));
	    	   		else 
						$path = render_link('')."data/files/{$id}/".$metadata['METADATA'][$defaulttype]['NAME'];
			} else {
				$path = $metadata['METADATA'][$defaulttype]['URL'];
			}
		
			switch ($defaulttype) {
				case 'DATA_ASC':
					$data = file_get_contents($path."&data_type=text");
					$img =  code_render_datapre($data,"txt");
					$not_viewable = 1;
				break;

				case 'DATA_JPG':
				case 'DATA_GIF':
				case 'DATA_PNG':
					$distype="img";
					if(!$metadata['METADATA'][$defaulttype]['SIZE_X'] || !$metadata['METADATA'][$defaulttype]['SIZE_Y']){
						list($metadata['METADATA'][$defaulttype]['SIZE_X'], $metadata['METADATA'][$defaulttype]['SIZE_Y']) = getimagesize($path);
					$metadata['METADATA']['SIZE_X'] = $metadata['METADATA'][$defaulttype]['SIZE_X'];
					$metadata['METADATA']['SIZE_Y'] = $metadata['METADATA'][$defaulttype]['SIZE_Y'];
		
					}
			   		$imagepath = $path;
					break;

				case 'PLOT_TIME':
					$distype="timeplot";

					$topic = $metadata['METADATA']['PLOT_TIME']['TOPIC'];

					if ( strlen($topic) == 0 ) {
						$building = $metadata['METADATA']['PLOT_TIME']['BUILDING'];
						$room = $metadata['METADATA']['PLOT_TIME']['ROOM'];

					}

					else { 
						list ($building, $room) = split(":", $topic);
						$building = substr($building, 1);
						$room = substr($room, 0, -2);
					}

					$startTime = $metadata['METADATA']['PLOT_TIME']['START'];
					$endTime = $metadata['METADATA']['PLOT_TIME']['END'];
					break;

				case 'DATA_PDF':
					$data = $path."&filename=".$metadata['METADATA'][$defaulttype]['NAME'];
					$img =  "<EMBED src=\"$data\" width=\"660\" height=\"660\"></EMBED>";
					$not_viewable = 1;
					break;

				case 'DATA_PL':
				case 'DATA_PHP':
				case 'DATA_ASP':
				case 'DATA_SH':
				case 'DATA_C':
				case 'DATA_CPP':
				case 'DATA_CSS':
				case 'DATA_M':
				case 'DATA_SQL':
				case 'DATA_RB':
				case 'DATA_TXT':
				case 'DATA_XML':
				case 'DATA_RDF':
				case 'DATA_JAVA':
					$data = file_get_contents($path."&data_type=fdown");
					$img =  code_render_datapre($data,$ct_config['geshi']['lang_map'][$defaulttype]);
					$not_viewable = 1;
					break;

				case 'DATA_KML':
				if($ct_config['blog_map_key']){
					$data = $path."&filename=".rawurlencode($metadata['METADATA'][$defaulttype]['NAME']);
					
					$kml =  file_get_contents($data);

					preg_match_all("/(<coordinates>)([^<]*)(<\/coordinates>)/", $kml, $matchs, PREG_PATTERN_ORDER);
					foreach( $matchs[0] as $part){
						foreach(preg_split("/\s+/",strip_tags($part)) as $chunk){
							$chunk = preg_replace ("/\s+/", "", $chunk);
							if(strlen($chunk)){
							list($lng,$lat,$alt) = preg_split("/\s*,\s*/", $chunk);
							$longs[] = $lng;
							$lats[] = $lat;
							}
						}

					}
					$ave_lat = array_sum($lats) / count( $lats );
					$ave_lng = array_sum($longs) / count( $longs );
					

				$head .= '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$ct_config['blog_map_key'].'" type="text/javascript"></script>
    <script type="text/javascript">

    //<![CDATA[

    function load() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));

      map.addControl(new GLargeMapControl());
      map.addControl(new GMapTypeControl());
		var rectObj = new GLatLngBounds(new GLatLng('.min($lats).','.min($longs).'), new GLatLng('.max($lats).','.max($longs).'));
		var zm = map.getBoundsZoomLevel(rectObj);	

      map.setCenter(new GLatLng('.$ave_lat.', '.$ave_lng.'), zm);

       var kml = new GGeoXml("'.$data.'");
   		map.addOverlay(kml)
      }
    }

    //]]>
    </script>';

			$body_tag = 'onload="load()" onunload="GUnload()"';
					$img =  '   <div id="map" style="width: 660px; height: 660px"></div>';
					$not_viewable = 1;				
					break;
				}
				default:
					$img =  "<div class=\"postTitle\" align=\"center\">No Viewable Data Type, Please download to view.</div>";
					$not_viewable = 1;
					break;

			}

			break;
	
		case 'img_meta':
			$metadata = readxml($row['data_data']);
			$distype="img";
			$imagepath = $metadata['METADATA']['PREVIEW_SRC'];
			break;

		case 'asc':
			break;
	
		default:

	}

	$limitx = 640;
	$limity = 480;

	// image data stuff
	if($distype=="img"){
		
		if(!$metadata['METADATA']['SIZE_X'] || !$metadata['METADATA']['SIZE_Y']){
			list($metadata['METADATA']['SIZE_X'], $metadata['METADATA']['SIZE_Y']) = getimagesize($imagepath);
		}

		if(1){
		//if($metadata['METADATA']['SIZE_X']>$limitx || $metadata['METADATA']['SIZE_Y']>$limity){
			if(($metadata['METADATA']['SIZE_X']/$limitx)>($metadata['METADATA']['SIZE_Y']/$limity)){
				$sizex = $limitx; 
				$sizey = (int)(($metadata['METADATA']['SIZE_Y']/$metadata['METADATA']['SIZE_X'])*$limitx);
				$imgheight = (int)(($limity-$sizey)/2);
			}
			
			else {
				$sizey = $limity; 
				$sizex = (int)(($metadata['METADATA']['SIZE_X']/$metadata['METADATA']['SIZE_Y'])*$limity);
				$imgheight = 0;
			}

		}
		
		else {

			//no resizings needed
			$sizex = $metadata['METADATA']['SIZE_X'];
			$sizey = $metadata['METADATA']['SIZE_Y'];
			$imgheight = (int)(($limity- $metadata['METADATA']['SIZE_Y'])/2);
		}  
		
		if($metadata['METADATA'][$defaulttype]['ID'] + 0 ){

		}

		if( $metadata['METADATA'][$defaulttype]['ID'] + 0 ){
				 if(!strlen($metadata['METADATA'][$defaulttype]['NAME']))
						$imagepath = "files/{$id}/".$metadata['METADATA'][$defaulttype]['ID'].".".strtolower(substr($key,5));
	    	   		else 
						$imagepath = "files/{$id}/".$metadata['METADATA'][$defaulttype]['NAME'];
		}

		$img = "<div align=\"center\" style=\"padding-top:".$imgheight."px;\"><img src=\"".$imagepath."\" width=\"$sizex\" height=\"$sizey\"></div>\n\n";
	
	}

	elseif ($distype=="timeplot") {

		$img = "<iframe src=\"".$ct_config['timeplot']."?building=$building&room=$room&chemtools=true&start=".$startTime."&end=".$endTime."\" width=\"635\" height=\"475\"><iframe>";

	}


	//Overlays

	//overlay stuff
	$overlayIDs = array();

	if($metadata['METADATA']['OVERLAY']) {
	
		$overlay_links = "\t\t<span>This Image has overlays</span>\n\n";
		$datas = split(",",$metadata['METADATA']['OVERLAY']);
		$table_cols = 3;
		$col = 0;

		$overlay_links .= "<table width=640><tr>";
		foreach($datas as $bit){
			$col++;

			$sql = "SELECT data_data FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = ".$bit;
			$result = runQuery($sql,'Fetch Page Groups');
			$srow = mysql_fetch_array($result);
	
			$overlay= NULL;
			$overlay = readxml($srow['data_data']);

			$overlayIDs[] = $overlay['METADATA']['ID'];

			$overlay_links .= "\t\t<td><input type=\"checkbox\" name=\"overlay\" onClick=\"toggleDisplay(".$overlay['METADATA']['ID'].")\"> ".$overlay['METADATA']['TITLE']." (".$overlay['METADATA']['USER'].") </td>\n\n";

			if($col == 	$table_cols){
				$col = 0;
				$overlay_links .= "</tr><tr>";
			}
	
		}
		$overlay_links .= "</tr></table>";
		
	
	}
	//Archives
	$body .= "\t\t<div class=\"postTitle\" align=\"center\">".$metadata['METADATA']['TITLE']."</div><br>\n";

	$f = 0;	
	// overlay stuff
	if($metadata['METADATA']['OVERLAY']) {

		$body .= "\t<div class=image_over_box id=image_over_box style=\"visibility:visible;\">\n\n";

		foreach ($overlayIDs as $overlayID) {
	
			$body.= "\t\t<div id=$overlayID align=\"center\"  align=\"center\" style=\"top:{$imgheight}px; display:none; position: relative; z-index:".(10+($f++))."; height:0px;\">
						<img src=\"/data/files/{$overlayID}.png\"></div>\n\n";

		}
		
		$body .= "\t</div>\n\n";
	
	}

//image
	$body .= "\t<div class=imagep_box id=imagep_box style=\"visibility:visible;\">\n\n\t\t$img\t</div>\n\n";


	$head .= "<script type=\"text/javascript\">

	function toggleDisplay(divId) {

		var div = document.getElementById(divId);

		if ( div.style.display == 'none' ) {

			div.style.display = 'block';

		}

		else { 

			div.style.display = 'none';

		}

	}

	</script>";



$body .= "\t<br><div class=\"info_image\" style=\"width:640px;\">\n\n";

	
	if($metadata['METADATA']['PICTURE_URL'])
		$body .= "\t\t<a href=\"".$metadata['METADATA']['PICTURE_URL']."\" target=\"_blank\">Hosted Page</a><br/><br/>\n\n";

		$body  .= $overlay_links;

	if(isset($datalist)){
	
		$body .= "\t\t<div style=\"\">Download as:<br />";
	
		foreach( $datalist as $key => $value){
		
			if( strtolower($value['TYPE'])=='local'){
			
				if(!strlen($value['NAME']))
					$url = render_link('')."data/files/".$value['ID'].".".strtolower(substr($key,5));
	       		else 
					$url = render_link('')."data/files/".$value['ID']."/".$value['NAME'];
	       			@mkdir("{$datapath_export}/data/files/{$id}/");
					copy("$url", "{$datapath_export}/data/files/{$id}/".basename($url));

				$body .= "\t\t<a href=\"files/{$id}/".basename($url);
			}
		
			else { $body .= "\t\t<a href=\"".$value['URL']; }
			
			$icon = substr(file_img(substr($key,5)),1);
			copy("{$ct_config['pwd']}/docs/$icon", "{$datapath_export}/data/icons/".basename($icon));
			$body .= "\"><img src=\"icons/".basename($icon)."\" style=\"vertical-align:middle;\"> ".substr($key,5)."</a>&nbsp; ";
	
		}

		$body .= "\t\t</div>\n\n";

	}



	$body .= "\t</div>\n\n";
 

$bodytag = "style=\"width: 655px; margin:auto;\" $body_tag";
$minipage = 1;



$page = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="padding-top: 20px;">

<head>
'.$head.'
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Data Item '.$id.'</title>
<link rel="stylesheet" type="text/css" href="style/style.css"/>
<link rel="stylesheet" type="text/css" href="style/'.$ct_config['blog_style'].'/style.css"/>
<body class="body_pop" '.$bodytag.'>'.$body.'
</body>
</html>';
file_put_contents("{$datapath_export}/data/{$id}.html",$page);

}

	file_put_contents("$datapath/status","compressing");
	`cd $datapath; zip -q -r {$request['blog_sname']}.zip {$request['blog_sname']}`;
	$resp = "Complete (Download export*: <a href=\"{$ct_config['blog_url']}cache/blogdumps/{$hashkey}/{$request['blog_sname']}.zip\">{$request['blog_sname']}.zip</a>)";
	$resp .= "<br/><small>*The download will be available for {$ct_config['gc']['blogdumps']} hours</small>";
	file_put_contents("$datapath/status",$resp);
	
	`rm -rf $datapath/{$request['blog_sname']}`;
?>
