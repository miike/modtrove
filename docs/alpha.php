<?php
include_once("../config.php");
//bindings for wolframalpha
//select type must be 'basic' or 'identifiers' or 'thermo'
$properties = array('basic', 'identifiers', 'thermo');
//valid shortcodes. basic['mass','moles','volume','eq'] identifiers['beilstein','cas','cid','sid'] thermo[]
$appid = $ct_config['wolframappID'];


function getInfo($xml, $selecttype){
	$found = false;
	$str = "success";
	$str2 = "error";
	$success = $xml->attributes()->$str;
	$error = $xml->attributes()->$str;

	//if ($success == "false" || $error == "true"){
		//return "wolfram error";
	//}
	
	if ($selecttype == "basic"){
		$podname = 'Basic properties';
	}
	else if ($selecttype == "identifiers"){
		$podname = 'Chemical identifiers';
	}
	else{
		$podname = "Thermodynamic";
	}
	
	foreach($xml->pod as $pod){
		if (stripos($pod->attributes()->title, $podname) !== false){
			$found = true;
			return (string)$pod->subpod->plaintext; //ideally this should be setup for multiple pods, but we only need one at the moment
		}
	}
	if ($found == false){
		return "failed to find details.";
	}
	
}

function query($term, $property, $shortcode="", $table=false){
	global $properties;
	global $appid;
	//first check it is a valid property
	if (in_array($property, $properties) == false){
		die("Invalid property");
	}


	$format = "plaintext";
	 //this should be your own appID
	$eterm = urlencode($term);
	$url = "http://api.wolframalpha.com/v2/query?appid=$appid&input=$eterm&format=$format";
	$ch = curl_init($url); //initialise cURL
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	//parse XML

	$xml = simplexml_load_string($data);
	if (!($xml)){
		die("failed to parse XML");
	}
	else{
		$result = getInfo($xml, $property);
	}

	
	//if requesting a specific property, return this property
	//properties for basic
	if ($shortcode != ""){
		if ($property == 'basic'){
			$shortcodes = array('mass'=>0, 'moles'=>1, 'volume'=>2, 'eq'=>3); //order may change without notice
			//explode plaintext in subpod
			$split = explode(PHP_EOL,$result); //split at newline
			$fullstr = $split[$shortcodes[$shortcode]];
			$value = explode("|",$fullstr);
			$result = $value[1];
			echo $result;
		}
		if ($property == 'identifiers'){
			$shortcodes = array('cas'=>0, 'beilstein'=>1, 'cid'=>2, 'sid'=>3);
			$split = explode(PHP_EOL,$result);
			$fullstr = $split[$shortcodes[$shortcode]];
			$value = explode("|",$fullstr);
			$result = $value[1];
			echo $result;
		}
	}
	else{
		//option to use tables if it's echoing more than one result
		if ($table){
			$result = formatTable($result);
		}
		echo $result;
	}
	
	
}

function validShortCode($sc, $property){
	if ($property == 'basic'){
		$shortcodes = array('mass'=>0, 'moles'=>1, 'volume'=>2, 'eq'=>3); //order may change without notice
	}
	else if ($property == 'identifiers'){
		$shortcodes = array('cas'=>0, 'beilstein'=>1, 'cid'=>2, 'sid'=>3);
	}
	else{
		$shortcodes = array(); //no shortcodes for thermodynamics yet.
	}
	
	if(in_array($sc, $shortcodes)==true){
		return true;
	}
	else{
		return false;
	}
}

function formatTable($plain){
	$elements = explode(PHP_EOL, $plain); //expodes into each line
	$html = "<table border='1'><thead><tr><th>Property</th><th>Value</th></tr></thead><tbody>";
	foreach($elements as $property){
		$split = explode("|", $property);
		$html .= "<tr><td>" . $split[0] . "</td><td>" . $split[1] . "</td></tr>\n";
	}

	$html .= "</tbody></table>";

	return $html;
}

//determine if a shortcode exists, if so isolate and send it to the function
//should also check for valid property
$qterm = $_GET['q'];
$sc = "";
$blast = explode(":",$qterm);
if (sizeof($blast) > 1){
	$sc = $blast[1];
	$qterm = $blast[0];
}

//check that the sc is valid and existing.
if ($sc != ""){
	$valid = validShortCode($sc, $_GET['prop']);
	if ($valid==false){
		die("Invalid shortcode for property");
	}
}
$table = $_GET['table'];

query($qterm, $_GET['prop'], $sc, $table);
?>
