<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);

function dbconnect() {
    global $hiba,$hibauzenet,$hibauzenet_prog,$db_name,$modul_url,$fuggveny_url,$design_url,$nyitomodul;
    
    $modul_url='moduls';
    $nyitomodul=1;

    $db_host = "localhost";
    $db_uname = "***";
    $db_upass = "***";
    $db_name = "vpa_portal";

    if(!@mysql_connect($db_host, $db_uname, $db_upass)) {
    
        $hiba=true;
        $hibauzenet.='Elnézést kérünk, a szolgáltatás jelenleg nem érhetõ el.';
        $idopont=date('Y.m.d. H:i.s');
        $hibauzenet_prog.="Adatbázisszerverhez nem lehet csatlakozni!\n".mysql_error()."\n$idopont";
    }
    
    mysql_query("SET NAMES latin2");
    
    mysql_select_db($db_name);
}

function kicsinyites($forras,$kimenet,$max) {

			if(!isset($max)) $max=120;    # maximum size of 1 side of the picture.

			$src_img=ImagecreateFromJpeg($forras);

			$oh = imagesy($src_img);  # original height
			$ow = imagesx($src_img);  # original width

			$new_h = $oh;
			$new_w = $ow;

			if($oh > $max || $ow > $max){
		       $r = $oh/$ow;
		       $new_h = ($oh > $ow) ? $max : $max*$r;
		       $new_w = $new_h/$r;
			}

			// note TrueColor does 256 and not.. 8
			$dst_img = ImageCreateTrueColor($new_w,$new_h);
			/*imageantialias($dst_img, true);*/

/*			ImageCopyResized($dst_img, $src_img, 0,0,0,0, $new_w, $new_h, ImageSX($src_img), ImageSY($src_img));*/
			ImageCopyResampled($dst_img, $src_img, 0,0,0,0, $new_w, $new_h, ImageSX($src_img), ImageSY($src_img));
			ImageJpeg($dst_img, "$kimenet");
}

?>
