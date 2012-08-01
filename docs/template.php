<?php

include("../lib/default_config.php");



//Load Blog info
if($_REQUEST['bit_id']){
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE `bit_edit` = 0 AND `bit_id` = ".(int)$_REQUEST['bit_id'];
	$result = runQuery($sql,'Get blog Id');
	$blog = mysql_fetch_array($result);
		if((int)$_REQUEST['blog_id']){
	$_SESSION['blog_id'] = (int)$_REQUEST['blog_id'];
	$blog_id = (int)$_REQUEST['blog_id'];
		}else{
	$_SESSION['blog_id'] = (int)$blog['bit_blog'];
	$blog_id = (int)$blog['bit_blog'];
		}
}else{
	header("Location: {$ct_config['blog_path']}?msg=forbidden");
	exit();
}

checkblogconfig($blog_id);

$blog = db_get_blog_by_id($blog_id);

$title = $blog['blog_name'];
$desc = $blog['blog_desc'];

$sql = "SELECT `bit_group` FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE `bit_blog` = ".$blog_id." AND bit_edit = 0 GROUP BY bit_group ";
$tresult = runQuery($sql,'Fetch Page Groups');
while($row = mysql_fetch_array($tresult)){
		$bloggroups[] = $row['bit_group'];


}

function listoptions($blog_id,$type,$val){
global $ct_config;
	if($blog_id) $blog_dit = "AND `bit_blog` = ".$blog_id.""; 
	if($type=="%"&& $val=="%"){
		$sqlb = "SELECT  `bit_id` ,  `bit_user` ,  `bit_title` FROM  `{$ct_config['blog_db']}`.`blog_bits`  WHERE    bit_edit = 0 $blog_dit ORDER BY  `bit_datestamp` DESC " ;
	}else
		$sqlb = "SELECT  `bit_id` ,  `bit_user` ,  `bit_title` FROM  `{$ct_config['blog_db']}`.`blog_bits`  WHERE  `bit_meta` LIKE '%<meta>%<".$type.">".$val."</".$type.">%</meta>%' $blog_dit AND bit_edit = 0 ORDER BY  `bit_datestamp` DESC " ;
	$tresult = runQuery($sqlb,'Fetch Page Groups');
    
	$ret .= "<option value=\"\"></option>";
    while($row = mysql_fetch_array($tresult)){

	$ret .= "<option value={$row['bit_id']}>{$row['bit_title']}</option>";
	}	
	return  $ret;
}


function srcblog($blog_name){
global $ct_config,$src_blog;
	if($blog_name=="all"){
		return $src_blog = 0;	
	}
	$sqlb = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_blogs` WHERE  `blog_sname` LIKE  '{$blog_name}'" ;
	$tresult = runQuery($sqlb,'Fetch Page Groups');
    
	if($row = mysql_fetch_array($tresult)){
		$src_blog = $row['blog_id'];
	}else{
		return " >>[b]Source Blog Not Found[/b]<< ";
	}

	return  $src_blog;
}

function srcblogid($blog_name){
global $ct_config;
	if($blog_name=="all"){
		return 0;	
	}
	$sqlb = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_blogs` WHERE  `blog_sname` LIKE  '{$blog_name}'" ;
	$tresult = runQuery($sqlb,'Fetch Page Groups');
    	if($row = mysql_fetch_array($tresult)){
		return $row['blog_id'];
	}else{
		return -1;
	}
}

