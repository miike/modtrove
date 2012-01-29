<?php

include("../lib/default_config.php");
include("style/{$ct_config['blog_style']}/blogstyle.php");

$title_url = 'about.php';
$title = 'About LabTrove';
$desc = '';
$head = '';

$admin="<a href='mailto:admin@yoursite.com'>Default Admin</a>";
$version = get_version();

$body = <<<BODY
<p>Welcome to LabTrove version {$version}.</p>
<p>This site is maintaind by {$admin}.</p>
<p>This is the default about page, if you wish to customise it please edit docs/about.php.</p>
BODY;

include('page.php');

function get_version()
{
  global $ct_config;

  $version = @file_get_contents("../install/version");
  if(!$version) { $version = 'unknown'; }

  // if we are based around a subversion checkout, determin the revision
  if(file_exists("{$ct_config['pwd']}/docs/.svn/all-wcprops"))
  {
    $fh = fopen("{$ct_config['pwd']}/docs/.svn/all-wcprops", "r");
    while( ($line = fgets($fh)) )
    {
      if(preg_match("/([0-9]+)\/[^\/]+\/docs$/", $line, $matches))
      {
        $rev = $matches[1];
        $version .= "(svn r{$rev})";
      }
    }
    fclose($fh);
  }

  return $version;
}
?>
