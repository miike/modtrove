
function dtag(){
	var selec = arguments[0];
	var data_id = (arguments.length <= 2) ? arguments[1] : null;
	
	var atext;
	
	if(data_id){
		atext = '[' + selec + ']'+data_id+'[/'+selec+']';
		$('#bbcode').replaceSelection(atext);
		return true;
	}else{
		var range = $('#bbcode').getSelection();
		atext = '[' + selec + ']'+range.text+'[/'+selec+']';
		$('#bbcode').replaceSelection(atext);
		return false;
	}
	
}

function tag(selec){
	 dtag(selec);
}

function linktag(id){
	var atext = '[blog]'+ id +'[/blog]';
	$(window.opener.document.getElementById('bbcode')).replaceSelection(atext);
}