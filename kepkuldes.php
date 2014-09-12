<?php

$header = "
    <html>
    <head>
        <title>VPP - Képbeküldés</title>\n
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n
        <style TYPE=\"text/css\">\n.alap { font-family: Arial, Verdana; font-size: 10pt; text-align: justify; }\n.urlap { font-family: Arial, Verdana;  font-size: 70%; color: #000000; background-color: #FFFFFF; }\n</style>\n
        <link rel='stylesheet' href='templates/upload.css' type='text/css'>\n
        <script src='http://code.jquery.com/jquery-1.10.2.js'></script>\n
        <script type='text/javascript' src='js/jquery.form.js'></script>
        <script type='text/javascript'>
            $(document).ready(function() { 
                var options = { 
                        target:   '#output',   // target element(s) to be updated with server response 
                        beforeSubmit:  beforeSubmit,  // pre-submit callback 
                        success:       afterSuccess,  // post-submit callback 
                        uploadProgress: OnProgress, //upload progress callback 
                        resetForm: true        // reset the form after successful submit 
                    }; 
                    
                 $('#MyUploadForm').submit(function() { 
                        $(this).ajaxSubmit(options);  			
                        // always return false to prevent standard browser submit and page navigation 
                        return false; 
                    }); 
                    

            //function after succesful file upload (when server response)
            function afterSuccess()
            {
                $('#submit-btn').show(); //hide submit button
                $('#loading-img').hide(); //hide submit button
                $('#progressbox').delay( 1000 ).fadeOut(); //hide progress bar

            }

            //function to check file size before uploading.
            function beforeSubmit(){
                //check whether browser fully supports all File API
               if (window.File && window.FileReader && window.FileList && window.Blob)
                {
                    
                    if( !$('#FileInput').val()) //check empty input filed
                    {
                        $('#output').html('Viccelsz?');
                        return false
                    }
                    
                    var fsize = $('#FileInput')[0].files[0].size; //get file size
                    var ftype = $('#FileInput')[0].files[0].type; // get file type
                    

                    //allow file types 
                    switch(ftype)
                    {
                       /* case 'image/png': 
                        case 'image/gif': */
                        case 'image/jpeg': 
                        /*case 'image/pjpeg':
                        case 'text/plain':
                        case 'text/html':
                        case 'application/x-zip-compressed':
                        case 'application/pdf':
                        case 'application/msword':
                        case 'application/vnd.ms-excel':
                        case 'video/mp4':*/
                            break;
                        default:
                            $('#output').html('<b>'+ftype+'</b> Nem támogatott formátum!');
                            return false
                    }
                    
                    //Allowed file size is less than 5 MB (1048576)
                    if(fsize>5242880) 
                    {
                        $('#output').html('<b>'+bytesToSize(fsize) +'</b> Túl nagy a file! <br />A file nem lehet nagyobb mint 5 MB.');
                        return false
                    }
                            
                    $('#submit-btn').hide(); //hide submit button
                    $('#loading-img').show(); //hide submit button
                    $('#output').html('');  
                }
                else
                {
                    //Output error to older unsupported browsers that doesn't support HTML5 File API
                    $('#output').html('Kérlek, frissítsd a böngésződet, hogy teljesíthessük kérésedet!');
                    return false;
                }
            }

            //progress bar function
            function OnProgress(event, position, total, percentComplete)
            {
                //Progress bar
                $('#progressbox').show();
                $('#progressbar').width(percentComplete + '%') //update progressbar percent complete
                $('#statustxt').html(percentComplete + '%'); //update status text
                if(percentComplete>50)
                    {
                        $('#statustxt').css('color','#fff'); //change status text to white after 50%
                    }
            }

            //function to format bites bit.ly/19yoIPO
            function bytesToSize(bytes) {
               var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
               if (bytes == 0) return '0 Bytes';
               var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
               return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
            }

            }); 

            </script>
    </head>\n
    <body bgcolor=\"#FFFFFF\" text=\"#000000\">";

$footer = "</body></html>";

include("load.php");
dbconnect();

function urlap() {
	global $db_name,$header,$footer;

	$sid=$_GET['sid'];
	$id=$_GET['id'];
	$kod=$_GET['kod'];

	if(!is_numeric($id)) {		
		echo $header."<script language=Javascript>close();</script>".$footer;
		exit();
	}

    $query="select nev,ismertnev,varos,egyhazmegye from templomok where id='$id' and ok='i'";
    if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
    list($nev,$ismertnev,$varos,$ehm)=mysql_fetch_row($lekerdez);
    $kiir.="<input type=hidden name=ehm value=$ehm>";
    $kiir.="\n<table width=100% bgcolor=#F5CC4C>
        <tr><td class=alap><big><b>$nev</b> $ismertnev - <u>$varos</u></big><br/>
        <i><strong>Kép feltöltése.</strong> Kérjük kellően jó minőségű és méretű jpeg képet töltsön csak fel.</i></big></td></tr></table>";
        
    $kiir .= '
        <div id="upload-wrapper">
        <div align="center">
        <form action="kepkuldesprocess.php" method="post" enctype="multipart/form-data" id="MyUploadForm">
            <input type=hidden name=kod value='.$kod.'>
            <input type=hidden name=sid value='.$sid.'>
            <input type=hidden name=id value='.$id.'>            
            <span class=alap>Feltöltendő kép: </span><input name="FileInput" id="FileInput" type="file" class=urlap />
            <input type="submit"  id="submit-btn" value="Feltölt" /><br/>
            <span class=alap>Leírás: </span><input type=text size=40 name=description class=urlap />
            <img src="img/ajax-loader.gif" id="loading-img" style="display:none;" alt="Türelem, türelem..."/>
        </form>
        <div id="progressbox" ><div id="progressbar"></div ><div id="statustxt">0%</div></div>
        <div id="output"></div>
        </div>
        </div>
    ';

	echo $header.$kiir.$footer;
}

function bezar() {
	echo $header."<script language=Javascript>close();</script>".$footer;
}

$op=$_POST['op'];
if(empty($op)) $op=$_GET['op'];

switch($op) {
	default:
        urlap();
        break;

	case 'add':
		/*adatadd();*/
        break;
        	
    case 'bezar':
        bezar();
        break;
}


?>