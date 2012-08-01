<?php


$sql = "SELECT UNIX_TIMESTAMP(min(`bit_datestamp`)) as start,  UNIX_TIMESTAMP(max(`bit_datestamp`)) as end FROM `{$ct_config['blog_db']}`.`blog_bits`".$sql_where;

$tresult = runQuery($sql,'Fetch Page Groups');
$posts = mysql_fetch_array($tresult);
	
$tlcolors = array("red","blue","green","gray","deeppink","orange","purple","cyan","goldenrod","light-gray","black");

$head .= "
<script>
	Timeline_ajax_url='{$ct_config['blog_path']}inc/timeline/timeline_ajax/simile-ajax-api.js';
 	Timeline_urlPrefix='{$ct_config['blog_path']}inc/timeline/timeline_js/';       
	Timeline_parameters='bundle=true';
</script>
<script src=\"{$ct_config['blog_path']}inc/timeline/timeline_js/timeline-api.js\" type=\"text/javascript\"></script>";
$bodytag .= " onload=\"onLoad();\" onresize=\"onResize();\"";

$body = "
<div class=\"containerPost\" style=\"margin:10px; \">
<div class=\"dialog\"><h2>Timeline</h2>
	<div id=\"my-timeline\" style=\"height: 550px;font-size: 110%;\" ></div>
</div>
<noscript>
This page uses Javascript to show you a Timeline. Please enable Javascript in your browser to see the full page. Thank you.
</noscript>
";


$body .= "
<script type=\"text/javascript\">
 var tl;
 function onLoad() {
   var eventSource = new Timeline.DefaultEventSource();
   var bandInfos = [
     Timeline.createBandInfo({
     	 eventSource:    eventSource,
   	  	 date:           \"".date('M d Y H:i:s \G\M\TO',$posts['end'])."\",
         width:          \"80%\", 
         intervalUnit:   Timeline.DateTime.DAY, 
         intervalPixels: 80
     }),
     Timeline.createBandInfo({
         eventSource:    eventSource,
		 overview:       true,
		 width:          \"15%\", 
         intervalUnit:   Timeline.DateTime.MONTH, 
         intervalPixels: 150
     }),
   		Timeline.createBandInfo({
         eventSource:    eventSource,
		 overview:       true,
		 width:          \"5%\", 
         intervalUnit:   Timeline.DateTime.YEAR, 
         intervalPixels: 600
     })
   ];
   bandInfos[1].syncWith = 0;
   bandInfos[1].highlight = true;

   bandInfos[2].syncWith = 0;
   bandInfos[2].highlight = true;

	 bandInfos[0].decorators = [
                new Timeline.SpanHighlightDecorator({
                    startDate:  \"".date('M d Y H:i:s \G\M\TO',$posts['start'])."\",
                    endDate:    \"".date('M d Y H:i:s \G\M\TO',$posts['end'])."\",
                    startLabel: \"first post\",
                    endLabel:   \"last post\",
                    opacity:    15
                })
            ];
 	bandInfos[1].decorators = [
                new Timeline.SpanHighlightDecorator({
                    startDate:  \"".date('M d Y H:i:s \G\M\TO',$posts['start'])."\",
                    endDate:    \"".date('M d Y H:i:s \G\M\TO',$posts['end'])."\",
                    startLabel: \"first post\",
                    endLabel:   \"last post\",
                    opacity:    15
                })
            ];


   tl = Timeline.create(document.getElementById(\"my-timeline\"), bandInfos);
	
   tl.loadXML(\"{$ct_config['blog_path']}{$blog['blog_sname']}/index.tlml\", function(xml, url) { eventSource.loadXML(xml, url); });
   
 }

 var resizeTimerID = null;
 function onResize() {
     if (resizeTimerID == null) {
         resizeTimerID = window.setTimeout(function() {
             resizeTimerID = null;
             tl.layout();
         }, 500);
     }
 }

 
</script>";

$body .= "<div class=\"dialog\" style=\"width:48%; float:left;\">
	<h2>Section Key:</h2><ul>
";
$cache = readxml($blog['blog_infocache']);
$i=0;
foreach($cache['METADATA']['SECTIONS'] as $val){
	$body.= "<li style=\"list-style-image:url('{$ct_config['blog_path']}inc/timeline/imgs/{$tlcolors[$i]}-circle.png'); font-size:14px; padding:2px;\">";
	$body.= "{$val['NAME']} <span class=\"num_posts\">(".$val['COUNT'].")</span></li>\n";
	$i = ($i+1) % count($tlcolors);
}


$body .= "</ul></div>";

$body .= "<div class=\"dialog\" style=\"width:48%; float:right;\">
	<h2>Jump to Date:</h2>
";
$i=0;
foreach($cache['METADATA']['ARCHIVES'] as $key=>$val){
	 $mtime = substr($key,1);
	$body.= "<a href=\"#\" onclick=\"tl.getBand(0).setCenterVisibleDate(Timeline.DateTime.parseGregorianDateTime('".date('M d Y H:i:s \G\M\TO',$mtime)."'));return false;\">".date('F Y',$mtime)."</a> <span class=\"num_posts\">(".$val.")</span>,\n";
	$i++;
}


$body .= "</div>";

$body .= "<div class=\"clear\"  style=\"text-align:center; padding-top:10px;\"><small>Using <a href=\"http://www.simile-widgets.org/timeline/\">Timeline</a> from the SIMILE project.</small>
</div>";

$body .= "</div>";


include('page.php');
exit();
?>