<?php

include("../lib/default_config.php");

$minipage = 1;

$blog = db_get_blog_by_id($_REQUEST['blog_id']);



if($blog){
	$blog_id = $blog['blog_id'];
	
checkblogconfig($blog_id);



if($_REQUEST['utypesub']){
	$postid = (int)$_REQUEST['post'];
if($_REQUEST['utype']=='single'){
	$filename = 'unset';
	$filename_alias = 'unset';
	if($_REQUEST['hasurl'] && parse_url($_REQUEST['fileurl']))
	{
		$filename = basename($_REQUEST['fileurl']);
		$filename_alias = $filename;
		$ok = 1;
	}
	elseif(is_uploaded_file($_FILES['imagefile']['tmp_name']))
	{
		$filename = $_FILES['imagefile']['tmp_name'];
		$filename_alias = $_FILES['imagefile']['name'];
		$ok =1;
	}

	if($ok)
	{
		$ext = pathinfo($filename_alias, PATHINFO_EXTENSION);
		$newid = add_data_by_filename($ext, $filename);

		$main_data = array("$ext"=>array("type"=>"local", "id"=>$newid, "name"=>$filename_alias));
		$newid = add_data_meta($_REQUEST['title'], $main_data, NULL,$postid);

		$sql =	"SELECT  * FROM  `{$ct_config['blog_db']}`.`blog_bits` ";
		$sql .= "WHERE `bit_id` = ".$postid." AND `bit_edit` = 0";

		$tresult = runQuery($sql,'Blogs');

		$row = mysql_fetch_array($tresult);

		$metadata = readxml($row['bit_meta']);

		if(isset($metadata['METADATA']['DATA'])){
		$metadata['METADATA']['DATA'] .= ",".$newid;
		}else{
		$metadata['METADATA']['DATA'] = $newid;
		}

		$meta = null;
		$metad = writexml($metadata);

		$new_id = edit_blog($postid, 'Added Data', NULL, NULL, $metad, NULL);
		$error="none";
	}
	else
	{
		$error = "Error";
	}

}else if($_REQUEST['utype']=='zip'){
		if (is_uploaded_file($_FILES['imagefile']['tmp_name'])) {
			$_FILES['imagefile']['tmp_name'];
			$tmpdir = $_FILES['imagefile']['tmp_name']."_ex";
			@mkdir($tmpdir);
			`unzip {$_FILES['imagefile']['tmp_name']} -d {$tmpdir}`;

			$files = array();
			exec("find $tmpdir", $files);
			foreach($files as $file){
				$filename = basename($file);
				if($filename{0} == ".") continue;
				if($filename{0} == "$") continue;
				if($filename{0} == ":") continue;
				if(strtolower($filename) == "thumbs.db") continue;
				if(stristr($file,"/__")) continue;
				if(is_dir($file)) continue;

				if($_REQUEST['title']) $title = "{$_REQUEST['title']}: $filename"; else $title = $filename;

				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$newid = add_data_by_filename($ext, $file);
				$main_data = array("$ext"=>array("type"=>"local", "id"=>$newid, "name"=>$filename));
				$newid = add_data_meta($title, $main_data);
				$newids[] = $newid;
			}

			`rm -rf $tmpdir`;

			$sql =	"SELECT  * FROM  `{$ct_config['blog_db']}`.`blog_bits` ";
				$sql .= "WHERE `bit_id` = ".$postid." AND `bit_edit` = 0";

				$tresult = runQuery($sql,'Blogs');

				$row = mysql_fetch_array($tresult);

				$metadata = readxml($row['bit_meta']);

				if(isset($metadata['METADATA']['DATA'])){
				$metadata['METADATA']['DATA'] .= ",".join(",",$newids);
				}else{
				$metadata['METADATA']['DATA'] = join(",",$newids);
				}

				$meta = null;
				$metad = writexml($metadata);

				$new_id = edit_blog($postid, 'Added Data', NULL, NULL, $metad, NULL);
				$error="none";
		}
}

}

	}else{
		$error = "Error";
	}


if($error=="none"){
	$bodytag = "onload=\"window.opener.post_form.elements['itemloop'].value = 1;window.opener.post_form.submit();self.close();\"";
}

$body = "<h1>File Upload</h1>";
$max_file_size = 134217728;
if(isset($ct_config['uploads_max_size'])) { $max_file_size = $ct_config['uploads_max_size']; }

$body .= "<form enctype=multipart/form-data method=post action=\"upload.php?blog_id=".(int)$_REQUEST['blog_id']."\" >
<input type=hidden name=MAX_FILE_SIZE value='$max_file_size'>";



$body .= "<table border=1 width=380>";
$body .= "<tr><td>Post: </td><td>".(int)$_REQUEST['post_id']."<input type=hidden name=post value=\"".(int)$_REQUEST['post_id']."\"></td></tr>";
$body .= "<tr><td>Title: </td><td><input type=text name=title value=\"".stripslashes($_REQUEST['title'])."\"></td></tr>";
$body .= "<tr id=\"filerow\"><td>Add Item:</td><td> <input type=file name=imagefile> 
	(<a href=\"#\" onclick=\"$('#hasurl').val('1'); $('#urlrow').show(); $('#filerow').hide(); return false;\">url</a>) </td></tr>";
$body .= "<tr id=\"urlrow\"><td>Item URL:</td><td> <input type=\"text\" name=\"fileurl\" style=\"width: 220px;\" />
  	(<a href=\"#\" onclick=\"$('#hasurl').val('0'); $('#urlrow').hide(); $('#filerow').show(); return false;\">file</a>)
	<input type=\"hidden\" name=\"hasurl\" id=\"hasurl\" value=\"0\"/>
	</td></tr>";

	$body .= "<tr><td>Type: </td><td><select name=stype>	
				<option value=''>Auto Detect</option>
				<option value=jpg>Image (jpg)</option>
				<option value=png>Image (png)</option>
				<option value=gif>Image (gif)</option>
				<option value=html>Webpage (html)</option>
				<option value=m>Matlab Code (m)</option>
				<option value=asc>Asc Text File (asc)</option>
				<option value=text>Plain Text File (txt)</option>
				<option value=pdf>Adobe PDF (pdf)</option>
			</select></td></tr>";


$body .= "<tr><td>Upload Type:</td><td>";
$body .= "<select name=utype><option value=single selected>Single Item, eg Image, Document etc</option>
<option value=zip>Zip File (no directories)</option></select></td></tr>";
$body .= "<tr></td><td><td align=right><input type=submit name=utypesub></td></tr>";
$body .= "</form>";
$body .= "</table>";



$jquery['function'] = "$('#urlrow').hide();\n";

$minipage = 1;

include('page.php');
?>
