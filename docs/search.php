<?php

include("../lib/default_config.php");

if( $ct_config['use_mysql_fulltext_search'] == 2 )
{
  include("../lib/porter_stemmer.php");
}

$pathinfo = $_REQUEST['uri'];

if($pathinfo)
{
	$pathinfo = explode("/",$pathinfo);
	$request['blog_sname'] = array_shift($pathinfo);
	while($request[array_shift($pathinfo)] = addslashes(array_shift($pathinfo)));
}
$_REQUEST['uri'] = "search/".$_REQUEST['uri'];

///Load Blog info
if($request['blog_sname'])
{
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_blogs` WHERE `blog_sname` = '{$request['blog_sname']}'";
	$result = runQuery($sql,'Blogs');
	$blog = mysql_fetch_array($result);
	$blog_id = $blog['blog_id'];
	$title = $blog['blog_name'];
	$desc = $blog['blog_desc'];
	$title_url = render_link($blog['blog_sname']);

	checkblogconfig($blog_id);
}
if(!$blog_id && $request['blog_sname'])
{
	set_http_error(404, $_REQUEST['uri']);
	exit();
}
if(!$blog_id)
{
	$title = $ct_config['blog_title'];
	$desc = $ct_config['blog_desc'];
	$title_url = $ct_config['blog_path'];
	$_REQUEST['sall']=1;
}

include("style/{$ct_config['blog_style']}/blogstyle.php");

$_SESSION['blog_id'] = $blog_id;

if($_REQUEST['save'] && ($_SESSION['user_name'] == $request['user']))
{
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_users` WHERE `u_name` = '{$_SESSION['user_name']}'";
	$result = runQuery($sql,'Blogs');

	if($_REQUEST['proflocate'])
	{
		$_REQUEST['proflocate'] = 1;
	}
	if(mysql_num_rows($result))
	{
		$sql = "UPDATE  `{$ct_config['blog_db']}`.`blog_users` SET  `u_emailsub` =  '".(int)$_REQUEST['emailset']."', `u_sortsub` =  '".(int)$_REQUEST['emailsort']."', `u_proflocate` =  '".(int)$_REQUEST['proflocate']."' WHERE `blog_users`.`u_name` =  '{$_SESSION['user_name']}' LIMIT 1 ;";
	}
	else
	{
		$sql = "INSERT INTO  `{$ct_config['blog_db']}`.`blog_users` ( `u_name` , `u_emailsub` , `u_sortsub` , `u_proflocate` ) VALUES ( '{$_SESSION['user_name']}',  '".(int)$_REQUEST['emailset']."',  '".(int)$_REQUEST['emailsort']."',  '".(int)$_REQUEST['proflocate']."');";
	}

	runQuery($sql,'Blogs');

	$sql = "DELETE FROM  `{$ct_config['blog_db']}`.`blog_sub` WHERE  `blog_sub`.`sub_username` =  '{$_SESSION['user_name']}'";
	runQuery($sql,'Blogs');

	if(isset($_REQUEST['blogs_sub']))
	{
		foreach($_REQUEST['blogs_sub'] as $key => $value)
		{
			$sql = "INSERT INTO  `{$ct_config['blog_db']}`.`blog_sub` ( `sub_username` , `sub_blog` ) VALUES ( '{$_SESSION['user_name']}',  '".(int)$key."' );";
			runQuery($sql,'Blogs');
		}
	}
}

$body = "";

if(!checkzone($ct_config['blog_zone']) )
{
	header("Location: {$ct_config['blog_path']}projects/blog/index.php?msg=Forbidden!");
	exit();
}

if($_REQUEST['msg'])
{
	$body .= "<div class=\"containerPost\" ><div class=\"postTitle\" style=\"color:red;\">Error: {$_REQUEST['msg']} </div></div>";
}
$body .= "\t<div class=\"containerPost\">\n";
$body .= "\t\t<div class=\"postTitle\">Search</div>\n";
$body .= "<form method=\"POST\" action=\"".$_REQUEST['uri']."\">";
$body .= "<div class=\"postText\"><center> Search: <input type=\"text\" name=\"q\" class=\"searchBox\" value=\"{$_REQUEST['q']}\"> <input type=submit name=search value=\"Go Search\"><br />";

if(!$blog_id)
{
	$body .= "Searching all of {$ct_config['blog_title']} blogs";
}
else
{
	if(!$_REQUEST['sall'])
	{
		$body .= "Search only this blog <input type=radio name=sall value=0 checked> or all {$ct_config['blog_title']} blogs <input type=radio name=sall value=1>";
	}
	else
	{
		$body .= "Search only this blog <input type=radio name=sall value=0> or all {$ct_config['blog_title']} blogs <input type=radio name=sall value=1 checked>";
	}
}
$body .= "</center></div>";
$body .= "</form>";
$body .="</div>\n";

