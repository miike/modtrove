<?php

chdir(dirname(__FILE__));

include("../default_config.php");



$sitemapi = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$sitemapi .= "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_blogs` WHERE `blog_redirect` = ''";
$tresult = runQuery($sql,'Fetch Page Groups');
while($blog = mysql_fetch_array($tresult)){
	if(checkzone($blog['blog_zone'],1)){

		$sitemap = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$sitemap .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
  
		$sitemap .= "\t<url>\n";
		$sitemap .= "\t\t<loc>http://{$ct_config['this_server']}/{$blog['blog_sname']}/</loc>\n";
		$sitemap .= "\t\t<lastmod>".date('c')."</lastmod>\n";
		$sitemap .= "\t\t<changefreq>always</changefreq>\n";
		$sitemap .= "\t\t<priority>0.9</priority>\n";
		$sitemap .= "\t</url>\n";

		$sql = "SELECT *, UNIX_TIMESTAMP(`bit_timestamp`) as unix_time FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_blog` = {$blog['blog_id']} AND  `bit_edit` =0 
					ORDER BY  `blog_bits`.`bit_datestamp` DESC ";		
		$result = runQuery($sql,'Fetch Page Groups');
		while($post = mysql_fetch_array($result)){
			$name = ereg_replace( "[^A-Za-z0-9\ \_]", "", strip_tags($post['bit_title']));
			$name = str_replace(" ","_",$name);	
			$sitemap .= "\t<url>\n";
			$sitemap .= "\t\t<loc>http://{$ct_config['this_server']}/{$blog['blog_sname']}/{$post['bit_id']}/{$name}.html</loc>\n";
			$sitemap .= "\t\t<lastmod>".date('c',$post['unix_time'])."</lastmod>\n";
			$sitemap .= "\t\t<priority>0.5</priority>\n";
			$sitemap .= "\t</url>\n";
		
		}
		$sitemap .= "</urlset>\n";

		file_put_contents("../../docs/sitemaps/{$blog['blog_sname']}.xml",$sitemap); 
			`gzip -f ../../docs/sitemaps/{$blog['blog_sname']}.xml`;
		$sitemapi .= "\t<sitemap>\n";
		$sitemapi .= "\t\t<loc>http://{$ct_config['this_server']}/{$blog['blog_sname']}/sitemap.xml.gz</loc>\n";
		$sitemapi .= "\t\t<lastmod>".date('c')."</lastmod>\n";
		$sitemapi .= "\t</sitemap>\n";
	}
}
$sitemapi .= "</sitemapindex>";

file_put_contents("../../docs/sitemap.xml", $sitemapi); 

$robots = "User-agent: *
Disallow: /search/
Disallow: /index/
Disallow: /admin/
Disallow: /style/
Disallow: /inc/
Sitemap: http://{$ct_config['this_server']}/sitemap.xml";

file_put_contents("../../docs/robots.txt", $robots); 
?>
