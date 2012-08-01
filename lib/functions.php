<?php

if($ct_config['devo']){
		$ct_config['devstr']['stime'] = microtime(true);
}






function prep_get(){
if($_SERVER['REQUEST_URI'])
if($pos = stripos($_SERVER['REQUEST_URI'],"?",0)){
	$pathinfo = substr($_SERVER['REQUEST_URI'],$pos+1);
	$vars = explode("&",$pathinfo);
	foreach($vars as $var){
	list($key,$value) = explode("=",$var,2);
	$value = urldecode($value);
	$_REQUEST[$key] = $value;
	$_GET[$key] = $value;
	}
}
}

$ct_config['extra_meta']['robots'] = "INDEX,NOFOLLOW";


$ct_config['perm_access'][0] = "View User";
$ct_config['perm_access'][1] = "User";
$ct_config['perm_access'][2] = "Moderator";
$ct_config['perm_access'][3] = "Admin";

$ct_config['yesno'][0] = "no";
$ct_config['yesno'][1] = "yes";
$ct_config['isdel'][1] = "no";
$ct_config['isdel'][0] = "yes";

$ct_config['var_prefix'] = "?";

$config['blog_sub'][1] = "No Email";
//$config['blog_sub'][2] = "A Daily Digest";
//$config['blog_sub'][3] = "A Hourly Digest";
$config['blog_sub'][4] = "1 Email/Post";
$config['blog_sub'][5] = "1 Email/Post (+Comments)";

$config['blog_sub_sort'][1] = "Sort By Date";
$config['blog_sub_sort'][2] = "Sort By Blog";


if(isset($_SERVER['REQUEST_URI'])){
$_SERVER['LABTROVE_REQUEST_PATH'] = substr($_SERVER['REQUEST_URI'],strlen($ct_config['blog_path']));
if(stripos($_SERVER['LABTROVE_REQUEST_PATH'],"?")!==false)
$_SERVER['LABTROVE_REQUEST_PATH'] = substr($_SERVER['LABTROVE_REQUEST_PATH'],0,stripos($_SERVER['LABTROVE_REQUEST_PATH'],"?"));
}

foreach($ct_config['plugins'] as $plugin){
	include_once("plugins/$plugin.php");
}
if( !is_set_not_empty('user_name', $_SESSION) )
	check_remembered();



function renlink($link,$desc = "", $target = ""){
if(!$desc) $desc = $link;
if(!$target) $target = "_blank";

	return "<a href=\"$link\" target=\"$target\" class=\"external\" >$desc</a>";
}


//Check if blog has its own config
function checkblogconfig($id){
	global $ct_config;

	if(file_exists("{$ct_config['config_dir']}/blog_{$id}.php"))
		include_once("{$ct_config['config_dir']}/config/blog_{$id}.php");
}


function xml2php($xml,$first = 1)
{
   $fils = 0;
	$array = array();
   foreach($xml->children() as $key => $value)
   {    
       $child = xml2php($value,0);
       //Add a simple element
           $array[strtoupper((string)$key)] = $child;
            
       $fils++;        
     }
   
   
   if($fils==0 && !$first)
   {
       return (string)$xml;
   }
   
   return $array;
   
}

function readxml($data,$v=0){
$data = "<?xml version='1.0'?>\n<root>{$data}</root>";
if(($xml=@simplexml_load_string($data))!==false)
$bla = xml2php($xml);
if(!$bla) $bla = array();
return $bla;
}


function strtotitle($string) { 
$len=strlen($string); 
$i=0; 
$last= ""; 
$new= ""; 
$string=strtoupper($string); 
while ($i<$len): 
$char=substr($string,$i,1); 
if (ereg( "[A-Z]",$last)): 
$new.=strtolower($char); 
else: 
$new.=strtoupper($char); 
endif; 
$last=$char; 
$i++; 
endwhile; 
return($new); 
}

if( !isset($i) ) { $i = 0; }
$navbar[$i]['page_url'] = '/index.php';
$navbar[$i++]['page_name'] = 'Home';
$navbar[$i]['page_url'] = '/websrv.php';
$navbar[$i++]['page_name'] = 'Web Services';

$navbar[$i]['page_url'] = '/marvin/';
$navbar[$i++]['page_name'] = 'Marvin';
$navbar[$i]['page_url'] = '/projects/';
$navbar[$i++]['page_name'] = 'Projects';
$navbar[$i]['page_url'] = '/software/';
$navbar[$i++]['page_name'] = 'Software';
$navbar[$i]['page_url'] = '/courses/';
$navbar[$i++]['page_name'] = 'Courses';
$navbar[$i]['page_url'] = '/store/';
$navbar[$i++]['page_name'] = 'Store';
$navbar[$i]['page_url'] = '/links.php';
$navbar[$i++]['page_name'] = 'Links';
$navbar[$i]['page_url'] = '/contact.php';
$navbar[$i++]['page_name'] = 'Contact Us';
 
