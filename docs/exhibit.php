<?php
$cache = readxml($blog['blog_infocache']);




$head .= "
  <link href=\"{$ct_config['blog_path']}{$blog['blog_sname']}/index.ejs\" type=\"application/json\" rel=\"exhibit/data\" />
  <script src=\"http://static.simile.mit.edu/exhibit/api-2.0/exhibit-api.js\" type=\"text/javascript\"></script>
  <script src=\"http://api.simile-widgets.org/exhibit/2.2.0/extensions/calendar/calendar-extension.js\" type=\"text/javascript\"></script>
";
// 
$head .="
	<style>
		div.exhibit-facet-body{height:auto;}
	
		.exhibit-date-picker .has-items{ font-weight:bold; }
		.exhibit-date-picker-text input{ font-size:80%; }
		.exhibit-date-picker-text { text-align: center; }
		.exhibit-thumbnailView-body .exhibit-thumbnailView-itemContainer { float:none; }
	</style>
";

$body .= "\t<div class=\"exhibit info\">\n";
$body .= '<div ex:role="facet" ex:beginDate=".date" ex:endDate=".date" ex:facetClass="DatePickerFacet" ex:collapsible="true" ex:facetLabel="Archive"></div>';
$body .= "<div ex:role=\"facet\" ex:expression=\".section\" ex:facetLabel=\"Section\" ></div>"; 

if(is_array($cache['METADATA']['META']))
foreach($cache['METADATA']['META'] as $key=>$val){
	$body .= "<div ex:role=\"facet\" ex:expression=\".".strtolower($key)."\" ex:facetLabel=\"".ucwords(strtolower($key))."\" ></div>"; 
}

$body .= "<div ex:role=\"facet\" ex:expression=\".author\" ex:facetLabel=\"Author\" ></div>"; 

$body .= "</div>";

$body .= "
<div class=\"exhibit containerPost\">
  <div ex:role=\"view\" ex:directions=\"descending\" ex:orders=\".date\" ex:viewClass=\"Thumbnail\"></div>
";

$body .= '
<div ex:role="lens" ex:itemTypes="Item" style="display: none">
<div class="exhibit containerPost" style="margin:0px;">		
	<div class="postTitle"><a ex:href-content=".uri" ex:content=".label"></a></div>
	<span class="timestamp" ex:content=".date"></span>
	<div  class="postText" ex:content=".html"></div>
	<div class="postInfo" width="100%" style="clear:left;">
		<a ex:href-content=".authoruri" ex:content=".author"></a> | <a ex:href-content=".uri">Comments</a>
	</div>
</div>
</div>
';

$body .= "</div>";


include('page.php');
exit();
?>