function NewSection()
{
	if(document.blog.section.value == '- New section -')
	{
		var new_section = prompt("New section name:", "");
		if(new_section != null && new_section != "")
		{
			var num = document.blog.section.options.length;
			document.blog.section.options[num] = new Option(new_section, new_section);
			document.blog.section.options[num].selected = true;
		}
	}
}

function NewMeta()
{
	var select = document.getElementById('metadata_key_new');
	if(select.value == '**new**')
	{
		var new_meta = prompt("New metadata key:", "");
		if(new_meta != null && new_meta != "")
		{
			var num = select.options.length;
			select.options[num] = new Option(new_meta, new_meta);
			select.options[num].selected = true;
		}
	}
}


var metadata_row_id = 100000;

$(function() {
	$('#metadata_key_new').change(function(){
		NewMeta();
		selval = $(this).val();
		if(selval.length){
			$('#metadata_value_new').addClass('required');
			
				$('#metadata_value_new').removeClass('valid');
		}else{
			$('#metadata_value_new').removeClass('required');			
		}
	});
	
	$('#metadata_value_new').change(function(){
		selval = $(this).val();
		if(selval.length){
			$('#metadata_key_new').addClass('required');
			
				$('#metadata_value_new').removeClass('valid');
		}else{
			$('#metadata_key_new').removeClass('required');			
		}
	});
	
	$('#metadata_add_button').click(function(){
		selkey = $('#metadata_key_new').val();
		selval = $('#metadata_value_new').val();
		if(!selkey.length || !selkey.length || selkey=='**new**'){
			alert('Please enter a key and value');
			return false;
		}
			var ins = '<tr id="meta_row_'+metadata_row_id+'"><td></td>';
			ins += '<td><input type="text" name="meta_key[]" value="'+ selkey +'" style="width:130px" class="required" /></td>';
			ins += '<td><input type="text" name="meta_value[]" value="'+ selval +'" style="width:180px" class="required" /></td>';
			ins += '<td><a  onClick="removeMetadata('+metadata_row_id+')" style="background-image: url(\'inc/icons/table_delete.png\');" class="button icononly"></td></tr>';
		$("#metadata_table tr:last").before(ins);
		metadata_row_id += 1;
		$('#metadata_key_new').val('');
			$('#metadata_key_new').removeClass('required');
		$('#metadata_value_new').val('');
			$('#metadata_value_new').removeClass('required');
	});
	
});

function removeMetadata(id){

	$('#meta_row_'+id).remove();
}



