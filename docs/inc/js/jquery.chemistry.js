function cmon(selmode, table){
 var scripturl = "";
 var content = $("#bbcode").tinymce().selection.getContent({format : 'text'});
 if (content == ""){
 	return 0;
 }
 if (selmode == ' '){
 	return 0;
 }
 //there are two different scripts that fetch data, one calls the chemspider(autochem) API, the other the wolframalpha(alpha)
 var mode=selmode;
 
 if (mode == 'basic' || mode == 'identifiers' || mode == 'thermo'){
 	scripturl = "alpha.php";
 	 var request = $.ajax({
 		url: scripturl,
 		type: "GET",
 		data: {"q":content,"prop":mode,"table":table},
 		dataType:"html"
 	});
 }
 else{
 	scripturl = "autochem.php";
 	 var request = $.ajax({
 		url: scripturl,
 		type: "GET",
 		data: {"name":content,"mode":mode},
 		dataType:"html"
 	});
 } 
//after the request has been initiated provide some feedback to show something has happened.
$("#predictive").html("Working...<img src='/inc/ajax-loader.gif' alt='Working...'>");
 
//note content is presumably a chemical in this case...
//$('#bbcode').tinymce().execCommand('mceReplaceContent',false,"Calculating...");
 
 request.done(function(msg){
 	var s=msg;//s contains returned material
 	$('#bbcode').tinymce().execCommand('mceReplaceContent', false, s);
 	$("#predictive").text(""); //clear text
 	//reset select control
 	$(".chemicalise").val(' ');
 	});
 	
 request.fail(function(jqXHR, textStatus){
 	alert("Request failed: " + textStatus);
 	$("#predictive").text("Failed to perform request");
 });
 
 return false;
 
}

function identify(){
	var tablemode = false;
	var checked = $("#tablise").is(":checked");
	if (checked){
		tablemode = true;
	}
	var mode = $(".chemicalise option:selected").val()
	cmon(mode,tablemode);
}
