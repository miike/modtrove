<?php
for ($z=0; $z < 5; $z++) {

$number = rand(10,31);
if ($number == "10"){
$value[$z] = "A";
}
if ($number == "11"){
$value[$z] = "B";
}
if ($number == "12"){
$value[$z] = "C";
}
if ($number == "13"){
$value[$z] = "D";
}
if ($number == "14"){
$value[$z] = "E";
}
if ($number == "15"){
$value[$z] = "F";
}
if ($number == "16"){
$value[$z] = "G";
}
if ($number == "17"){
$value[$z] = "H";
}
if ($number == "18"){
$value[$z] = "I";
}
if ($number == "19"){
$value[$z] = "J";
}
if ($number == "20"){
$value[$z] = "K";
}
if ($number == "21"){
$value[$z] = "L";
}
if ($number == "22"){
$value[$z] = "M";
}
if ($number == "23"){
$value[$z] = "N";
}
if ($number == "24"){
$value[$z] = "O";
}
if ($number == "25"){
$value[$z] = "P";
}
if ($number == "26"){
$value[$z] = "R";
}
if ($number == "27"){
$value[$z] = "S";
}
if ($number == "28"){
$value[$z] = "T";
}
if ($number == "29"){
$value[$z] = "U";
}
if ($number == "30"){
$value[$z] = "V";
}
if ($number == "31"){
$value[$z] = "Z";
}

}

header("Content-type: image/png");

$image = imagecreatetruecolor(120,30);

$black = imagecolorallocate($image, 0, 0, 0);
$white = imagecolorallocate($image, 255, 255, 255);

imagefilledrectangle($image,0,0,119,29,$white);

// text

$string = "";

for ($i = 0; $i < 5; $i++) {
	$random = rand(0,1);
	switch($random) {
		case 0: $font = str_replace("index.php", "", $_SERVER['SCRIPT_FILENAME'])."inc/erthqake.ttf"; break;
		case 1: $font = str_replace("index.php", "", $_SERVER['SCRIPT_FILENAME'])."inc/refrig2.ttf"; break;
	}

	$color = rand(0,64);

	$move = rand(-4,4);
	$rotate = rand(-10,10);

	$string = $string . $value[$i];

	imagettftext($image,18.0,$rotate,5+($i*21),25+$move,imagecolorallocate($image, $color, $color, $color),$font,$value[$i]);
}

$_SESSION[$session_name."captcha"] = $string;

imagepng($image);
?>