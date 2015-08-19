<?php
// ez az egész rész a TinyMCE miatt kell

    $tinymce_host = $_SERVER['SERVER_NAME'];
    $edit_script="\n";

    $edit_script.='<script language="javascript" type="text/javascript" src="js/tiny_mce/tiny_mce.js"></script>'."\n";
    $edit_script.='<script language="javascript" type="text/javascript">'."\n";
    $edit_script.='   tinyMCE.init({'."\n";
    $edit_script.='	language : "hu",'."\n";
//    $edit_script.='	mode : "textareas",'."\n";
//	$edit_script.=' textarea_trigger : "szoveg",'."\n";
	$edit_script.=' mode : "exact",'."\n";
	$edit_script.='	elements : "szoveg",'."\n";
    $edit_script.='	theme : "advanced",'."\n";
	$edit_script.=' content_css : "img/style.css",'."\n";
    $edit_script.='	remove_script_host : false,'."\n";
    $edit_script.=' relative_urls : true,'."\n";

//    $edit_script.='      urlconvertor_callback: "convLinkVC",'."\n";
    
	$edit_script.='	plugins : "table,save,advhr,advimage,advlink,emotions,iespell,preview,zoom,searchreplace,print,paste,fullscreen,noneditable,contextmenu",'."\n";
	$edit_script.=' theme_advanced_styles : "alap=alap;alapkizárt=alapkizart;kicsi=kicsi;kiscim=kiscim;kozepescim=kozepescim;alcim=alcim;link=link;menülink=menulink;rovatcikklink=rovatcikklink",'."\n";

	$edit_script.='	theme_advanced_buttons1 : "styleselect,bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,link,unlink,separator,image,bullist,numlist,outdent,indent,separator",'."\n";    
	$edit_script.='	theme_advanced_buttons2 : "search,replace,separator,forecolor,backcolor,separator,cut,copy,pastetext,pasteword,selectall,separator,undo,redo,cleanup,spearator,preview,code,separatorprint,zoom,fullscreen,separator",'."\n";
	$edit_script.='	theme_advanced_buttons3_add_before : "tablecontrols,separator",'."\n";
    
	$edit_script.='	theme_advanced_toolbar_align : "left",'."\n";
	$edit_script.='	theme_advanced_toolbar_location : "top",'."\n";
	$edit_script.='	theme_advanced_statusbar_location : "bottom",'."\n";

    //$edit_script.='	theme_advanced_buttons1 : "styleselect,bold,italic,underline,separator,justifyleft,justifyfull,justifyright,separator,link,unlink,separator,image,bullist,separator",'."\n";    
//	$edit_script.=' theme_advanced_buttons2 : "cut,copy,pastetext,pasteword,selectall,separator,undo,redo,cleanup,spearator,preview,separator,code,separator",'."\n";
//	$edit_script.='	theme_advanced_buttons3_add_before : "tablecontrols,separator",'."\n";
    
//	$edit_script.='	theme_advanced_toolbar_align : "left",'."\n";
    $edit_script.='	plugin_insertdate_dateFormat : "%Y. %m. %d.",'."\n";
    $edit_script.='	plugin_insertdate_timeFormat : "%H:%M:%S",'."\n";
    $edit_script.=' paste_create_paragraphs : false,'."\n";
    $edit_script.='	paste_use_dialog : true,'."\n";
    $edit_script.='	external_link_list_url : "jscripts/examples/example_link_list.js",'."\n";
    $edit_script.='	external_image_list_url : "jscripts/examples/example_image_list.js",'."\n";
    $edit_script.='	extended_valid_elements : "a[href|target|title|class],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|name],hr[class|width|size|noshade],font[size|color|style],span[class|align|style]"'."\n";
    $edit_script.='	});'."\n";
    $edit_script.="\n";   
	$edit_script.='	</script>';


// idáig

Return $edit_script;

?>