<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);

$config = array();

$config['connection'] = array(
        'host' => 'localhost',
        'user' => '***',
        'password' => '***',
        'database' => 'vpa_portal',
        'prefix' => '', /* Még nem működik */
    );

$config['mail'] = array(
        'sender' => 'miserend.hu <info@miserend.hu>',
        'debug' => 0, /* 0,1,2,3 */
        'debugger' => 'eleklaszlosj@gmail.com'
    );

$config['path']['domain'] = '';

function dbconnect() {
    global $hiba,$hibauzenet,$hibauzenet_prog,$db_name,$modul_url,$fuggveny_url,$design_url,$nyitomodul;
    
    $modul_url='moduls';
    $nyitomodul=1;

    global $config;
    $db_host = $config['connection']['host'];
    $db_uname = $config['connection']['user'];
    $db_upass = $config['connection']['password'];
    $db_name = $config['connection']['database'];

    if(!@mysql_connect($db_host, $db_uname, $db_upass)) {
    
        $hiba=true;
        $hibauzenet.='Elnézést kérünk, a szolgáltatás jelenleg nem érhető el.';
        $idopont=date('Y.m.d. H:i.s');
        $hibauzenet_prog.="Adatbázisszerverhez nem lehet csatlakozni!\n".mysql_error()."\n$idopont";
    }
    
    mysql_query("SET NAMES UTF8");
    
    if(!mysql_select_db($db_name)) {
        $hiba=true;
        $hibauzenet.='Elnézést kérünk, a szolgáltatás jelenleg nem érhető el.';
        $idopont=date('Y.m.d. H:i.s');
        $hibauzenet_prog.="Az '".$db_name."' adatbázis nem létezik, vagy nincs megfelelő jogosultság elérni azt!\n".mysql_error()."\n$idopont";
    }
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
