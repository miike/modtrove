<?php
define('FPDF_FONTPATH','font/');
require('fpdf.php');
require('../include/functions.php');
require('barcode.php');

class PDF extends FPDF
{
	//Page header

	function Header()
	{
		$this->Image('img/logolg.jpg', 5, 5, 25);
		$this->Image('img/susu.jpg', 175, 5, 20);

	}
/*
	//Page footer

	function Footer()
	{

		$this->SetFont('Arial','',8);
		$text = $GLOBALS['cinfo']['name'].", ".$GLOBALS['cinfo']['address1'].", ";
		$this->Text(5,101,$text);
		$text = $GLOBALS['cinfo']['address2'].", ".$GLOBALS['cinfo']['address3'].", ".$GLOBALS['cinfo']['town'].", ".$GLOBALS['cinfo']['postcode'].".";
		$this->Text(5,103.5,$text);
		$text = "Phone: ".$GLOBALS['cinfo']['phone']." Email: ".$GLOBALS['cinfo']['email'].", Web: ".$GLOBALS['cinfo']['web'];
		$this->Text(5,106,$text);
	}
	*/
	function printthispage()
	{


		$this->AddPage();

		$this->SetFont('Arial','B',12);
	//	$text = "Student ID:";
	//	$this->Text(5,30,$text);
		$text = "Name:";
		$this->Text(15,30,$text);
		$text = "Email:";
		$this->Text(60,30,$text);
		$text = "Order Code:";
		$this->Text(125,30,$text);
		$text = "Collected:";
		$this->Text(160,30,$text);




		$sql = "select c.custfname,c.custsname,c.custemail,o.ordercode,
			o.orderid,c.custid,o.orderstartby from orders o
			left join customers c on o.custid=c.custid
			left join user_users u on u.user_id=o.orderstartby
			where o.orderstatus='To Collect' and o.inactive=0 and u.user_group=$_COOKIE[user_group] order by c.custsname ";

		$result = runQuery($sql,'fetch custid from orders (envelope)');
		$j=37;
		while($row = mysql_fetch_array($result))
		{

			$this->SetFont('Arial','',10);
			$this->SetFillColor(255,0,0);
		//	$this->Text(7,$j,$row[custstudid]);
			$this->Text(15,$j,$row[custsname].", ".$row[custfname]);
			$this->Text(60,$j,$row[custemail]);
			$this->Text(125,$j,$row[ordercode]);
			$text = "______________________";
			$this->Text(160,$j,$text);
			if($j==285)
			{
				$this->AddPage();
				$this->SetFont('Arial','B',12);
				$text = "Name:";
				$this->Text(15,30,$text);
				$text = "Email:";
				$this->Text(60,30,$text);
				$text = "Order Code:";
				$this->Text(125,30,$text);
				$text = "Collected:";
				$this->Text(160,30,$text);
				$j=37;
			}
			else
			{
				$j+=8;
			}


		}

	}

}

//start output
$pdf=new PDF('P','mm','a4');
$pdf->SetAutoPageBreak(true,2);

	$pdf->printthispage();

$pdf->Output();
?>