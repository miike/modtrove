<?php
include('barcodeLabelIt.php');
include("../../lib/default_config.php");

$pdf=new barcodeLabelIt();
$pdf->SetAutoPageBreak(false);


$type = $_REQUEST['type'];
$id = $_REQUEST['id'];

if($type=='blog_bit' && $_REQUEST['qr_code']){
$sql = "SELECT * FROM  `{$ct_config['uri_db']}`.`uri` WHERE  `uri_id` = {$id} LIMIT 1";
					$tresulta = runQuery($sql,'Fetch Page Groups');
 					$line_uri = mysql_fetch_array($tresulta);

$uri = $line_uri['uri_url'];

$sql = "SELECT *, UNIX_TIMESTAMP(`bit_timestamp`)as datetime FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_uri` ={$id} AND  `bit_edit` = 0";
		$tresulta = runQuery($sql,'Fetch Page Groups');
	$line = mysql_fetch_array($tresulta);




$barcode = dechex($line['bit_uri']);
if($line['bit_uri']<16){
$barcode = "0$barcode";
}
$uri = 'http://'.$ct_config['uri_server'].'/uri/'.$barcode;

$part = strpos($uri,"/",10);
$nuri = "URI:\n".substr($uri,0,$part)."\n".substr($uri,$part);

$url = "{$line_uri['uri_url']}";
$part = strpos($url,"/",10);
$nurl = "URL:\n".substr($url,0,$part)."\n".substr($url,$part);

$key = "KEY:\n".$line['bit_md5'];
//$key = ''.$line['bit_title'];
$datetime = 'DATE: '.date("jS F Y @ H:i",$line['datetime']);

$pdf->SetMargins(27,3,2);
$pdf->AddPage();
$pdf->qr_code(1,1,29,$uri);

$pdf->SetFont('Arial','B',9);
$pdf->Cell(34,2.2,'CODE: '.$barcode,0,1,'C');
$pdf->Ln(1);
$pdf->SetFont('Arial','B',4.2);
$pdf->MultiCell(34,2,$nuri,0,'C');
$pdf->SetFont('Arial','B',5);

$pdf->Ln(1);
$pdf->MultiCell(34,1.8,$nurl,0,'C');

$pdf->SetFont('Arial','B',5);
$pdf->Ln(1);
$pdf->MultiCell(34,2,$key,0,'C');
$pdf->Cell(34,2,$datetime,0,1,'C');

}elseif($type=='blog_bit'){
$sql = "SELECT * FROM  `{$ct_config['uri_db']}`.`uri` WHERE  `uri_id` = {$id} LIMIT 1";
					$tresulta = runQuery($sql,'Fetch Page Groups');
 					$line_uri = mysql_fetch_array($tresulta);

$uri = $line_uri['uri_url'];


$sql = "SELECT *, UNIX_TIMESTAMP(`bit_timestamp`)as datetime FROM  `{$ct_config['blog_db']}`.`blog_bits` WHERE  `bit_uri` ={$id} AND  `bit_edit` = 0";
		$tresulta = runQuery($sql,'Fetch Page Groups');
	$line = mysql_fetch_array($tresulta);




$barcode = dechex($line['bit_uri']);
if($line['bit_uri']<16){
$barcode = "0$barcode";
}
$uri = 'URI: http://'.$ct_config['uri_server'].'/uri/'.$barcode;
$url = "URL: {$line_uri['uri_url']}";
$key = 'KEY: '.$line['bit_md5'];
//$key = ''.$line['bit_title'];
$datetime = 'DATE: '.date("jS F Y @ H:i",$line['datetime']);

$pdf->SetMargins(1,14.5,1);
$pdf->AddPage();
$pdf->SetFont('Arial','B',6.5);
$pdf->barcode(6,0,50,14,'3of9', $barcode);

$pdf->Cell(60,2,'CODE: '.$barcode,0,1,'C');
$pdf->Cell(60,2,$uri,0,1,'C');
$pdf->SetFont('Arial','B',4.3);
$pdf->Cell(60,2,$url,0,1,'C');
$pdf->SetFont('Arial','B',6.5);
$pdf->Cell(60,2.7,$key,0,1,'C');
$pdf->Cell(60,2.7,$datetime,0,1,'C');

}elseif($type=='uri'){
					$sql = "SELECT * FROM  `{$ct_config['uri_db']}`.`uri` WHERE  `uri_id` = {$id} LIMIT 1";
					$tresulta = runQuery($sql,'Fetch Page Groups');
 					$line = mysql_fetch_array($tresulta);

$barcode = dechex($id);
if($id<16){
$barcode = "0$barcode";
}
$uri = 'URI: http://'.$ct_config['uri_server'].'/uri/'.$barcode;
$url = "URL: {$line['uri_url']}";
$datetime = 'DATE: '.date("jS F Y @ H:i");

$pdf->SetMargins(1,17.5,1);
$pdf->AddPage();
$pdf->SetFont('Arial','B',6.5);
$pdf->barcode(6,0,50,16.5,'3of9', $barcode);

$pdf->Cell(60,2,'CODE: '.$barcode,0,1,'C');
$pdf->Cell(60,2,$uri,0,1,'C');
$pdf->SetFont('Arial','B',4.3);
$pdf->Cell(60,2,$url,0,1,'C');
$pdf->SetFont('Arial','B',6.5);
$pdf->Cell(60,2.7,$datetime,0,1,'C');

}


if($_REQUEST['action']=="print"){
$file = '/tmp/'.md5(session_id().microtime()).'.pdf';

$pdf->Output($file);	


$sql = "SELECT * FROM  `{$ct_config['uri_db']}`.`printers` WHERE  `print_id` = ".$_REQUEST['printer'];
$tresulta = runQuery($sql,'Fetch Page Groups');
 					$line = mysql_fetch_array($tresulta);
if($line['print_id']){
require_once('php_classes/PrintIPP.php'); 
$ipp = new PrintIPP(); 
$ipp->setHost($ct_config['label_server']); 
$ipp->setPrinterURI("/printers/{$line['print_uri']}"); 
$ipp->setData($file); 
// Path to file.
$ipp->printJob();
} 

unlink($file);

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 TRANSITIONAL//EN\">
<html>

	<head>
		<title></title>
	</head>
	<body onLoad=\"javascript: self.close()\">
	</body>
</html>";
}else{
header('Content-type: application/pdf');
header('Content-disposition: inline;filename=barcode.pdf');
	
$pdf->Output();	
}

?>
