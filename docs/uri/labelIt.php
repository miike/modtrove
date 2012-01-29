<?php
/*////////////////////////////////////////////////////////////////////////
	labelIt.php v1.00.1

	labelIt - A Toolkit for building label PDF's

	Copyright (c) 2006 bluerhinos.co.uk

If you use this Library please could you inform us that you are
doing so, in order for us to realise how ut is being used. This request is 
in no way a requirement.

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

If you have any questions or comments, please email:

Andrew Milsted andrew@bluerhinos.co.uk http://www.bluerhinos.co.uk

////////////////////////////////////////////////////////////////////////*/

define('FPDF_FONTPATH','fpdf/font/');
include('fpdf/fpdf.php');
include('ximpleML.php');

class labelIt extends FPDF {

//misc varibles
var $hno, $vno, $lwidth, $lheight, $topmargin,$sidemargin,$hpitch,$vpitch;
var $label, $labelborder;

var $curlabel = 0;
var $border = array(221,221,221);
var $font = 'Arial';
var $fontstyle = '';
var $fontsize = 10;
var $fontalign = 'L';
var $labelrotate = 'P';
var $pageheight;
var $pagewidth;

var $labeldataurl = 'http://localhost/labs/lables/site/';

function setLabel($label, $ort = 'P') 
{
		$this->labelrotate = $ort; 
		
		$fetch = new ximpleML();
		$fetch->getdata();

		$labled = $fetch->items[$label];	

		if($this->labelrotate=='P'){
        
		$this->hno = $labled['hno'];
		$this->vno = $labled['vno'];

		$this->lwidth =  $labled['lwidth'];
		$this->lheight =  $labled['lheight'];

		$this->topmargin =  $labled['topmargin'];
		$this->sidemargin =  $labled['sidemargin'];

		$this->hpitch =  $labled['hpitch'];
		$this->vpitch =  $labled['vpitch'];

		}else{

		$this->hno = $labled['vno'];
		$this->vno = $labled['hno'];

		$this->lwidth =  $labled['lheight'];
		$this->lheight =  $labled['lwidth'];

		$this->topmargin =  $labled['sidemargin'];
		$this->sidemargin =  $labled['topmargin'];

		$this->hpitch =  $labled['vpitch'];
		$this->vpitch =  $labled['hpitch'];

		}	

		$this->label = $labled['desc'];

		if(!$labled['code']){
			error_log('labelIt error: Invalid Label Type - '.$label);
			die('<B>labelIt error: </B>Invalid Label Type - '.$label);
			}
		$this->FPDF($this->labelrotate,'mm','62x29');

		if($this->labelrotate=='P'){
			$this->pageheight = $this->fh;
			$this->pagewidth = $this->fw;
		}else{
			$this->pageheight = $this->fd;
			$this->pagewidth = $this->fh;
		}	
}

function updateLabelData(){
	if(md5_file('label_data.xml')!=file_get_contents($this->labeldataurl.'md5_data.php'))		{
			file_put_contents('label_data.xml',file_get_contents($this->labeldataurl.'label_data.php'));
		}
}

function addLabel($labeltext = NULL){
	
	$this->curlabel++;
	$this->curx += $this->hpitch;

	if($this->curlabel>($this->vno*$this->hno))
		$this->curlabel = 1;

	if(($this->curlabel % $this->hno) == 1){
			$this->cury += $this->vpitch;
			$this->curx = $this->sidemargin;
	}

	if($this->curlabel==1){
			$this->AddPage();
			$this->cury = $this->topmargin;
			$this->curx = $this->sidemargin;

	}	
		if($this->labelborder){
			$this->SetLineWidth(0.2);
			$this->SetDrawColor($this->border[0],$this->border[1],$this->border[2]);
			$this->Rect($this->curx,$this->cury,$this->lwidth,$this->lheight);
		}

		if($labeltext){
			$this->SetXY($this->curx+1,$this->cury+1);
			$this->SetFont($this->font,$this->fontstyle,$this->fontsize);
			$this->MultiCell(($this->lwidth-2), (0.36*$this->fontsize), $labeltext,0,$this->fontalign);

		}
	}
	function startLabel($ii = 0){
	for($i=0;$i<$ii;$i++)
		$this->addLabel();
	}
	function Header()
	{
	$this->SetXY($this->sidemargin,($this->topmargin)/2-2);
    $this->SetFont('Arial','',12);
	$sidebox = (($this->pagewidth-60)-($this->sidemargin*2))/2;
    $this->Cell($sidebox,4,'bluerhinos.co.uk',0,0,'L');
  	$this->SetFont('Courier','',14);
	$this->Cell(60,4,'labelIt'.chr(153),0,0,'C');
    $this->SetFont('Arial','',8);
	$this->Cell($sidebox,4,"Label Type: ".$this->label." (".$this->lwidth."x".$this->lheight."mm)",0,0,'R');
	}
	function Footer()
	{
	//$this->SetXY($this->sidemargin,$this->topmargin+($this->vpitch*($this->vno-1))+2+$this->lheight);
	$this->SetXY(8,$this->pageheight-(($this->topmargin)/2+2));
    $sidebox = (($this->pagewidth-60)-(16))/2;
   	
    $this->SetFont('Arial','',8);
	$this->Cell($sidebox,4,"Label Type: ".$this->label." (".$this->lwidth."x".$this->lheight."mm)",0,0,'L');  $this->SetFont('Courier','',14);
	$this->Cell(60,4,'labelIt'.chr(153),0,0,'C');
	$this->SetFont('Arial','',12);
	$this->Cell($sidebox,4,'bluerhinos.co.uk',0,0,'R');
 
	}

}

?>