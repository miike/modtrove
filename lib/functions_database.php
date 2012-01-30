<?php
include("../config.php");

/* database functions, currently MySQL flavoured */

$database_debug_level = 0;
$ct_config['db_link'] = mysql_pconnect($ct_config['blog_host'],$ct_config['blog_user'],$ct_config['blog_pass']);
mysql_select_db($ct_config['blog_db']) or die("Could not select database");


/* internal interface to MySQL, to connect and run SQL */
/* $oneliner is will return mysql $result not the array of values */
function _db_call($sql, $oneliner = true)
{
  global $ct_config, $database_debug_level;

/*
  $link = mysql_pconnect($ct_config['blog_host'], $ct_config['blog_user'], $ct_config['blog_pass']);

  if(!$link)
  {
    error_log("Database Connection Error: failed to connect ({$ct_config['blog_host']}, {$ct_config['blog_user']}, {$ct_config['blog_pass']})");
    return false;
  }

  $select_ok = mysql_select_db($ct_config['blog_db']);

  if(!$select_ok)
  {
    error_log("Database Connection Error : failed to select database ({$ct_config['blog_db']})");
    return false;
  }
*/
  $result = mysql_query($sql);

  if(!$result)
  {
    error_log("Database Connection Error : failed to execute query ({$sql})");
    error_log(mysql_error());
    return false;
  }

  if($database_debug_level > 0)
  {
    /* log the query and where in the client code this was called from */
    $bt = debug_backtrace(false);
    $n = 1;
    if(!$bt[1]) { $n = 0; }
    error_log("DATABASE CALL '{$bt[$n]['file']}:{$bt[$n]['line']}' : '{$sql}'");
  }

  if(is_bool($result) || !$oneliner)
  {
    return $result;
  }
  else
  {
    return mysql_fetch_array($result, MYSQL_ASSOC);
  }
}

function db_get_blog_by_id($blog_id)
{
  global $ct_config;
  $blog_id = (int)$blog_id;
  return _db_call("SELECT * FROM  `{$ct_config['blog_db']}`.`blog_blogs` WHERE `blog_id` = '{$blog_id}'");
}

function db_get_blog_link_info($bit_id)
{
  global $ct_config;
  // return _db_call("SELECT `blog_bits`.*, `blog_blogs`.`blog_sname` FROM `{$ct_config['blog_db']}`.`blog_bits` INNER JOIN `{$ct_config['blog_db']}`.`blog_blogs` ON `blog_bits`.`bit_blog` = `blog_blogs`.`blog_id` WHERE `bit_id` = $bit_id AND `bit_edit` = 0");
  return _db_call("SELECT blog_bits.bit_title, blog_blogs.blog_sname FROM `{$ct_config['blog_db']}`.blog_bits INNER JOIN `{$ct_config['blog_db']}`.blog_blogs ON blog_bits.bit_blog = blog_blogs.blog_id WHERE bit_id = $bit_id AND bit_edit = 0");
}

function db_get_post_by_id($post_id)
{
  global $ct_config;

  $post_id = (int)$post_id;
  $sql = "SELECT  `bit_id`,  `bit_user` ,  `bit_title` ,  `bit_content` ,  `bit_meta` ,  `bit_datestamp` ,  `bit_timestamp` ,  `bit_group` ,  `bit_blog` ,  `bit_edit` ,  `bit_editwhy` , UNIX_TIMESTAMP(  `bit_datestamp` ) AS datetime ,  UNIX_TIMESTAMP(  `bit_timestamp` ) AS timestamp
    FROM  `{$ct_config['blog_db']}`.`blog_bits`
    WHERE `bit_id` = '{$post_id}' AND `bit_edit` = 0 Limit 1";

  return _db_call($sql);
}


