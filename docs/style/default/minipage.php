<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo strip_tags($title)?></title>
	<base href="<?php echo $ct_config['blog_url'];?>"/>
	<?php echo $head?>
	<link rel="stylesheet" type="text/css" href="<?php echo $ct_config['blog_path']; ?>style/style.css"/>
	<link rel="stylesheet" type="text/css" href="<?php echo $ct_config['blog_path']; ?>style/default/style.css"/>
	<?php if($ct_config['blog_style']!="default"){ ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $ct_config['blog_path']; ?>style/<?php echo $ct_config['blog_style']?>/style.css"/>
	<?php } ?>
</head>
<body class="body_pop" <?php echo $bodytag?> >

	<div id="page">		
			<?php echo $body?>
			<div class="clear"></div>
	</div>
	<div id="footer">
        Powered by <a href="http://labtrove.org">labtrove 2.2</a> &copy; University of Southampton
	</div>
</div>
</body>
</html>
