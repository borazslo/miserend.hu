tinyMCE.init({
    language: "hu",
    mode: "exact",
    elements: "szoveg",
    theme: "advanced",
    content_css: "img/style.css",
    relative_urls: true,
    plugins: "save,advlink,emotions,iespell,preview,zoom,searchreplace,print,paste,fullscreen,noneditable,contextmenu",
    theme_advanced_buttons1: "bold,italic,separator,link,unlink,separator,preview,code",
    theme_advanced_buttons2: "",
    theme_advanced_buttons3: "",
    theme_advanced_toolbar_align: "left",
    theme_advanced_toolbar_location: "top",
    paste_create_paragraphs: true,
    extended_valid_elements: "a[href|target|title|class],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|name],hr[class|width|size|noshade],font[size|color|style],span[class|align|style]"
});