g<?php
if($_SESSION['user_uid'])
	$uid_bit = "/uid/{$_SESSION['user_uid']}";

if(!$title_url){
	$title_url = substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],'.php')+4);
}


//body .= var_export($_COOKIE, true);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="padding-top: 20px;">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?php echo strip_tags($title)?></title>
<base href="<?php echo $ct_config['blog_url'];?>"/>
<link rel="stylesheet" type="text/css" href="/style/style.css"/>
<link rel="stylesheet" type="text/css" href="/style/<?php echo $ct_config['blog_style']?>/style.css"/>
<?php if(isset($rss_feed))
	foreach($rss_feed as $feed){ 
	echo "<link rel=\"alternate\" href=\"{$feed['url']}\" type=\"{$feed['type']}\" lang=\"en\" title=\"{$feed['title']}\"/>\n";
}
 
if(isset($ct_config['extra_meta']))
foreach($ct_config['extra_meta'] as $key=>$var){
	echo "<meta name=\"$key\" content=\"$var\" />\n";
}
?>

<?php echo $head?>
</head>
<?php if($minipage){
echo '<body class="body_pop" '.$bodytag.'>';
echo '<script language="Javascript" src="inc/bbcode.js" type="text/javascript"></script>';
echo $body;
}else{
?>
<body class="body_main" <?php echo $bodytag?>>

<script language="Javascript" src="inc/bbcode.js" type="text/javascript"></script>

<div class="top_bit">
<span style="float:right;">
<?php if($_SESSION['user_admin']>2){ echo "<a href=\"".render_link('admin.php')."\">Admin</a> "; }?>

<a href="<?php echo $ct_config['blog_path'];?>">Dashboard</a> | <a href="http://chemtools.chem.soton.ac.uk/wiki/index.php/Blog_Manual" title="The Help Guide to the blog">Help</a>
</span><?php echo renlogin_blog()?></div>

<div class="header">
<h1 onclick="javascript:location.href='<?php echo $ct_config['blog_path'];?>'"><?php echo $ct_config['blog_title'];?></h1>
<h2 class="headerCenter"><a href="<?php echo $title_url?>" id="white"><?php echo $title?></a><br/><span class="description"><?php echo $desc?></span></div></h2>

<?php echo $body?>

<br/><br/>
<div class="footer">
Powered by <a href="http://labtrove.org">labtrove 2.2</a> &copy; University of Southampton
</div>
<?php } 

if($ct_config['devo']){
		$ct_config['devstr']['time'] = microtime(true) - $ct_config['devstr']['stime'];
		echo "<div class=\"containerPost\">
				<div class=\"postTitle\">Dev Info</div><div class=\"postText\">";
		echo "<b>Run Time:</b> ".number_format($ct_config['devstr']['time'],3)." <br/>";
		
		echo "<b>Memory:</b> ".number_format(memory_get_peak_usage()/1024,1)."kB <br/>";
		echo "<br/>";
		echo "<b>No of SQL</b> ".count($ct_config['devstr']['sql'])." <br/>";
		echo "<table border=1><tr><th>Sql</th><th>time</th></tr>";
		foreach($ct_config['devstr']['sql'] as $val){
		echo "<tr><td>{$val['sql']}</td><td>".number_format($val['time'],4)."</td></tr>";
			$totsqltime +=$val['time'];
		}
		echo "<tr><th>Total</th><th>".number_format($totsqltime,4)."</th></tr>";
		echo "</table>";
		echo "</div>";
echo "</div>";
}

?>
</body>
</html>
