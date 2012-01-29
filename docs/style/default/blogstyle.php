<?php
function blog_style_post($blogpost)
{
	$ret = "<div class=\"containerPost\">\n";
	if(isset($blogpost['infohead']) && $blogpost['infohead'])
	{
		$ret .= "\t\t\t	<div style=\"clear:left;\">";
		$ret .= "<div class=\"infoBox\">{$blogpost['infohead']}</div></div>\n";
	}

	if(is_set_not_empty('url', $blogpost))
	{
		$ret .= "\t\t\t<div class=\"postTitle\"><a href=\"{$blogpost['url']}\">{$blogpost['title']}</a></div>\n";
	}
	else
	{
		$ret .= "\t\t\t<div class=\"postTitle\">{$blogpost['title']}</div>\n";
	}

	$date = (isset($blogpost['date'])) ? $blogpost['date'] : '';
	$ret .= "\t\t\t<div class=\"timestamp\">{$date}</div>\n";
	if($blogpost['post'])
	{
		$ret .= "\t\t\t	<div class=\"postText\">{$blogpost['post']}</div>\n";
	}

	if(isset($blogpost['data']) && $blogpost['data'])
	{
		$ret .= "\t\t\t	<div style=\"clear:left;\">";
		if($blogpost['data_title'])
		{
			$ret .= "<span class=\"dataTitle\" >{$blogpost['data_title']}</span>";
		}
		$ret .= "<div class=\"dataBox\">{$blogpost['data']}</div></div>\n";
	}

	if(is_set_not_empty('footer', $blogpost))
	{
		$ret .= "\t<div class=\"postInfo\" width=\"100%\" style=\"clear:left;\">{$blogpost['footer']}</div>";
	}
	$ret .= "</div>";

	return $ret;
}

function blog_style_comment($comment)
{
	return <<<END
		<div class=\"containerComment\"><div><b><a name=\"{$comment['com_id']}\" href=\"{$comment['com_url']}\">{$comment['com_title']}</a></b>
			by {$comment['com_user']} </div>
		<div class=\"timestampComment\">{$comment['com_rdate']}</div>
			{$comment['com_html']}</div>;
END;
}


function blog_style_error($errmsg, $sev = "error", $id = 0)
{
	return <<<END
		<div class="msg {$sev}" id="msg_$id"><a href="#" class="msg_close" onclick="$('#msg_$id').fadeOut('slow'); return false;">x</a>
		$errmsg</div>
END;
}
?>
