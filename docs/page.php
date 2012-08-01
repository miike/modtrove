<?php

header("Cache-Control: no-cache");
header("Pragma: no-cache");

if(!isset($minipage) || !$minipage)
	$loginbox = renlogin_blog();

	if(isset($jquery)){
		if( array_key_exists('function', $jquery) ){
			if(!isset($jquery['code'])){ $jquery['code'] = ''; }
			$jquery['code'] .= "\n\n$(function() {

			   {$jquery['function']}

			 });";
		}
		if(isset($jquery['edit-post'])){
				$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/js/jquery.edit-post.js\"></script>\n";
		}
		
		if(isset($jquery['validate'])){
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/jquery/js/jquery.validate.js\"></script>\n";
		}
		

		if(isset($jquery['fieldselection'])){
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/jquery/js/jquery.fieldselection.js\"></script>\n";
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/blog.fieldselection.js\"></script>\n";
		}
		if(isset($jquery['markitup']) && $ct_config['editor_enabled'] == false){
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/markitup/jquery.markitup.js\"></script>\n";
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/markitup/sets/bbcode/set.js\"></script>\n";
			$jquery['srcs'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$ct_config['blog_path']}inc/markitup/skins/simple/style.css\" />\n";
			$jquery['srcs'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$ct_config['blog_path']}inc/markitup/sets/bbcode/style.css\" />\n";
		}
		
		if ($ct_config['sharelinks']){
			$jquery['srcs'] .= "<script type='text/javascript'>var switchTo5x=true;</script>\n";
			$jquery['srcs'] .= "<script type='text/javascript' src='http://w.sharethis.com/button/buttons.js'></script>\n";
			$jquery['srcs'] .= "<script type='text/javascript'>stLight.options({publisher: 'ur-957945e6-242a-856d-c4a1-fe735a575740'});</script>\n";
		}
		
		if($ct_config['editor_enabled']){
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/js/jquery.tablesorter.min.js\"></script>\n";
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/js/jquery.chemistry.js\"></script>\n";
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tinymce/jscripts/tiny_mce/tiny_mce.js\"></script>\n";
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tinymce/jscripts/tiny_mce/tiny_mce_src.js\"></script>\n";
		$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/tinymce/jscripts/tiny_mce/jquery.tinymce.js\"></script>\n";
		$jquery['srcs'] .= '<script type="text/javascript">
				$(function() {
					$(\'#bbcode\').tinymce({
					script_url : \'' . $ct_config['blog_url'] . 'inc/tinymce/jscripts/tiny_mce/tiny_mce.js\',
					// General options
					theme : "advanced",
					plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,linktopost,tablegrid",

					// Theme options
					theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontsizeselect",
					theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,image,cleanup,code,|,insertdate,inserttime,",
					theme_advanced_buttons3 : "tablegrid,|,row_props,cell_props,|,row_before,row_after,delete_row,|,col_before,col_after,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,fullscreen",
					theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak|,linktopost",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : true,
					style_formats : [
						{title: "Table styles"},
						{title: "Sortable table", selector : "table", classes : "tablesorter"},
			
					],

					// Drop lists for link/image/media/template dialogs
					template_external_list_url : "js/template_list.js",
					external_link_list_url : "js/link_list.js",
					external_image_list_url : "js/image_list.js",
					media_external_list_url : "js/media_list.js",
					
					});
				});
				</script>';
		
		}
		
		if( !array_key_exists('srcs', $jquery) ){
			$jquery['srcs'] = "";
		}
		if( !isset($head) ) { $head = ""; }
		$head .= <<<END
				<script type="text/javascript" src="{$ct_config['blog_path']}inc/jquery/js/jquery-1.7.1.min.js"></script>
				<script type="text/javascript" src="{$ct_config['blog_path']}inc/jquery/js/jquery-ui-1.8.2.custom.min.js"></script>
				<script type="text/javascript" src="{$ct_config['blog_path']}inc/jquery/js/jquery.textarea-expander.js"></script>
				{$jquery['srcs']}
END;
		

			

}
	

	$head .="\n<script type=\"text/javascript\">";
	$head .="\nvar labtrove_path = '{$ct_config['blog_path']}'\n";
	if(isset($jquery['code'])){
		$head .="\n{$jquery['code']}\n";
	}
	$head .="</script>";	
	
if(isset($minipage) && $minipage){
	include("style/{$ct_config['blog_style']}/minipage.php");
}else{
	include("style/{$ct_config['blog_style']}/index.php");
}
?>
