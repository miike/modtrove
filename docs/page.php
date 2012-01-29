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
		if(isset($jquery['markitup'])){
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/markitup/jquery.markitup.js\"></script>\n";
			$jquery['srcs'] .= "<script type=\"text/javascript\" src=\"{$ct_config['blog_path']}inc/markitup/sets/bbcode/set.js\"></script>\n";
			$jquery['srcs'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$ct_config['blog_path']}inc/markitup/skins/simple/style.css\" />\n";
			$jquery['srcs'] .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$ct_config['blog_path']}inc/markitup/sets/bbcode/style.css\" />\n";
		}
		
		if( !array_key_exists('srcs', $jquery) ){
			$jquery['srcs'] = "";
		}
		if( !isset($head) ) { $head = ""; }
		$head .= <<<END
				<script type="text/javascript" src="{$ct_config['blog_path']}inc/jquery/js/jquery-1.4.2.min.js"></script>
				<script type="text/javascript" src="{$ct_config['blog_path']}inc/jquery/js/jquery-ui-1.8.2.custom.min.js"></script>
				<script type="text/javascript" src="{$ct_config['blog_path']}inc/jquery/js/jquery.textarea-expander.js"></script>
				{$jquery['srcs']}
END;

			

}
	

	$head .="<script type=\"text/javascript\">";
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
