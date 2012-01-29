<?php
		

	foreach( array(array(4,4),array(4,(14+4*$qrcode_version)),array((14+4*$qrcode_version),4)) as $startmark){
	
	$objects[] = array("type" => "rect", "x1" => $unit*$startmark[0], "y1" => $unit*$startmark[1], "x2" => $unit*($startmark[0]+1-$pres), "y2" => $unit*($startmark[1]+7-$pres));
	$objects[] = array("type" => "rect", "x1" => $unit*$startmark[0], "y1" => $unit*$startmark[1], "x2" => $unit*($startmark[0]+7-$pres), "y2" => $unit*($startmark[1]+1-$pres));
 	$objects[] = array("type" => "rect", "x1" => $unit*($startmark[0]+6), "y1" => $unit*$startmark[1], "x2" => $unit*($startmark[0]+7-$pres), "y2" => $unit*($startmark[1]+7-$pres));
	$objects[] = array("type" => "rect", "x1" => $unit*$startmark[0], "y1" => $unit*($startmark[1]+6), "x2" => $unit*($startmark[0]+7-$pres), "y2" => $unit*($startmark[1]+7-$pres));
	
	$objects[] = array("type" => "rect", "x1" => $unit*($startmark[0]+2), "y1" => $unit*($startmark[1]+2), "x2" => $unit*($startmark[0]+5-$pres), "y2" => $unit*($startmark[1]+5-$pres));
	}

	for($i=0;$i<((2*$qrcode_version)+3);$i++){

		$objects[] = array("type" => "rect", "x1" => $unit*(10), "y1" => $unit*(($i*2)+12), "x2" => $unit*(11-$pres), "y2" => $unit*(($i*2)+13-$pres) );
		$objects[] = array("type" => "rect", "y1" => $unit*(10), "x1" => $unit*(($i*2)+12), "y2" => $unit*(11-$pres), "x2" => $unit*(($i*2)+13-$pres) );

	}

		//trailing dot bottom left
		$objects[] = array("type" => "rect", "x1" => $unit*(12), "y1" => $unit*(13+4*$qrcode_version), "x2" => $unit*(13-$pres), "y2" => $unit*((14+4*$qrcode_version)-$pres));

	//Small Boxes
	
	$nosmbox = (int)($qrcode_version/7)+2;
	if($qrcode_version!=1){
	
	$min = 10;
	$max = (14+4*$qrcode_version);

	$div = ($max - $min) / ($nosmbox-1);

	for($i=0;$i<$nosmbox;$i++){
		for($j=0;$j<$nosmbox;$j++){
		
			if(!($i==0&&$j==0) && !($i==0&&$j==($nosmbox-1)) && !($j==0&&$i==($nosmbox-1))){
			$xpos = $min + ($j*$div);
			$ypos = $min + ($i*$div);

			$objects[] = array("type" => "rect", "x1" => $unit*($xpos), "y1" => $unit*($ypos), "x2" => $unit*($xpos+1-$pres), "y2" => $unit*($ypos+1-$pres));
			$objects[] = array("type" => "rect", "x1" => $unit*($xpos-2), "y1" => $unit*($ypos-2), "x2" => $unit*($xpos-1-$pres), "y2" => $unit*($ypos+3-$pres));
			$objects[] = array("type" => "rect", "x1" => $unit*($xpos-2), "y1" => $unit*($ypos-2), "x2" => $unit*($xpos+3-$pres), "y2" => $unit*($ypos-1-$pres));
			$objects[] = array("type" => "rect", "x1" => $unit*($xpos-2), "y1" => $unit*($ypos+2), "x2" => $unit*($xpos+3-$pres), "y2" => $unit*($ypos+3-$pres));
			$objects[] = array("type" => "rect", "x1" => $unit*($xpos+2), "y1" => $unit*($ypos-2), "x2" => $unit*($xpos+3-$pres), "y2" => $unit*($ypos+3-$pres));		
			}
		}
	}
	
	}
	
	if($qrcode_version > 6){
	include("qr_ver_data.php");

		foreach($versions[$qrcode_version] as $box){
			$objects[] = array("type" => "rect", "x1" => $unit*($box['start_x']), "y1" => $unit*($box['start_y']), "x2" => $unit*($box['start_x']+$box['size']-$pres), "y2" => $unit*($box['start_y']+1-$pres));
			$objects[] = array("type" => "rect", "y1" => $unit*($box['start_x']), "x1" => $unit*($box['start_y']), "y2" => $unit*($box['start_x']+$box['size']-$pres), "x2" => $unit*($box['start_y']+1-$pres));		
		}

	}


?>