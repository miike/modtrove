<?php
function code_render_inline($src,$lang){
	require_once 'geshi/geshi.php';
	$geshi =& new GeSHi(stripslashes($src),$lang);
	   $geshi->set_overall_style('color: #000066; border: 1px solid #d0d0d0; background-color: #f0f0f0; padding:5px;', true);	

		$geshi->set_header_content("$lang code:");
    $geshi->set_header_content_style('font-family: Verdana, Arial, sans-serif; color: #808080; font-size: 70%; font-weight: bold; border-bottom: 1px solid #d0d0d0; padding: 0px;');

return $geshi->parse_code();
	}



function code_render_datapre($src,$lang){
	require_once 'geshi/geshi.php';
	$geshi =& new GeSHi(stripslashes($src),$lang);
    $geshi->set_header_type(GESHI_HEADER_DIV);
	 	$geshi->set_overall_style('color: #000066; border: 1px solid #d0d0d0; background-color: #f0f0f0; padding:0px; width:640px;', true);	
		$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 999999999999);
return $geshi->parse_code();
	}





$ct_config['geshi']['lang_map']['DATA_PL'] = "perl";
$ct_config['geshi']['lang_map']['DATA_PHP'] = "php";
$ct_config['geshi']['lang_map']['DATA_ASP'] = "asp";
$ct_config['geshi']['lang_map']['DATA_SH'] = "bash";
$ct_config['geshi']['lang_map']['DATA_C'] = "c";
$ct_config['geshi']['lang_map']['DATA_CPP'] = "cpp";
$ct_config['geshi']['lang_map']['DATA_CSS'] = "css";
$ct_config['geshi']['lang_map']['DATA_M'] = "matlab";
$ct_config['geshi']['lang_map']['DATA_SQL'] = "sql";
$ct_config['geshi']['lang_map']['DATA_RB'] = "ruby";
$ct_config['geshi']['lang_map']['DATA_TXT'] = "text";
$ct_config['geshi']['lang_map']['DATA_XML'] = "xml";
$ct_config['geshi']['lang_map']['DATA_RDF'] = "xml";
$ct_config['geshi']['lang_map']['DATA_JAVA'] = "java";



?>
