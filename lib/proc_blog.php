<?php


//print_r($_REQUEST);
//exit();


if(isset($_REQUEST['flip_keys']) && $_REQUEST['flip_keys']==1){
	if($_COOKIE['showkeys']){
		$_COOKIE['showkeys'] = 0;
		setcookie("showkeys", 0, time()+(3600*24*30),'/');
	}else{
		$_COOKIE['showkeys'] = 1;
		setcookie("showkeys", 1, time()+(3600*24*30),'/');
	}
}
if(isset($_REQUEST['flip_qr']) && $_REQUEST['flip_qr']==1){
	if($_COOKIE['showqr']){
$_COOKIE['showqr'] = 0;
		setcookie("showqr", 0, time()+(3600*24*30),'/');
	}else{
		$_COOKIE['showqr'] = 1;
		setcookie("showqr", 1, time()+(3600*24*30),'/');
	}
}




if(isset($_REQUEST['action_com']) && $_REQUEST['action_com']=="Submit"){

	if(strlen($_REQUEST['comment_title']) && strlen($_REQUEST['text'])){

		$id = add_com($request['bit_id'], $_REQUEST['comment_title'], $_REQUEST['text']);
		header("Location: ".render_link($blog['blog_sname'],array('bit_id' => $request['bit_id']))."#$id" );
		exit();

	}else{
		$errmsg = "Check all Fields, could be title";
	}
}


if(isset($_REQUEST['action_comedit']) && $_REQUEST['action_comedit']=="Save"){

	if(strlen($_REQUEST['comment_title']) && strlen($_REQUEST['text']) && strlen($_REQUEST['editwhy'])){

			$new_id = edit_com($_REQUEST['comid'],$_REQUEST['editwhy'],$_REQUEST['comment_title'],$_REQUEST['text']);
		header("Location: ".render_link($blog['blog_sname'],array('bit_id' => $request['bit_id'])).'#'.$_REQUEST['comid']);
		exit();
	}else{
		$errmsg = "Check all Fields, could be title or edit reason.";
	}
}

if(isset($_REQUEST['jsact']) && $_REQUEST['jsact']=="action_metaa"){
	$_REQUEST['metat_key'][] = $_REQUEST['meta_keyn'];
	$_REQUEST['metat_value'][] = $_REQUEST['meta_valuen'];
}
if(isset($_REQUEST['jsact']) && $_REQUEST['jsact']=="action_metad"){
		
	unset($_REQUEST['metat_key'][($_REQUEST['jsval'])]);
	unset($_REQUEST['metat_value'][($_REQUEST['jsval'])]);

}
if(isset($_REQUEST['jsact']) && $_REQUEST['jsact']=="action_metaod"){
	$_SESSION['delmetakeys'][$_REQUEST['jsval']] = 1;
}


//Post Submit
if((isset($_REQUEST['action_post']) && $_REQUEST['action_post']=="Submit") && $_SESSION['user_name'] && ($_SESSION['user_admin'] > 1 || $user_can_post)){


	if(strlen($_REQUEST['comment_title']) && strlen($_REQUEST['text']) && strlen($_REQUEST['section'])){
		$metad = null;	
		$metadata = "";
		
			if($_REQUEST['meta_key']){
				
				$metadata['METADATA']['META'] = array();
				foreach($_REQUEST['meta_key'] as $key => $keyn){
					if($keyn && $_REQUEST['meta_value'][$key]){
							$keyname = strtoupper(str_replace(" ","_",$keyn));
							if(isset($metadata['METADATA']['META'][$keyname]))
								$metadata['METADATA']['META'][$keyname] .= ";".addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['meta_value'][$key])));
							else
							$metadata['METADATA']['META'][$keyname] = addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['meta_value'][$key])));
					}
				}
				$metad = writexml($metadata);
			}

		$id	= add_blog($blog_id, $_REQUEST['comment_title'], $_REQUEST['text'], $metad, $_REQUEST['section']);
		header("Location: ".render_blog_link($id,true));
		exit();
	}else{
		$_REQUEST['add_blog']=1;
		$errmsg = "Check Fields;";
		if(!$_REQUEST['comment_title'])
			$errmsg .= " Try Title,"; 
		if(!$_REQUEST['text'])
			$errmsg .= " Try Content Text,"; 
		if(!$_REQUEST['section'])
			$errmsg .= " Try Selecting A Section"; 
		
	}
}