if($_REQUEST['q'])
{
	// Build SQL template variables

	$result_index = 0;
	$result_fragments[$result_index] = "";
	$result_scores[$result_index] = 0;

	$search_matcher = "`bit_title` LIKE  '%{$_REQUEST['q']}%' OR `bit_content` LIKE  '%{$_REQUEST['q']}%'";
	$search_matcher_as_score = '';
	$search_order_by = " ORDER BY  `bit_datestamp` DESC ";

	if( $ct_config['use_mysql_fulltext_search'] == 1 )
	{
		$search_matcher = "MATCH (bit_content, bit_title) AGAINST ('" . $_REQUEST['q'] . "' IN BOOLEAN MODE)";
		$search_matcher_as_score = ", " . $search_matcher . " AS score";
		$search_order_by = " ORDER BY score DESC";
	}
	elseif( $ct_config['use_mysql_fulltext_search'] == 2 )
	{
		$search_matcher = "MATCH (bit_content, bit_title) AGAINST ('" . porter_stemmer_prime_search($_REQUEST['q']) . "' IN BOOLEAN MODE)";
		$search_matcher_as_score = ", " . $search_matcher . " AS score";
		$search_order_by = " ORDER BY score DESC";
	}

	// Search Posts

	$sql = <<<SQL
SELECT `bit_id` , `bit_rid` , `bit_user` , `bit_title`, UNIX_TIMESTAMP(`bit_datestamp`) as datetime, `bit_blog`, `blog_name`, `bit_user`, `blog_zone` $search_matcher_as_score
FROM `{$ct_config['blog_db']}`.`blog_bits` INNER JOIN `{$ct_config['blog_db']}`.`blog_blogs` ON `blog_bits`.`bit_blog` = `blog_blogs`.`blog_id`
WHERE `bit_edit` = 0 AND ({$search_matcher})
SQL;

	if($_REQUEST['sall']!=1)
	{
		$sql .= " AND `blog_blogs`.`blog_id` = $blog_id";
	}
	$sql .= $search_order_by;

	$result = runQuery($sql,'Blogs');

	if(mysql_num_rows($result))
	{
		while($rowb = mysql_fetch_array($result))
		{
			if(checkzone($rowb['blog_zone'],1,$rowb['bit_blog'])!=0)
			{
				$fragment = "\t\t\t<li style=\"margin-top:4px;\">".render_blog_link($rowb['bit_id'])." by ".get_user_info($rowb['bit_user'],"name");
				if($_REQUEST['sall'])
				{
					$fragment .= " from ".$rowb['blog_name'];
				}
				$fragment .= "<br />\t\t\t<span class=\"timestampComment\">".date("jS F Y @ H:i",$rowb['datetime'])."</span>\n";
				$result_fragments[$result_index] = $fragment;
				$result_scores[$result_index] = isset($rowb['score']) ? $rowb['score'] : 0;
				$result_index++;
			}
		}

	}

	// Search Comments

	$sql = <<<SQL
SELECT `blog_name`,`blog_id`, `blog_com`.`com_id` as uid,  `blog_bits`.`bit_id` ,  `blog_com`.`com_user` AS  `bit_user` ,  `blog_com`.`com_title` AS  `bit_title` ,  `blog_com`.`com_cont` AS  `bit_content` , UNIX_TIMESTAMP(  `blog_com`.`com_datetime` ) AS datetime , 'comment' AS `btype` , `blog_com`.`com_edit`, `blog_blogs`.`blog_zone` $search_matcher_as_score
FROM `{$ct_config['blog_db']}`.`blog_bits` INNER JOIN  `{$ct_config['blog_db']}`.`blog_com` ON  `blog_bits`.`bit_id` =  `blog_com`.`com_bit` INNER JOIN  `{$ct_config['blog_db']}`.`blog_blogs` ON  `blog_bits`.`bit_blog` =  `blog_blogs`.`blog_id`
WHERE `blog_com`.`com_edit` = 0 AND  `bit_edit` = 0 AND ({$search_matcher})
SQL;

	if($_REQUEST['sall']!=1)
	{
		$sql .= " AND `blog_blogs`.`blog_id` = $blog_id";
	}
	$sql .= $search_order_by;

	$result = runQuery($sql,'Blogs');

	if(mysql_num_rows($result))
	{
		while($rowb = mysql_fetch_array($result))
		{
			if(checkzone($rowb['blog_zone'],1,$rowb['blog_id'])!=0)
			{
				$fragment = "\t\t\t<i><li style=\"margin-top:4px;\"><a href=\"".render_blog_link($rowb['bit_id'],1)."#{$rowb['uid']}\">".$rowb['bit_title']."</a> by ".get_user_info($rowb['bit_user'],"name");
				if($_REQUEST['sall'])
				{
					$fragment .= " from ".$rowb['blog_name'];
				}
				$fragment .= "</i><br />\t\t\t<span class=\"timestampComment\">".date("jS F Y @ H:i",$rowb['datetime'])."</span>\n";
				$result_fragments[$result_index] = $fragment;
				$result_scores[$result_index] = isset($rowb['score']) ? $rowb['score'] : 0;
				$result_index++;
			}
		}

	}

	// Render results
	$body .= "\t<div class=\"containerPost\">\n";
	$body .= "\t\t<div class=\"postTitle\">Results - Blog and Comment Posts</div>\n";
	$body .= "<div class=\"postText\">";

	if($result_fragments[0])
	{
		arsort($result_scores);
		foreach ($result_scores as $index => $score)
		{
			$body .= $result_fragments[$index];
			// $body .= "[" . $score . "]";
		}
	}
	else
	{
		$body .= "<i>No results found</i>\n";
	}

	$body .= "</div>";
	$body .="</div>\n";
}
include('page.php');
?>
