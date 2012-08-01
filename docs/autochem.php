<?php
include("../config.php");
//let's do some magical php by autolinking chemicals.
//lookup properties (from common name)
//CSID, StdInChIKey, StdInChI, SMILES via getcompound info
$token = $ct_config['chemspiderAPIkey'];

function ChemLink($chemname){ //can return csid?
	global $token; //note this is MY token and may need to be changed.
	//get data using cURL
	$url = "http://www.chemspider.com/Search.asmx/SimpleSearch?query=" . urlencode($chemname) . "&token=" . $token;
	$ch = curl_init($url); //initialise cURL
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	if (!($data)){
		die("Failed to connect to Chemspider");
	}
	$xml = simplexml_load_string($data);
	if (!($xml)){
		die("Failed to parse Chemspider XML");
	}
	if ($xml->int != ""){
		$chemurl = "http://www.chemspider.com/Chemical-Structure." . $xml->int . ".html";
		//return $chemurl;
		$csid = $xml->int;
		return $csid;
	}
	else{
		return "";
	}
}

//updated, this converts inchi to a chemspider id and then returns it
function InChIToCSID($inchi){
	$inchi = urlencode($inchi);
	$url = "http://www.chemspider.com/InChI.asmx/InChIToCSID?inchi=$inchi";
	$ch = curl_init($url); //initialise cURL
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	if (!($data)){
		die("Failed to connect to Chemspider");
	}
	$xml = simplexml_load_string($data);
	if (!($xml)){
		die("Failed to parse Chemspider XML");
	}
	if ($xml->string != ""){
		$csid = $xml->string;
		return $csid; //this is the CSID
	}
	else{
		return "";
	}
}

function getCommonName($csid){ //this returns the common name
	//dont need to encode this
	global $token;
	$url = "http://www.chemspider.com/MassSpecAPI.asmx/GetExtendedCompoundInfo?CSID=$csid&token=$token";
	$ch = curl_init($url); //initialise cURL
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	if (!($data)){
		die("Failed to connect to Chemspider");
	}
	$xml = simplexml_load_string($data);
	if (!($xml)){
		die("Failed to parse Chemspider XML");
	}
	if ($xml->CommonName != ""){
		$common = $xml->CommonName;
		return $common; //this is the CSID
	}
	else{
		return "";
	}
}

function getCompoundInfo($csid){ //returns array [inchi, inchikey, SMILES]
	global $token;
	$url = "http://www.chemspider.com/Search.asmx/GetCompoundInfo?CSID=$csid&token=$token";
	$xml = curlReturn($url);
	if ($xml != false){
		$inchi = (string)$xml->InChI;
		$inchikey = (string)$xml->InChIKey;
		$smiles = (string)$xml->SMILES;
		$arr = array("inchi"=>$inchi, "inchikey"=>$inchikey, "smiles"=>$smiles);
		return $arr;
	}
	else{
		return false;
	}
}

function toMol($csid){//converts csid to mol
	global $token;
	$url = "http://www.chemspider.com/InChI.asmx/CSIDToMol?CSID=$csid&token=$token";
	$xml = curlReturn($url);
	if ($xml != false){
		$mol = (string)$xml[0];
		return $mol;
	}
	else{
		return false;
	}
}

function getThumbnail($csid){ //doesn't work...yet.
	global $token;
	$url = "http://www.chemspider.com/Search.asmx/GetCompoundThumbnail?id=$csid&token=$token";
	$xml = curlReturn($url);
	if ($xml != false){
		$b64 = (string)$xml[0];
		return $b64;
	}
	else{
		return false;
	}
	//decoding...
	$decode = base64_decode($b64);
	header("Content-type: image/png");
	echo '<img src="data:image/png;base64,' . $decode . '" />';
	echo $imgstr;
}

function curlReturn($url){ //return parsed XML
	$ch = curl_init($url); //initialise cURL
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	if (!($data)){
		//die("Failed to connect to Chemspider");
		return false;
	}
	$xml = simplexml_load_string($data);
	if (!($xml)){
		//die("Failed to parse Chemspider XML");
		return false;
	}
	else{
		return $xml;
	}
}

//first get csid then determine the mode.
$mode = $_GET['mode'];
$common = $_GET['name'];
$csid = ChemLink($common);
if ($csid == ""){
	die("there's been an error1");
}
if ($mode == "inchi" || $mode == "smiles" || $mode == "inchikey"){
	$result = getCompoundInfo($csid);
	$result = $result[$mode];
	
}
else if ($mode == "mol"){
	$result = toMol($csid);
}
else if ($mode == "csid"){
	$result = $csid;
}
else if($mode == "csidlink"){
	$chemurl = "<a href='http://www.chemspider.com/Chemical-Structure." . $csid . ".html'>" . $common . "</a>";
	$result = $chemurl;
}
else{
	die("Unknown mode");
}

if ($result != ""){
	echo $result;
}
else{
	die("there's been an error2");
}
	
	


?>