function db_add_data_to_database_by_value($data_type, $size, &$data)
{
  global $ct_config;

  if($ct_config['uploads_db_update'])
  {
    $sql = sprintf("INSERT INTO `%s`.`blog_data` (`data_id`, `data_datetime`, `data_type` , `data_data`, filesize, mode) VALUES ('', NOW(),  '%s',  '%s', '%s', 'database')",
      $ct_config['blog_db'],
      mysql_real_escape_string($data_type),
      mysql_real_escape_string($data),
      mysql_real_escape_string($size));
  }
  else // legacy mode
  {
    $sql = sprintf("INSERT INTO `%s`.`blog_data` (`data_id`, `data_datetime`, `data_type` , `data_data`) VALUES ('', NOW(),  '%s',  '%s' )",
      $ct_config['blog_db'],
      mysql_real_escape_string($data_type),
      mysql_real_escape_string($data));
  }

  _db_call($sql);

  return mysql_insert_id();
}


function db_get_data_by_id($data_id){
	global $ct_config;
	  $data_id = (int)$data_id;
	  return _db_call("SELECT * FROM `{$ct_config['blog_db']}`.`blog_data` WHERE `data_id` = '".$data_id."'");
}

function db_add_data_to_database_by_reference($data_type, $size, $checksum, $filepath)
{
  global $ct_config;

  $sql = sprintf("INSERT INTO `%s`.`blog_data` (data_id, data_datetime, data_type ,filepath, filesize, checksum, mode) VALUES ('', NOW(), '%s',  '%s', '%s', '%s', 'filesystem')",
    $ct_config['blog_db'],
    mysql_real_escape_string($data_type),
    mysql_real_escape_string($filepath),
    mysql_real_escape_string($size),
    mysql_real_escape_string($checksum));
  _db_call($sql);

  return mysql_insert_id();
}


function runQuery($sql, $query_desc="")
{
  // Connecting, selecting database
  global $ct_config;

  if($ct_config['devo']) // database debugging, enable general_log_file in /etc/mysql/my.cnf and the annotated SQL will be logged by MySQL
  {
    $bt = debug_backtrace(false);
    $sql .= " -- CALLED FROM '{$bt[0]['file']}:{$bt[0]['line']}'";
  }

  $uri = $_SERVER['SERVER_NAME'];
  if(! $ct_config['db_link'])
  {
    echo "DB Connection Error!";
    exit(); // dangerous as exits entire php stack
  }

  if($ct_config['devo'])
  {
    $time_start = microtime(true);
  }

  if(!$ct_config['db_link'])
  {
    return false;
  }
  // else $ret .=  "An error occurred while attempting to connect to the database.";

  // Run the query.
  $result = mysql_query($sql);
  if(!$result)
  {
    // Get the error message.
    $err_msg = mysql_error();
    $email = $ct_config['blog_contact'];
    $ret .=  "<hr />\n";
    $ret .=  "<p>There was a problem running the <b>$query_desc</b> query. Please report the message ";
    $ret .=  "below to the <a href=\"mailto:$email\">webmaster</a>, telling them when and where the problem occurred.</p>";
    $ret .=  "<pre><b>ERROR MESSAGE:</b>\n$err_msg</pre>";
    $ret .=  "<hr />\n";
    $ret .=  $sql;
    if($_REQUEST['backtrace'])
    {
      print_r(debug_backtrace());
    }
    echo $ret;
  }

  if($ct_config['devo'])
  {
    $time =  microtime(true) - $time_start;
    $ct_config['devstr']['sql'][]= array("sql"=>$sql,"time"=>$time);
  }

  return $result;
}


function real_db_escape(&$var)
{
  if(is_array($var))
  {
    foreach($var as $k=>$v)
    {
      if(is_array($v))
      {
        real_db_escape($var[$k]);
      }
      else
      {
        $var[$k] = mysql_real_escape_string($v);
      }
    }
  }
  else
  {
    $var = mysql_real_escape_string($var);
  }
}


function real_db_unescape(&$var)
{
  if(is_array($var))
  {
    foreach($var as $k=>$v)
    {
      if(is_array($v))
      {
        stripslashes($var[$k]);
      }
      else
      {
        $var[$k] = stripslashes($v);
      }
    }
  }
  else
  {
    $var = stripslashes($var);
  }
}

?>
