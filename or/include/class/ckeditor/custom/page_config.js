(function () {
	CKEDITOR.plugins.addExternal('youtube', baseurl + '/node_modules/ckeditor-youtube-plugin/youtube/');
	CKEDITOR.plugins.addExternal("tableresize", baseurl + '/node_modules/ckeditor4/plugins/tableresize/');
	CKEDITOR.plugins.addExternal("quicktable", baseurl + '/node_modules/ckeditor-quicktable-plugin/');
})();

CKEDITOR.editorConfig = function (config) {
	config.skin = 'moono';

	config.toolbar_Page = [
		{ name: 'document', groups: ['mode', 'document', 'doctools'], items: ['Source', '-', 'Save', 'Print', '-', 'Templates'] },
		{ name: 'clipboard', groups: ['clipboard', 'undo'], items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
		{ name: 'forms', items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'] },
		{ name: 'editing', items: ['Find', '-', 'SelectAll'] },
		{ name: 'maximize', items: ['Maximize'] },
		'/',
		{ name: 'fonts', items: ['Font', 'FontSize'] },
		{ name: 'paragraph', items: ['Outdent', 'Indent', 'NumberedList', 'BulletedList', 'Blockquote'] },
		{ name: 'insert', items: ['Link', 'Unlink', 'Anchor', '-', 'CreateDiv', 'Table', '-', 'Image', 'Flash', 'Youtube', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe'] },
		{ name: 'tools', items: ['ShowBlocks'] },
		'/',
		{ name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
		{ name: 'colors', items: ['TextColor', 'BGColor'] },
		{ name: 'justify', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
		{ name: 'styles', items: ['Format', 'Styles'] },
		{ name: 'lang', items: ['BidiLtr', 'BidiRtl', 'Language'] },
		{ name: 'others', items: ['-'] }

	];

	config.resize_enabled = false;
	config.allowedContent = true;
	config.height = 300;
	config.entities_additional = '';
	config.protectedSource.push(/<code>[\s\S]*?<\/code>/gi);
	config.extraPlugins = 'language,menubutton,menu,youtube,tableresize,quicktable,button,panelbutton,panel,floatpanel,widget,lineutils';

	config.language_list = [
		'ar:Arabic:rtl',
		'bg:Bulgarian',
		'cs:Czech',
		'de:German',
		'el:Greek',
		'en:English',
		'es:Spanish',
		'fi:Finnish',
		'fr:French',
		'hr:Croatian',
		'hu:Hungarian',
		'id:Indonesian',
		'it:Italian',
		'lt:Lithuanian',
		'nl:Dutch',
		'pt-br:Portuguese (Brazil)',
		'pt:Portuguese (Portugal)',
		'ru:Russian',
		'sv:Swedish',
		'tr:Turkish',
	];
}