//Post Edit
if((isset($_REQUEST['action_edit']) && $_REQUEST['action_edit']=="Submit") && $_SESSION['user_name'] && ($_SESSION['user_admin'] > 1 || $user_can_post) && (isset($_REQUEST['itemloop']) && $_REQUEST['itemloop']==0)){

	$sql =	"SELECT  bit_meta, bit_user FROM  `{$ct_config['blog_db']}`.`blog_bits` ";
		$sql .= "WHERE `bit_id` = ".(int)$request['bit_id']." AND bit_edit = 0";

		$tresult = runQuery($sql,'Blogs');

		$row = mysql_fetch_array($tresult);

		if( $_SESSION['user_name']!=$row['bit_user'] && !$_SESSION['user_admin'] > 1 && !$user_can_edit){
			header("Location: ".$blogpost['furl']);
			exit();
		}


		$metadata = readxml($row['bit_meta']);
		
		$metadata['METADATA']['META'] = NULL;
		if(is_array($_REQUEST['meta_key'])){
		foreach($_REQUEST['meta_key'] as $key => $keyn){
			if($keyn && $_REQUEST['meta_value'][$key]){
				$keyname = strtoupper(str_replace(" ","_",$keyn));
			
				if(isset($metadata['METADATA']['META'][$keyname]))
					$metadata['METADATA']['META'][$keyname] .= ";".addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['meta_value'][$key])));
				else
				$metadata['METADATA']['META'][$keyname] = addslashes(str_replace(array(' ','/'),array('_','-'),stripslashes($_REQUEST['meta_value'][$key])));
				
			}
		}
		}
		
		$metad = null;
		$metad = writexml($metadata);



	if(strlen($_REQUEST['comment_title']) && strlen($_REQUEST['text']) && strlen($_REQUEST['editwhy'])){
	$new_id = edit_blog($request['bit_id'],$_REQUEST['editwhy'], $_REQUEST['comment_title'], $_REQUEST['text'], $metad, $_REQUEST['section']);
	unset($_SESSION['delmetakeys']);
	header("Location: ".render_link($blog['blog_sname'],array('bit_id' => $request['bit_id'])) );
	exit();	
	}else{
	    $_REQUEST['action'] = "edit";	
		$errmsg = "Check all Fields, could be title or reason for the edit";
	}
}


if((isset($_REQUEST['action_edit']) && $_REQUEST['action_edit']=="Submit") && (isset($_REQUEST['itemloop']) && $_REQUEST['itemloop']==1) ){
	$_REQUEST['action'] = "edit";
}

