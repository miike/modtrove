tinyMCEPopup.requireLangPack();

var InsertDialog = {
	init : function() {
		var f = document.forms[0];
		//we need to replace text somewhere in here.
		// Get the selected contents as text and place it in the input
		f.someval.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		
	},

	insert : function(itext) {
		// Insert the contents from the input into the document
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, '[data]' + itext + '[/data]');
		tinyMCEPopup.close();
	}

	
};

tinyMCEPopup.onInit.add(InsertDialog.init, InsertDialog);
