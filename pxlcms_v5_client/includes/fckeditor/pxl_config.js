FCKConfig.AutoDetectLanguage = false ;
FCKConfig.DefaultLanguage = "en" ;
FCKConfig.EnterMode = "br" ;
FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/office2003/' ;
FCKConfig.LinkUpload = false ;
FCKConfig.ImageBrowser = false ;
FCKConfig.FlashBrowser = false ;
FCKConfig.ImageUpload = false ;
FCKConfig.FlashUpload = false ;
FCKConfig.LinkBrowser = false ;
FCKConfig.ForcePasteAsPlainText	= true ;
FCKConfig.ToolbarCanCollapse = false ;

FCKConfig.ToolbarSets["PXL"] = [
	['Cut','Copy','Paste'],
	['Undo','Redo'],
	['Bold','Italic','Underline','StrikeThrough'],
	['TextColor','FontName','FontSize'],
	['OrderedList','UnorderedList'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Link','Unlink'],
	['SpecialChar','-','FitWindow']
] ;

FCKConfig.ToolbarSets["PXL_Admin"] = [
	['Source','Cut','Copy','Paste','PasteText','PasteWord'],
	['Undo','Redo','-','Find','Replace','-','RemoveFormat'],
	['Bold','Italic','Underline','StrikeThrough'],
	['FontFormat', 'TextColor','FontName','FontSize'],
	['OrderedList','UnorderedList'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Link','Unlink','Anchor'],
	['SpecialChar'],
	['ShowBlocks','FitWindow']
] ;