$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_id` = {$_REQUEST['bit_id']} AND  `bit_edit` =0";
$result = runQuery($sql,'Blogs');
$rowd = mysql_fetch_array($result);

if($_REQUEST['add_blog']){

$posttxt = addslashes($rowd['bit_content']);
$i = 0;
 $preg = array(
   
	
//[data]
	
'/\[\[box\]\]/sie'=> " \$_REQUEST['templateval_'.\$i++] ",
'/\[\[box=([^\]]*?)\]\]/sie'=>" \$_REQUEST['templateval_'.\$i++] ",
	
'/\[\[checkbox\]\]/sie'=> " isset(\$_REQUEST['templateval_'.\$i++]) ? '&#9745;' : '&#9744;' ",
		

'/\[\[blog\]\]/sie'=>" '[blog]'.\$_REQUEST['templateval_'.\$i++].'[/blog]' ",
'/\[\[([^\]]*?)\:([^\]]*?)\]\]/sie'=>" '[blog]'.\$_REQUEST['templateval_'.\$i++].'[/blog]' ",

'/\[\[([^\]]*?)>([^\]]*?)\]\](\s*)/sie'=>"",


'/\<\<([^\]]*?)\>\>/si' => "",
'/\[\[([^\]]*?)\]\]/si' => ""
	
	

	//	'/\[\[box\]\](.*?)\[\/data(?::\w+)?\]/sie'=>"'<div style=\"float:left;\">'.getdata(\\1).'</div>'"
	 
  );

	$posttxt = preg_replace(array_keys($preg), array_values($preg), $posttxt);

	$metad = null;	
		$metadata = "";
		//check for new value
		if($_REQUEST['meta_keyn'] && $_REQUEST['meta_valuen']){
		$_REQUEST['metat_key'][] = $_REQUEST['meta_keyn'];
		$_REQUEST['metat_value'][] = $_REQUEST['meta_valuen'];
		}

			if($_REQUEST['metat_key']){
				
				foreach($_REQUEST['metat_key'] as $key => $keyn){
					if($keyn && $_REQUEST['metat_value'][$key]){
							$metadata['METADATA']['META'][strtoupper(str_replace(" ","_",$keyn))] = addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['metat_value'][$key])));
					}
				}
				
				$metad = writexml($metadata);
			}

		$id	= add_blog($blog_id, $_REQUEST['comment_title'], $posttxt, $metad, $_REQUEST['section']);
		header("Location: ".render_blog_link($id,1)."?action=edit" );
		exit();


}else{

$src_blog = $blog_id;

$posttxt = $rowd['bit_content'];
$i = 0;
 $preg = array(
   
'/\[\[srcblog\=([^\]]*?)\]\]/sie' => "'<<'.srcblog(\"\\1\").'>>'",

	//[data]
'/\[\[box\]\]/sie'=>"'<input type=\"text\" name=\"templateval_'.\$i++.'\" />'",
'/\[\[box=([^\]]*?)\]\]/sie'=>"'<input type=\"text\" size=\"\\1\" name=\"templateval_'.\$i++.'\" />'",


'/\[\[checkbox\]\]/sie'=>"'<input type=\"checkbox\" name=\"templateval_'.\$i++.'\" />'",	

'/\[\[blog\]\]/sie'=>"'Post Id:<input type=\"text\" name=\"templateval_'.\$i++.'\" size=6 />'",

'/\[\[([^\]]*?)\:([^\]]*?)\:([^\]]*?)\]\]/sie'=>"'<select name=\"templateval_'.\$i++.'\">'.listoptions(srcblogid(\"\\1\"),\"\\2\",\"\\3\").'</select>'",
'/\[\[([^\]]*?)\:([^\]]*?)\]\]/sie'=>"'<select name=\"templateval_'.\$i++.'\">'.listoptions(\$src_blog,\"\\1\",\"\\2\").'</select>'",

'/\[\[Section>([^\]]*?)\]\](\s*)/sie'=>" '<<'.(\$_REQUEST['section'] = \"\\1\").'>>'",
'/\[\[([^\]]*?)>([^\]]*?)\]\](\s*)/sie'=>" '<<'.(\$_REQUEST['metat_key'][] = \"\\1\").(\$_REQUEST['metat_value'][] = \"\\2\").'>>'",


'/\<\<(.*?)\>\>/si' => "",
'/\[\[([^\]]*?)\]\]/si' => ""
	
	

	//	'/\[\[box\]\](.*?)\[\/data(?::\w+)?\]/sie'=>"'<div style=\"float:left;\">'.getdata(\\1).'</div>'"
	 
  );

	$posttxt = preg_replace(array_keys($preg), array_values($preg), $posttxt);
	
$posttxt =  bbcode($posttxt);
}
$body .= "\t<div class=\"containerPost\">\n";

$body .= "\t<div class=\"postTitle\">Add Post From Template</div>\n";


$body .= "\t<div class=\"postText\">\n

<script language=\"JavaScript\" type=\"text/javascript\">

function NewSection() {

if (document.blog.section.value == '- New section -') {

var new_section = prompt (\"New section name:\",\"\")

document.blog.section.options[2] = new Option(new_section,new_section);
document.blog.section.options[2].selected = true;


}

}
function NewMeta() {
if (document.blog.meta_keyn.value == '- New Field -') {

var new_section = prompt (\"New meta field name:\",\"\")

document.blog.meta_keyn.options[2] = new Option(new_section,new_section);
document.blog.meta_keyn.options[2].selected = true;


}

}


</script>
";

$body .= "
<form action=\"template.php?bit_id={$_REQUEST['bit_id']}\" name=\"blog\" id=\"post_form\" method=\"post\" target=\"_self\">";

$body .= "<input type=\"hidden\" name=\"add_blog\" value=\"1\" />";
$body .= "<input type=\"hidden\" name=\"jsact\" value=\"\" />";
$body .= "<input type=\"hidden\" name=\"jsval\" value=\"\" />";
$body .= "<input type=\"hidden\" name=\"blog_id\" value=\"$blog_id\" />";

$body .="Title<span class=\"formreq\">*</span> <br/><input type=\"text\" name=\"comment_title\" class=\"comment_title required\" size=\"80\" value=\"".$rowd['bit_title']."\" /><br/><br/>";

$body .= "\t\tText<span class=\"formreq\">*</span><br><div class=\"dataBox\">$posttxt</div>";

$body .= "<br/>Section<span class=\"formreq\">*</span> 
<select name=\"section\" onchange=\"javascript:NewSection();\" class=\"required\"><option value=''></option>
";

$found = 0;
if(isset($bloggroups)){
foreach($bloggroups as $group){
	if($group == stripslashes($_REQUEST['section'])){
	$body .= "<option value=\"$group\" selected='selected'>$group</option>\n";
	$found = 1;
		}else{
	$body .= "<option value=\"$group\">$group</option>\n";
	}
}
}

if(!$found && $_REQUEST['section'])
	$body .= "<option value=\"".stripslashes($_REQUEST['section'])."\" selected='selected'>".stripslashes($_REQUEST['section'])."</option>\n";
	
$body .= "
<option value='- New section -'>- New section -</option></select><br/>";

$body .= "Metadata: <br />";

if($_REQUEST['metat_key']){

foreach($_REQUEST['metat_key'] as $key => $keyn){
			if($keyn && $_REQUEST['metat_value'][$key]){
				//$metadata['METADATA']['META'][strtoupper(str_replace(" ","_",$keyn))] = addslashes(str_replace(array(' ','/'),array('_','-'),$_REQUEST['meta_value'][$key])) ;
			$body .= "key:<input type=\"text\" name=\"metat_key[]\" value=\"".strtotitle(str_replace("_"," ",$keyn))."\" /> value:<input type=\"text\" name=\"metat_value[]\" value=\"".$_REQUEST['metat_value'][$key]."\" /><br>";		
	}
	}
}

$body .="You can add/delete metadata at the next step";

$body .= "<br/><br/>Blog: ";

$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_blogs` INNER JOIN `{$ct_config['blog_db']}`.`blog_types` ON `blog_type` = `type_id`  WHERE `blog_del` = 0 AND blog_redirect = '' ORDER BY  `blog_types`.`type_order` ASC  ";
$result = runQuery($sql,'Blogs');
$body .= "<select name=\"blog_id\" style=\"width:120px;\" onChange=\"document.blog_id.submit();\">";
		while($rowb = mysql_fetch_array($result)){
	if(($rowb['blog_zone']==0) || (checkzone($rowb['blog_zone'],0,$rowb['blog_id'])) || ($_SESSION['user_admin'] > 1)){
		$body .= "<option value={$rowb['blog_id']}";
		if($blog_id == $rowb['blog_id']){
			$body .= " selected";
		}
		$body .= "> {$rowb['blog_name']} </option>";
	}
	

}
$body .= "</select>";


$jquery['validate'] = true;
$jquery['function'] .= "$('#post_form').validate();\n";

$body .= "<center style=\" padding-top: 10px; padding-bottom: 15px; margin:auto;\">";
$body .= mkButton("disk","Save", array("class"=>"withbox", "onClick"=>"javascript: $('#post_form').submit();"));
$body .= mkButton("delete","Cancel", array("class"=>"withbox",  "onClick"=>"if(!confirm('Are you sure you want to cancel, all changes will be lost.')) return false;", "href"=>render_blog_link($rowd['bit_id'],1)));
$body .= "</center>";




$body.= "</form>";

	

$body.= "</div></div>";



include('page.php');

?>