///Add/Edit Blog Post Form
if(((isset ($_REQUEST['add_blog']) && $_REQUEST['add_blog'] != false) && $user_can_post)  || (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit")){

$body .= makesidebar();

if($errmsg)
$body .= blog_style_error($errmsg);

$edittext = stripslashes($_REQUEST['text']);
$edittitle = stripslashes($_REQUEST['comment_title']);
$editgroup = stripslashes($_REQUEST['section']);
$blogpost = NULL;	


if($_REQUEST['action'] == "edit"){
	$blogpost['title'] = "Edit Post";

	$post = db_get_post_by_id($request['bit_id']);



if(!$edittext)
$edittext = $post['bit_content'];
if(!$edittitle)
$edittitle =   $post['bit_title'];
if(!$editgroup)
$editgroup =   $post['bit_group'];
	$metadata = NULL;
	$metadata = readxml($post['bit_meta']);


$blogpost['furl'] = render_blog_link($post['bit_id'],true);

	if( $_SESSION['user_name']!=$post['bit_user'] && !($_SESSION['user_admin'] > 1) && !$user_can_edit){
		newMsg("Forbidden: You are not allowed to edit this post", "error");
		header("Location: ".$blogpost['furl']);
		exit();
	}

	
$blogpost['hiddenform'] = "<input type=\"hidden\" name=\"aaction\" value=\"edit\" />";
}else{
$blogpost['title'] = "Add Post";
$blogpost['furl'] = render_link($blog['blog_sname']);
$blogpost['hiddenform'] = "<input type=\"hidden\" name=\"add_blog\" value=\"1\" />";
}

$blogpost['post'] = "

<script language=\"JavaScript\" type=\"text/javascript\">



</script>
";

$blogpost['post'] .= "
<form action=\"".$blogpost['furl']."\" name=\"blog\" id=\"post_form\" method=\"post\" target=\"_self\">";

$blogpost['post'] .= $blogpost['hiddenform'];

$blogpost['post'] .= "<input type=\"hidden\" name=\"itemloop\" value=\"0\" />";
$blogpost['post'] .= "<input type=\"hidden\" name=\"jsact\" value=\"\" />";
$blogpost['post'] .= "<input type=\"hidden\" name=\"jsval\" value=\"\" />";
$blogpost['post'] .= "<input type=\"hidden\" name=\"blog_id\" value=\"$blog_id\" />";

//markitup
$jquery['markitup'] = true;
$jquery['validate'] = true;
$jquery['fieldselection'] = true;

$jquery['edit-post'] = true;

$jquery['function'] .= "$('#bbcode').markItUp(mySettings);\n";
$jquery['function'] .= "$('#post_form').validate();\n";

$jquery['code'] .= "var blog_id = {$blog_id};\n";

$blogpost['post'] .="Title<span class=\"formreq\">*</span>  <br/><input type=\"text\" name=\"comment_title\" class=\"comment_title required\" size=\"50\" value=\"".$edittitle."\"/><br/>

<br/>Text<span class=\"formreq\">*</span>  <br/><textarea name=\"text\" id=\"bbcode\" class=\"required\">".$edittext."</textarea><br/>
<table style='border: 1px solid darkgrey; width: 552px; padding: 10px; margin-bottom: 10px;'><tr><td>
Section<span class=\"formreq\">*</span><br/>
<select style='width:150px' name=\"section\" onchange=\"javascript:NewSection();\" class=\"required\"><option value=''></option>";

$found = 0;
if(isset($bloggroups)){
foreach($bloggroups as $group){
	if($editgroup == $group || $group == stripslashes($_REQUEST['section'])){
	$blogpost['post'] .= "<option value=\"$group\" selected='selected'>$group</option>\n";
	$found = 1;
		}else{
	$blogpost['post'] .= "<option value=\"$group\">$group</option>\n";
	}
}
}

if(!$found && $_REQUEST['section'])
	$blogpost['post'] .= "<option value=\"".stripslashes($_REQUEST['section'])."\" selected='selected'>".stripslashes($_REQUEST['section'])."</option>\n";
	
$blogpost['post'] .= "
<option value='- New section -'>- New section -</option></select><br/><br/>";
$ii = 0;
$blogpost['post'] .= "Metadata<table style='margin: 10px' id='metadata_table'>";
$blogpost['post'] .= "<tr><td></td><td align=center>key</td><td align=center>value</td></tr>";

// $blogpost['post'] .= "<table id=\"metadata_table\">";
// $blogpost['post'] .= "<tr><td>Extra Metadata:</td><td align=center>key</td><td align=center>value</td></tr>";
if(is_array($metadata['METADATA']['META'])){
		foreach($metadata['METADATA']['META'] as $key => $val){
			$keysvals = explode(";",$val);
			foreach($keysvals as $value){
				$blogpost['post'] .= "<tr id=\"meta_row_{$ii}\"><td></td>";
				$blogpost['post'] .= "<td><input type=\"text\" name=\"meta_key[]\" id=\"meta_key_{$ii}\" value=\"".strtotitle(str_replace("_"," ",$key))."\" class=\"required\" style=\"width: 100px;\" /> </td>";
				$blogpost['post'] .= "<td><input type=\"text\" name=\"meta_value[]\" id=\"meta_value_{$ii}\" value=\"$value\" class=\"required\"  style=\"width: 180px\" /></td>";
				$blogpost['post'] .= "<td>".mkButton("table_delete","",array("onClick"=>"removeMetadata({$ii})"))."</td>";
				$blogpost['post'] .= "</tr>";
				$ii++;
			}
		}
	}

$blogpost['post'] .= "<tr><td></td>";
// $blogpost['post'] .= "<td align=right>New:</td>";
$blogpost['post'] .= "<td><select name=\"meta_key[]\" id=\"metadata_key_new\" style=\"width: 106px\" ><option value=''></option>";

$metas = meta_metas($blog_id);
if(isset($metas))
	foreach($metas as $key => $value){
		$key = strtotitle(str_replace("_"," ",$key));
		$blogpost['post'] .= "<option value='$key'>".$key."</option>\n";
}
$blogpost['post'] .= "
<option value='**new**'>-- New Key --</option></select> </td>";
$blogpost['post'] .= "<td><input type=\"text\" name=\"meta_value[]\" style=\"width: 180px\" value=\"\" id=\"metadata_value_new\"/></td><td>".mkButton("table_add","",array("id"=>"metadata_add_button"))."</td>";
$blogpost['post'] .= "</tr>";


$blogpost['post'] .= "</tr>";

$blogpost['post'] .= "</table><br/>";


if($_REQUEST['action'] == "edit"){
  $blogpost['post'] .= "Reason For Edit<span class=\"formreq\">*</span><br/><textarea name=\"editwhy\" style='width: 520px' class=\"required expand\" value=\"".$_REQUEST['editwhy']."\"></textarea><br />";
}




$blogpost['post'] .= "</td></tr></table>";


$blogpost['post'] .= "<center style=\" padding-top: 10px; padding-bottom: 15px; margin:auto;\">";

if($_REQUEST['action'] == "edit"){
	$blogpost['post'] .= "<input type=\"hidden\" name=\"action_edit\" value=\"Submit\" />";
	$blogpost['post'] .= mkButton("disk","Save", array("class"=>"withbox", "onClick"=>"javascript: $('#post_form').submit();"));
}else{
	$blogpost['post'] .= "<input type=\"hidden\" name=\"action_post\" value=\"Submit\" />";
	$blogpost['post'] .= mkButton("disk","Save", array("class"=>"withbox", "onClick"=>"javascript: $('#post_form').submit();"));
}

$blogpost['post'] .= mkButton("page_white_magnify","Preview", array("class"=>"withbox", "onClick"=>"javascript: document.blog.target = 'previewpopup'; tempurl = document.blog.action; document.blog.action = '".render_link('preview.php')."'; previewpopupvariable = window.open('', 'previewpopup', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0, resizable=0,width=600,height=450,left = 50,top = 50'); document.blog.submit(); document.blog.target = '_self'; document.blog.action = '{$blogpost['furl']}'; "));
$blogpost['post'] .= mkButton("delete","Cancel", array("class"=>"withbox",  "onClick"=>"if(!confirm('Are you sure you want to cancel, all changes will be lost.')) return false;", "href"=>"{$blogpost['furl']}"));
$blogpost['post'] .= "</center>";




if($_REQUEST['action'] == "edit"){
$blogpost['data_title'] = "Attached Files"; 
$blogpost['data']  = "<a href=\"javascript:window.open('{$ct_config['blog_path']}upload.php?post_id={$request['bit_id']}&blog_id={$blog['blog_id']}','upload', 'left=400,top=20,width=400,height=300,toolbar=0,resizable=0,location=0,directories=0,scrollbars=0,menubar=0,status=0'); void(0)\" style=\"float:right;\" id=\"link_upload\">upload data</a>";
$blogpost['data']  .= " <a href=\"javascript:window.open('{$ct_config['blog_path']}sketch.php?id={$request['bit_id']}&blog_id={$blog['blog_id']}','upload', 'left=100,top=20,width=700,height=700,toolbar=0,resizable=0,location=0,directories=0,scrollbars=0,menubar=0,status=0'); void(0)\" style=\"float:right;\" id=\"link_sketch\">add sketch</a>";
	
if($metadata['METADATA']['DATA']){
	
	$datas = NULL;
	$datas = split(",",$metadata['METADATA']['DATA']);
	foreach($datas as $bit){
	$data_type = array("type" => "data", "id" => (int)($bit));
	$blogpost['data']  .= "<div style=\"width:100%; height:85px; clear:left\"><div style=\"float:left;\">";
	$blogpost['data']  .= getdata($bit);
	$blogpost['data']  .= "</div>";
	$blogpost['data']  .= "<input type=\"button\" value=\"Add to text\" style=\"width:100px\" onclick=\"dtag('".$data_type['type']."',".$data_type['id'].");\"/></div>";
	}
	
	
	$blogpost['data']  .= "<div style=\"clear:left\"></div>";
}


	

}

$body .= blog_style_post(&$blogpost);

//listblogs
include('page.php');
exit();
}




?>
