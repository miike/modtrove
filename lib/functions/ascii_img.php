<?php


function acsii_img($data_r,$img_type = 'png' ,$title = NULL, $sizey = 600,$sizex = 800){

if($sizey <150)
$sizey = 150;
if($sizex < 200)
$sizex = 200;
$ylable = $title['y'];
$xlable = $title['x'];;


$data_s = 	split("\n",$data_r);
foreach($data_s as $buffer){
	$data_t= split(',',$buffer);
	$data_temp[$data_t[0]] = $data_t[1];
}
unset($data_t);	
	
unset($data_s);
ksort($data_temp);



foreach($data_temp as $x => $y){
	$data_t['x'] = $x; $data_t['y'] = $y;
	$data[] = $data_t;
}

	unset($data_t);
	unset($data_temp);

if($_REQUEST['height'])
$sizey = $_REQUEST['height'];
if($_REQUEST['width'])
$sizex = $_REQUEST['width'];




$margin = 25;

$clip = 4;
$boxy1 = $margin;
$boxy2 = $sizey-($margin+20);
$boxx1 = $margin+40;
$boxx2 = $sizex-($margin);


//$maxy = 10;

$noy = 10;
$maxy = 1;
$miny = 0;

$textymar = 4;

$yfact = 100;


$ylablepos = 65;

//$maxx = $time + (15*60*$i);

$nox = 8;
$minx = 0;
$maxx = 255;
$textxmar = 5;

$xfact = 1;


$xlablepos = 20;


$xpeek_det = 0;

$xdate = "";

$smooth = 1;
$thumb = 0;


$im = @imagecreatetruecolor($sizex , $sizey)
     or die("Cannot Initialize new GD image stream");
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 0, 0, 0);
$grey = imagecolorallocate($im, 99, 99, 99);
$red = imagecolorallocate($im, 255, 0, 0);

imagefilledrectangle ( $im, 0, 0, $sizex , $sizey, $white );

//graph paper
imagerectangle ( $im, $boxx1, $boxy1, $boxx2 ,$boxy2, $grey );

foreach($data as $samp){

	if($samp['y']<$miny)
		$miny=$samp['y'];
	if($samp['y']>$maxy)
		$maxy=$samp['y'];
	if($samp['x']<$minx)
		$minx=$samp['x'];
	if($samp['x']>$maxx)
		$maxx=$samp['x'];
	

}
//smooth graph if needs be
if(count($data) && $smooth==1){
$lowx = $minx;
foreach($data as $samp){
			$x2 = $boxx1 + intval((($samp['x']-$minx)/($maxx-$minx))*($boxx2-$boxx1));
			$y2 =$samp['y']; //$boxy2-intval(($samp['y']/($maxy-$miny))*($boxy2-$boxy1));
			if($x1){
				if($x1==$x2){

				}else{
					$data_s['y'] = array_sum($temp_y)/count($temp_y);
					$data_s['x'] = ($topx + $lowx)/2;
					$new_data[] = $data_s;
					$temp_y = "";
					$lowx = $samp['x'];
				}
			}
			$temp_y[] = $y2;
			$x1 = $x2;
			$y1 = $y2;
			
			$topx = $samp['x'];
		}
		
					$data_s['y'] = array_sum($temp_y)/count($temp_y);
					$data_s['x'] = $maxx;
					$new_data[] = $data_s;		

			$data = $new_data;
			$new_data="";

}

$yfact = pow(10,intval(log(($maxy-$miny),10))-1);
$miny = 0;// ((intval($miny / $yfact)+((($miny/abs($miny))/2)-0.5))*($yfact));
$maxy = ((intval($maxy / $yfact)+((($maxy/abs($maxy))/2)+0.5))*($yfact));
$noy = ($maxy-$miny)/$yfact;
while($noy <= 5){
	$noy *=2;
}
//if(($maxy-$yfact) == $omaxy) $maxy = $omaxy;
$minx = $minx - ($minx % $xfact);
$maxx = ($maxx - ($maxx % $xfact))+$xfact;


//grid
imageline ( $im,  $boxx1, $boxy1, $boxx1, ($boxy2+$clip), $grey );
imageline ( $im,  ($boxx1-$clip), $boxy2, $boxx2, $boxy2, $grey );
//yl lines and lable
$blob = ($boxy2-$boxy1)/$noy;
	$gap = ($maxy-$miny)/$noy; 
if(strlen( $ylable)){
imagestringup ( $im, 4, ($boxx1-$ylablepos), intval(($boxy1+(($boxy2-$boxy1)/2) + (imagefontwidth(4)*strlen($ylable)/2) )), $ylable, $black );
}
for($i=0;$i<($noy+1); $i++){

	$ytextbit = ($maxy-($i*$gap));

	if((abs($ytextbit) > 1000) || ( (0!=$ytextbit) && (abs($ytextbit) < 0.01)))
			$ytextbit = sprintf("%.3e",$ytextbit);
	
		$y = $boxy1 + intval($blob*$i);
		imageline ( $im,  ($boxx1-$clip), $y, $boxx2, $y, $grey );
		imagestring ( $im, 2, intval(( ($boxx1-$textymar)- (imagefontwidth(2)*strlen($ytextbit)) )), ($y-(imagefontheight(2)/2) ), $ytextbit, $black );

	}


if(strlen($xlable)){

imagestring( $im, 4, intval(($boxx1+($boxx2-$boxx1)/2) - (imagefontwidth(4)*strlen($xlable)/2) 	), ($boxy2+$xlablepos), $xlable, $black );
}

$blob = ($boxx2-$boxx1)/$nox;
	$gap = ($maxx-$minx)/$nox; 
	for($i=0;$i<=$nox; $i++){
		$x = $boxx1 + intval($blob*$i);
		imageline ( $im,  $x, ($boxy2+$clip), $x, $boxy1, $grey);
	
$xtextbit = ($minx+($i*$gap));

	if($xdate)
			$xtextbit = date($xdate,(int)($minx+($i*$gap)));
	elseif((abs($xtextbit) > 1000) || ( (0!=$xtextbit) && (abs($xtextbit) < 0.01)))
			$xtextbit = sprintf("%.3e",($minx+($i*$gap)));
	else 
			$xtextbit = ($minx+($i*$gap));

	imagestring ( $im, 4, intval( $x - (imagefontwidth(4)*strlen($xtextbit))/2 ) , ($boxy2+$textxmar), $xtextbit , $black );

	
	}


//draws paper
$first=0;
	foreach($data as $samp){
			$x1 = $oldx;
			$y1 = $oldy;
			$x2 = $boxx1 + intval((($samp['x']-$minx)/($maxx-$minx))*($boxx2-$boxx1));
			$y2 = $boxy2-intval((($samp['y']-$miny)/($maxy-$miny))*($boxy2-$boxy1));
			if($first){
					imageline ( $im,  $x1, $y1, $x2, $y2, $red);
				}
			$first = 1;
			$oldx = $x2;
			$oldy = $y2;
	}


if($img_type == "png"){
header ("Content-type: image/png");
imagepng($im);
}elseif($img_type == "jpg"){
header ("Content-type: image/jpeg");
imagejpg($im);
}elseif($img_type == "gif"){
header ("Content-type: image/gif");
imagegif($im);
}
imagedestroy($im);
exit();
 
}
?>