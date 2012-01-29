var isMozilla = (navigator.userAgent.toLowerCase().indexOf('gecko')!=-1) ? true : false;
var regexp = new RegExp("[\r]","gi");



function dtag(selec, data_id)
{
	if (isMozilla) 
	{
	// Si on est sur Mozilla

		oField = document.forms['blog'].elements['text'];

		objectValue = oField.value;

		deb = oField.selectionStart;
		fin = oField.selectionEnd;

		objectValueDeb = objectValue.substring( 0 , oField.selectionStart );
		objectValueFin = objectValue.substring( oField.selectionEnd , oField.textLength );
		objectSelected = objectValue.substring( oField.selectionStart ,oField.selectionEnd );
		oField.focus();	
		
		oField.value = objectValueDeb + "[" + selec + "]" + data_id + "[/" + selec + "]" + objectValueFin;
		fin = objectValueDeb.length + (2* selec.length) + 5 + data_id.toString().length;
		oField.setSelectionRange(fin,fin);
	}
	else
	{
	// Si on est sur IE
		
		oField = document.forms['blog'].elements['text'];
		var str = document.selection.createRange().text;

		
		// Si on a selectionné du texte
			var sel = document.selection.createRange();
			sel.text = "[" + selec + "]" + data_id + "[/" + selec + "]";
			sel.collapse();
			sel.select();
		
		
	}
}

function tag(selec)
{
	if (isMozilla) 
	{
	// Si on est sur Mozilla

		oField = document.forms['blog'].elements['text'];

		objectValue = oField.value;

		deb = oField.selectionStart;
		fin = oField.selectionEnd;

		objectValueDeb = objectValue.substring( 0 , oField.selectionStart );
		objectValueFin = objectValue.substring( oField.selectionEnd , oField.textLength );
		objectSelected = objectValue.substring( oField.selectionStart ,oField.selectionEnd );

	//	alert("Debut:'"+objectValueDeb+"' ("+deb+")\nFin:'"+objectValueFin+"' ("+fin+")\n\nSelectionné:'"+objectSelected+"'("+(fin-deb)+")");
			
		oField.value = objectValueDeb + "[" + selec + "]" + objectSelected + "[/" + selec + "]" + objectValueFin;
		oField.selectionStart = strlen(objectValueDeb);
		oField.selectionEnd = strlen(objectValueDeb + "[" + selec + "]" + objectSelected + "[/" + selec + "]");
		oField.focus();
		oField.setSelectionRange(
			objectValueDeb.length + selec.length + 2,
			objectValueDeb.length + selec.length + 2);
	}
	else
	{
	// Si on est sur IE
		
		oField = document.forms['blog'].elements['text'];
		var str = document.selection.createRange().text;

		if (str.length>0)
		{
		// Si on a selectionné du texte
			var sel = document.selection.createRange();
			sel.text = "[" + selec + "]" + str + "[/" + selec + "]";
			sel.collapse();
			sel.select();
		}
		else
		{
			oField.focus(oField.caretPos);
		//	alert(oField.caretPos+"\n"+oField.value.length+"\n")
			oField.focus(oField.value.length);
			oField.caretPos = document.selection.createRange().duplicate();
			
			var bidon = "%~%";
			var orig = oField.value;
			oField.caretPos.text = bidon;
			var i = oField.value.search(bidon);
			oField.value = orig.substr(0,i) + "[" + selec + "][/" + selec + "]" + orig.substr(i, oField.value.length);
			var r = 0;
			for(n = 0; n < i; n++)
			{if(regexp.test(oField.value.substr(n,2)) == true){r++;}};
			pos = i + 2 + selec.length - r;
			//placer(document.forms['news'].elements['newst'], pos);
			var r = oField.createTextRange();
			r.moveStart('character', pos);
			r.collapse();
			r.select();

		}
	}
}

function linktag(selec)
{
	if (isMozilla) 
	{
	// Si on est sur Mozilla

		oField = window.opener.document.forms['blog'].elements['text'];

		objectValue = oField.value;

		deb = oField.selectionStart;
		fin = oField.selectionEnd;

		objectValueDeb = objectValue.substring( 0 , oField.selectionStart );
		objectValueFin = objectValue.substring( oField.selectionEnd , oField.textLength );
		objectSelected = objectValue.substring( oField.selectionStart ,oField.selectionEnd );

	//	alert("Debut:'"+objectValueDeb+"' ("+deb+")\nFin:'"+objectValueFin+"' ("+fin+")\n\nSelectionné:'"+objectSelected+"'("+(fin-deb)+")");
			
		oField.value = objectValueDeb + "[blog]" + selec + "[/blog]" + objectSelected + objectValueFin;
		oField.selectionStart = objectValueDeb.length;
		oField.selectionEnd = objectValueDeb.length + 6 + selec + 7 + objectSelected.length;
		oField.focus();
	
		oField.setSelectionRange(
				objectValueDeb.length + selec.length + 13,
				objectValueDeb.length + selec.length + 13);
	}
	else
	{
	// Si on est sur IE
		
		oField = window.opener.document.forms['blog'].elements['text'];
		var str = document.selection.createRange().text;

		if (str.length>0)
		{
		// Si on a selectionné du texte
			var sel = document.selection.createRange();
			sel.text = "[blog]" + selec + "[/blog]" + str;
			sel.collapse();
			sel.select();
		}
		else
		{
			oField.focus(oField.caretPos);
		//	alert(oField.caretPos+"\n"+oField.value.length+"\n")
			oField.focus(oField.value.length);
			oField.caretPos = document.selection.createRange().duplicate();
			
			var bidon = "%~%";
			var orig = oField.value;
			oField.caretPos.text = bidon;
			var i = oField.value.search(bidon);
			oField.value = orig.substr(0,i) + "[blog]" + selec + "[/blog]" + orig.substr(i, oField.value.length);
			var r = 0;
			for(n = 0; n < i; n++)
			{if(regexp.test(oField.value.substr(n,2)) == true){r++;}};
			pos = i + 2 + selec.length - r;
			//placer(document.forms['news'].elements['newst'], pos);
			var r = oField.createTextRange();
			r.moveStart('character', pos);
			r.collapse();
			r.select();

		}
	}

		
}