if( lookup_or_default('user_admin', $_SESSION, 0) > 2 )
{
	$navbar[$i]['page_url'] = '/admin/';
	$navbar[$i++]['page_name'] = 'Admin';
}

if( !is_set_not_empty('user_name', $_SESSION) )
{
	$navbar[$i]['page_url'] = '/register.php';
	$navbar[$i++]['page_name'] = 'Register';
}




function new_message($subject, $body,  $html,  $to,  $email,  $prof,  $key, $pri = 1){
global $ct_config;
$sql = "INSERT INTO  `{$ct_config['blog_msgdb']}`.`messages` ( `mess_id` ,`mess_subject` ,`mess_body` ,`mess_html` ,`mess_to` ,`mess_email` ,`mess_proflocate` ,`mess_key` ,`mess_pri` ,`mess_datetime` ) VALUES ( NULL ,  '$subject',  '$body',  '$html',  '$to',  '1',  '1',  '$key',  '1', NOW( ) );";

 runQuery($sql,'insert uri');
}


function mailTo ($from, $to, $oggetto, $contenuto_text, $contenuto_html, $type = "both", $reply = true) {


   //add From: header 
$headers = "From: $from\r\n"; 

if( $type == "both"){
//specify MIME version 1.0 
$headers .= "MIME-Version: 1.0\r\n"; 

//unique boundary 
$boundary = uniqid("Chemtools".time()); 

//tell e-mail client this e-mail contains//alternate versions 
$headers .= "Content-Type: multipart/alternative" . 
   "; boundary = $boundary\r\n\r\n"; 

//message to people with clients who don't 
//understand MIME 
$headers .= "This is a MIME encoded message.\r\n\r\n"; 

//plain text version of message 
$headers .= "--$boundary\r\n" . 
   "Content-Type: text/plain; charset=ISO-8859-1\r\n" . 
   "Content-Transfer-Encoding: base64\r\n\r\n"; 
$headers .= chunk_split(base64_encode($contenuto_text)); 

//HTML version of message 
$headers .= "--$boundary\r\n" . 
   "Content-Type: text/html; charset=ISO-8859-1\r\n" . 
   "Content-Transfer-Encoding: base64\r\n\r\n"; 
$headers .= chunk_split(base64_encode($contenuto_html)); 

//send message 
return mail($to, $oggetto, "", $headers); 
}else{
return mail($to, $oggetto, $contenuto_text, $headers); 
}

}

function validate_email($email){

if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {
return true;
}else {
return false;
}
}

function tempdir($dir, $prefix='', $mode=0777)
  {
    if (substr($dir, -1) != '/') $dir .= '/';

    do
    {
	  $key = md5((mt_rand(0, 9999999)*time()).$key);
      $path = $dir.$prefix.$key;
    } while (!mkdir($path, $mode));

    return $key;
  }

/* checks that the key is a valid entry in the associative array and that the value in the array is not an empty string */
function is_set_not_empty($key, $array)
{
  return (@array_key_exists($key, $array) && $array[$key] != "");
}

/* returns the value of the key in the associative array, or returns a supplied default */
function lookup_or_default($key, $array, $def)
{
  if( @array_key_exists($key, $array) )
  {
    return $array[$key];
  }
  else
  {
    return $def;
  }
}


//Adds a message to screen $sev = message,warning, error 
function newMsg($msg, $sev = "error"){
	$_SESSION['labtrove']['msgs'][] = array("sev"=>$sev,"msg"=>$msg);
}

function drawMsg(){
	if(isset($_SESSION['labtrove']['msgs']) && is_array($_SESSION['labtrove']['msgs']) && count($_SESSION['labtrove']['msgs'])){
		$ret = "<div id=\"messages\">";	
			foreach($_SESSION['labtrove']['msgs'] as $k=>$v){
				$ret .= blog_style_error($v['msg'], $v['sev'], $k);
			}
		$ret .= "</div>";
		$_SESSION['labtrove']['msgs'] = array();
	}
	else { $ret = ''; }
	return $ret;
}


function mkButton($img, $text="", $attr = array()){
	if(isset($attr['style'])){
		 $attr['style'] .= " background-image: url('inc/icons/{$img}.png');";
	}else $attr['style'] = "background-image: url('inc/icons/{$img}.png');";
	
	if(isset($attr['class'])){
		 $attr['class'] .= " button";
	}else $attr['class'] = "button";
	
	
	
	if(!strlen($text)){
		$attr['class'] .= " icononly";
	}
	
	foreach($attr as $k=>$v)
		$att .= " {$k}=\"{$v}\"";
	$ret = "<a $att>{$text}</a>"; 
	return $ret;
}

// http error handling
function set_http_error($status, $uri)
{
	global $ct_config;
	// stash the values in the session so we dont need to clutter the REQUEST
	$_SESSION['error_status'] = $status;
	$_SESSION['error_uri'] = $uri;
	header("Location: {$ct_config['blog_path']}error.php");
}

function unset_http_error()
{
	// clear the values, harmless to leave them here, as only used in error.php
	unset($_SESSION['error_status']);
	unset($_SESSION['error_uri']);
}

?>
