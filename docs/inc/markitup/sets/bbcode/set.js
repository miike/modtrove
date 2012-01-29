// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// BBCode tags example
// http://en.wikipedia.org/wiki/Bbcode
// ----------------------------------------------------------------------------
// Feel free to add more tags
// ----------------------------------------------------------------------------
mySettings = {
	previewParserPath:	'', // path to your BBCode parser
	markupSet: [
		{name:'Bold', key:'B', openWith:'[b]', closeWith:'[/b]'},
		{name:'Italic', key:'I', openWith:'[i]', closeWith:'[/i]'},
		{name:'Underline', key:'U', openWith:'[u]', closeWith:'[/u]'},
		{separator:'---------------' },
		{name:'Picture', key:'P', replaceWith:'[img][![Url]!][/img]'},
		{name:'Link', key:'L', openWith:'[url=[![Url]!]]', closeWith:'[/url]', placeHolder:'Your text to link here...'},
		{separator:'---------------' },
		{name:'Size', key:'S', openWith:'[size=[![Text size (between 8 and 50)]!]]', closeWith:'[/size]',
		dropMenu :[
			{name:'Big', openWith:'[size=24]', closeWith:'[/size]' },
			{name:'Normal', openWith:'[size=12]', closeWith:'[/size]' },
			{name:'Small', openWith:'[size=8]', closeWith:'[/size]' }
		]},
		{separator:'---------------' },
		{name:'Bulleted list', openWith:'[list]\n', closeWith:'\n[/list]'},
		{name:'Numeric list', openWith:'[list=[![Starting number]!]]\n', closeWith:'\n[/list]'}, 
		{name:'List item', openWith:'[*] '},
		{separator:'---------------' },
		{name:'Quotes', openWith:'[quote]', closeWith:'[/quote]'},
		{name:'Code', openWith:'[code]', closeWith:'[/code]',
		dropMenu :[
			{name:'ASP', openWith:'[code=asp]', closeWith:'[/code]' },
			{name:'Bash', openWith:'[code=bash]', closeWith:'[/code]' },
			{name:'C', openWith:'[code=c]', closeWith:'[/code]' },
			{name:'C++', openWith:'[code=cpp]', closeWith:'[/code]' },
			{name:'CSS', openWith:'[code=css]', closeWith:'[/code]' },
			{name:'Matlab', openWith:'[code=matlab]', closeWith:'[/code]' },
			{name:'Java', openWith:'[code=java]', closeWith:'[/code]' },
			{name:'Perl', openWith:'[code=perl]', closeWith:'[/code]' },
			{name:'PHP', openWith:'[code=php]', closeWith:'[/code]' },
			{name:'RDF', openWith:'[code=xml]', closeWith:'[/code]' },
			{name:'SQL', openWith:'[code=sql]', closeWith:'[/code]' },
			{name:'Text', openWith:'[code=text]', closeWith:'[/code]' },
			{name:'XML', openWith:'[code=xml]', closeWith:'[/code]' },
		]},
		{separator:'---------------' },
		{name:'Link to Post', afterInsert:function(markItUp) { window.open( labtrove_path + 'linkblog.php?blog_id='+blog_id,'_blank', 'left=400,top=400,width=450,height=500,toolbar=0,resizable=0,location=0,directories=0,scrollbars=1,menubar=0,status=0'); } },
		{separator:'---------------' },
		{name:'Clean', className:"clean", replaceWith:function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, "") } },
		//{name:'Preview', className:"preview", call:'preview' }
	]
}
