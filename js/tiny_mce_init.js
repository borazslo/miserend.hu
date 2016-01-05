tinymce.init({
    selector: ".tinymce",
    mode: "inline",
    elements: "leiras,misemegj,megjegyzes,plebania",
    theme: "modern",
    relative_urls: true,
    plugins: "preview,paste,link,code",
    toolbar1: "bold italic | link unlink | preview code",
    menubar: false,
    statusbar: false,
    toolbar_items_size: 'small',
    elementpath: false,
    paste_create_paragraphs: true,    
    forced_root_block : false,
    extended_valid_elements: "a[href|target|title|class],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|name],hr[class|width|size|noshade],font[size|color|style],span[class|align|style]"
});