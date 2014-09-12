<?php

function url_ell() {
    // domain: $SERVER_NAME
    // a domain utáni URL: $REQUEST_URI
	$fooldal_id=$_REQUEST['fooldal_id'];
    if(!empty($fooldal_id)) {
        //Átugrasztjuk arra a főoldalra
        $query="select domain,ugras from fooldal where id='$fooldal_id'";
        $lekerdez=mysql_query($query);
        list($domain,$ugras)=mysql_fetch_row($lekerdez);
        if(!empty($ugras)) $ujlink=$ugras;
        else $ujlink=$domain;
        
        if(isset($_REQUEST['fooldal_id']))
            foreach($_REQUEST as $kulcs=>$ertek) {
                if($kulcs!='fooldal_id') $parameterT[]="$kulcs=$ertek";
            }
        if(is_array($parameterT)) $parameterek=implode('&',$parameterT);
        
        header("Location: http://$ujlink?$parameterek");
    }   

}


function modul_ell($fooldal_id,$aldomain) {
//Behívandó modulok ellenőrzése
/////////////////////////////////
// fooldal_modulok tábla:
// id int(3) auto_increment primary
// fooldal_id int(2) [Melyik főoldalhoz tartozik]
// hova enum(f,b,t,j,l) [Hova kerül - fejléc, balmenü, tartalom, jobbmenü, lábléc]
// tipus enum(a,d) [Típusa - állandó (a) vagy alapértelmezett (d), melyet az egyes oldalak átírhatnak.
// sorrend int(4) [Ha több modul is behívásra kerül, itt lehet beállítani a sorrendet]
// modul int(3) [modul kódja]
/////////////////////////////////
// A fent behívott alapértelmezett modulokat az url paraméterei módosíthatják!

    global $hiba, $hibauzenet, $hibauzenet_prog, $html_kod;
    
    $query="select hova,tipus,sorrend,modul from fooldal_modulok where fooldal_id='$fooldal_id' order by hova, sorrend";
    if(!$lekerdez=mysql_query($query)) {
        //Ha a lekérdezés nem sikerült...
        $hiba=true;
        $hibauzenet.='';
        $hibauzenet_prog.="\n\nHIBA az adatbázis lekérdezésnél (ell.inc #125 [modul_ell]):\n" . mysql_error();
    }
    else {
        while(list($fm_hova,$fm_tipus,$fm_sorrend,$fm_modul)=mysql_fetch_row($lekerdez)) {
            if($fm_hova=='f') $modulList['f'][]=$fm_modul;
            elseif($fm_hova=='b') $modulList['b'][]=$fm_modul;
            elseif($fm_hova=='t') $modulList['b'][]=$fm_modul;
            elseif($fm_hova=='j') $modulList['b'][]=$fm_modul;
            elseif($fm_hova=='l') $modulList['b'][]=$fm_modul;
        }
        //Itt még ellenőrizni kell, hogy az URL adatai alapján változik-e valami!
        return $modulList;
    }
}

function ip_ell() {
//IP ellenőrzés
//////////////////////////////
// Tábla: IP_tiltas
// id int(3), auto_increment, primary
// ip varchar(20)
// ig datetime [Ameddig a letiltás érvényes]
// fooldalak varchar(50) [Amit nem láthat]
// belepfooldalak varchar(50) [Ahova nem léphet be]
// megj tinytext [Ide bármit lehet írni, hogy kit zártunk ki, miért, stb.]
//////////////////////////////

    global $REMOTE_ADDR, $hiba, $hibauzenet, $hibauzenet_prog, $db_name;
    
    $user_IP=$REMOTE_ADDR; //felhasználó IP címe
    $most=date('Y-m-d H:i:s');

    $query="select fooldalak, belepfooldalak from IP_tiltas where ip='$user_IP' and (ig>='$most' or ig=0)";
    if(!$lekerdez=mysql_query($query)) {
        //Ha a lekérdezés nem sikerült...
        $hiba=true;
        $hibauzenet.='';
        $hibauzenet_prog.="\n\nHIBA az adatbázis lekérdezésnél (login.php #206 [ip_ell]):\n" . mysql_error();
    }
    else {
        list($fooldalak,$belepfooldalak)=mysql_fetch_row($lekerdez);
        $ipT=array($fooldalak,$belepfooldalak);
    }
    
    return $ipT;
}

?>
