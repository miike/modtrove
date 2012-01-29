<?php

include("../lib/default_config.php");
include("style/{$ct_config['blog_style']}/blogstyle.php");

$status = (is_set_not_empty('error_status', $_SESSION)) ? $_SESSION['error_status'] : 500;
$uri = $ct_config['blog_path'];
$uri .= (is_set_not_empty('error_uri', $_SESSION)) ? $_SESSION['error_uri'] : '';

//unset_http_error();

$title_url = 'error.php';
$title = 'Sorry...';
$desc = '';
$head = '';

if($status == 404)
{
  header("HTTP/1.0 404 Not Found");
  $body = <<<BODY
  <h2>404 - Page not found</h2>
  <p>Unknown path or file not found "{$uri}"</p>
BODY;
}
elseif($status == 500)
{
  header("HTTP/1.0 500 Internal Error");
  $body = <<<BODY
  <h2>500 - Internal error</h2>
  <p>Error accessing "{$uri}"</p>
  <p>LabTrove has encountered and internal error while accessing the above path.</p>
BODY;
}
else
{
  header("HTTP/1.0 {$status}");
  $body = <<<BODY
  <h2>{$status} - Unknown problem</h2>
  <p>Problem accessing "{$uri}"</p>
  <p>LabTrove encoutered an problem while trying to access the above path.<br>Please review the path to ensure it is correct.</p>
BODY;
}

include('page.php');

?>
