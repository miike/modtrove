<?php
define('FPDF_FONTPATH','fpdf/font/');
include('fpdf/fpdf.php');

class barcodeLabelIt extends FPDF {

	var $colorBlack = array(0,0,0);
	var $colorWhite = array(255,255,255);

	function barcode($posx,$posy,$width,$height,$type,$barcode){

		//$this->Rect($posx,$posy,$width,$height);
			
		$NarrowRatio = 20;
        $WideRatio = 55;
        $QuietRatio = 35;

        $nChars = (strlen($barcode)+2) * ((6 * $NarrowRatio) + (3 * $WideRatio) + ($QuietRatio));
        $Pixels = $width / $nChars;
        $NarrowBar = number_format($NarrowRatio * $Pixels,3);
        $WideBar = number_format($WideRatio * $Pixels,3);
        $QuietBar = number_format($QuietRatio * $Pixels,3);

		$ActualWidth = (($NarrowBar * 6) + ($WideBar*3) + $QuietBar) * (strlen ($barcode)+2);
		if (($NarrowBar == 0) || ($NarrowBar == $WideBar) || ($NarrowBar == $QuietBar) || ($WideBar == 0) || ($WideBar == $QuietBar) || ($QuietBar == 0))
        {
                $this->Text($posx,$posy+3, "barcode size is too small!");
                return;
        }

	$CurrentBarX = $posx;
    

	    $Color = $White;
        $BarcodeFull = "*".strtoupper ($barcode)."*";
        settype ($BarcodeFull, "string");
        
		$bartop = $posy;

		$colorWhite = $this->colorWhite;
		$colorBlack = $this->colorBlack;
		$color = "white";

        for ($i=0; $i<strlen($BarcodeFull); $i++)
        {
                $StripeCode = $this->Code39 ($BarcodeFull[$i]);


                for ($n=0; $n < 9; $n++)
                {
                        if ($color == "white"){
							$color = "black";
						 $Color = $colorBlack;
			             }else{
							$color = "white";
							 $Color = $colorWhite;
						}

						$this->SetFillColor($Color[0],$Color[1],$Color[2]);

                        switch ($StripeCode[$n])
                        {
                                case '0':
                                        $this->Rect($CurrentBarX, $bartop, $NarrowBar, $height, 'F');
                                        $CurrentBarX += $NarrowBar;
                                        break;


                                case '1':
                                        $this->Rect($CurrentBarX, $bartop, $WideBar, $height, 'F');
                                        $CurrentBarX += $WideBar;
                                        break;
                        }
                }

				
                $Color = $colorWhite;
				$color = "white";
				$this->SetFillColor($Color[0],$Color[1],$Color[2]);
                $this->Rect($CurrentBarX, $bartop, $QuietBar, $height, 'F');
                $CurrentBarX += $QuietBar;
        }

	}		





function Code39 ($Asc)
{
        switch ($Asc)
        {
                case ' ':
                        return "011000100";     
                case '$':
                        return "010101000";             
                case '%':
                        return "000101010"; 
                case '*':
                        return "010010100"; // * Start/Stop
                case '+':
                        return "010001010"; 
                case '|':
                        return "010000101"; 
                case '.':
                        return "110000100"; 
                case '/':
                        return "010100010"; 
                case '0':
                        return "000110100"; 
                case '1':
                        return "100100001"; 
                case '2':
                        return "001100001"; 
                case '3':
                        return "101100000"; 
                case '4':
                        return "000110001"; 
                case '5':
                        return "100110000"; 
                case '6':
                        return "001110000"; 
                case '7':
                        return "000100101"; 
                case '8':
                        return "100100100"; 
                case '9':
                        return "001100100"; 
                case 'A':
                        return "100001001"; 
                case 'B':
                        return "001001001"; 
                case 'C':
                        return "101001000";
                case 'D':
                        return "000011001";
                case 'E':
                        return "100011000";
                case 'F':
                        return "001011000";
                case 'G':
                        return "000001101";
                case 'H':
                        return "100001100";
                case 'I':
                        return "001001100";
                case 'J':
                        return "000011100";
                case 'K':
                        return "100000011";
                case 'L':
                        return "001000011";
                case 'M':
                        return "101000010";
                case 'N':
                        return "000010011";
                case 'O':
                        return "100010010";
                case 'P':
                        return "001010010";
                case 'Q':
                        return "000000111";
                case 'R':
                        return "100000110";
                case 'S':
                        return "001000110";
                case 'T':
                        return "000010110";
                case 'U':
                        return "110000001";
                case 'V':
                        return "011000001";
                case 'W':
                        return "111000000";
                case 'X':
                        return "010010001";
                case 'Y':
                        return "110010000";
                case 'Z':
                        return "011010000";
                default:
                        return "011000100"; 
        }
}




function qr_code($posx,$posy,$width,$barcode){
$height = $width;

	error_log($width);
$pres = 0.01;
if(!$width || !$height) $error = "Error: Check size infomation";

include('qr_code/qr_img.php');

foreach($objects as $obj){
	   $this->Rect($obj['x1'], $obj['y1'], ($obj['x2']-$obj['x1']), ($obj['y2']-$obj['y1']), 'F');
}


}

}

?>