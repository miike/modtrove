<?php 

include("../../lib/default_config.php");

$type = $_REQUEST['type'];
$id = (int)$_REQUEST['id'];

$sql = "SELECT * FROM  `{$ct_config['uri_db']}`.`uri` WHERE  `uri_id` = '{$id}' LIMIT 1";
					$tresulta = runQuery($sql,'Fetch Page Groups');
 					$line = mysql_fetch_array($tresulta);

$barcode = dechex($id);
if($id<16){
$barcode = "0$barcode";
}
$uri = 'URI: http://'.$ct_config['uri_server'].$ct_config['blog_path'].'uri/'.$barcode;
$url = "URL: {$line['uri_url']}";


$body .= "		
		<h2>URI Label Generator</h2>
			CODE: {$barcode}<br />
			{$uri}<br />
			{$url}
		";

if($ct_config['label_server']){	
	$body .= "		<h3>Print Label</h3>
			<form action=\"/uri/barcode_print.php\" method=POST>
				Select Printer <select name=\"printer\">";


		$prolocatelocal = $_COOKIE['LAST_PRINTER_USED'];
		$sql = "SELECT * FROM  `{$ct_config['uri_db']}`.`printers`";
						$tresulta = runQuery($sql,'Fetch Page Groups');
	 					while($line = mysql_fetch_array($tresulta)){

						if($prolocatelocal == $line['print_id'])
					//	if(2 == $line['print_id'])
							$select = " selected";
						else
							$select = "";
					$body .="<option value=\"{$line['print_id']}\"$select>{$line['print_name']}</option>";
		}


	$body .= " </select> ";

	$body .= " <select name=\"qr_code\"> <option value=1>QR Code</option><option value=0 selected>3of9</option>";

	$body .="</select>
			<input type=\"hidden\" name=\"id\" value=\"{$id}\">
			<input type=\"hidden\" name=\"type\" value=\"{$type}\">
			<input type=\"hidden\" name=\"action\" value=\"print\">
			<input type=\"submit\" value=\"print\">
			</form>
		
	";
}
$body .= "		(<a href=\"{$ct_config['blog_path']}uri/barcode_print.php?id={$id}&type={$type}\">just view label</a>)
		(<a href=\"{$ct_config['blog_path']}uri/barcode_print.php?id={$id}&type={$type}&qr_code=1\">just view qr label</a>)
	</body>
</html> ";

$minipage = 1;
include('../page.php');
?>
