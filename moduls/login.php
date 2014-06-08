<?

function belepell() {
    global $hiba,$hibauezenet,$hibauzenet_prog,$fooldal_id,$db_name,$m_zart,$tiltott_IP_T,$sessid,$m_id,$_GET;
	
	$rovat=$_GET['rovat'];
	if(empty($rovat) and $m_id==1) $rovat=1;

	$last=date('Y-m-d H:i:s');
    $most=time();
    $limit=2400; //40 perc
    $lejart=$most-$limit;
    $belep=false;
    
    $sessid=urlencode($sessid);
    
    $query="select uid,login,jogok,lastlogin,szavazott from session where sessid='$sessid' order by lastlogin desc";
    if(!@$lekerdez=mysql_db_query($db_name,$query)) {
        $hiba=true;
        $hibauzenet.='';
        $hibauzenet_prog.='HIBA a beléptetésnél (adatbázis ellenőrzés) - login.php [#20]<br>'.mysql_error();
    }
    if(!$tiltott_IP_T[1]) {
        list($u_id,$u_login,$u_jogok,$u_lastlogin,$u_szavazott)=@mysql_fetch_row($lekerdez);

        //Ha van loginje:
        if($u_id>0 and $u_login!='*vendeg*') {
            //Még nem járt le az azonosítója
            if($u_lastlogin>$lejart) {
                $belep=true;
                $query="update session set lastlogin='$most', last='$last', modul_id='$m_id', rovat='$rovat' where sessid='$sessid'";                
				@mysql_db_query($db_name,$query);
				$query="update user set lastlogin='$last' where uid='$u_id'";
	            @mysql_db_query($db_name,$query);

				if($m_id>0) @mysql_db_query($db_name,"update modulok set szamlalo=szamlalo+1 where id='$m_id'");
				elseif($rovat>0) @mysql_db_query($db_name,"update rovatok set szamlalo=szamlalo+1 where id='$rovat'");
				
            }
            else {
                $hibauzenet='Az azonosító lejárt. Kérlek lépj be újra!';
            }
        }
        //Ha vendég
        else {
            $query="update session set lastlogin='$most', last='$last', modul_id='$m_id', rovat='$rovat' where sessid='$sessid'";
            @mysql_db_query($db_name,$query);
            if($m_id>0) @mysql_db_query($db_name,"update modulok set szamlalo=szamlalo+1 where id='$m_id'");
			elseif($rovat>0) @mysql_db_query($db_name,"update rovatok set szamlalo=szamlalo+1 where id='$rovat'");		
        }
    }
    //Ha a megadott sessid valamiért nem szerepel az adatbázisban, akkor csinálunk neki újat
    if(@mysql_num_rows($lekerdez)==0) {
        $u_login='*vendeg*';
        $u_jogok='';
        $sessid=ujvendeg();
    }
        

    $belepT[0]=$belep;
    $belepT[1]=$u_id;
    $belepT[2]=$u_login;
    $belepT[3]=$u_jogok;
    $belepT[4]=$hibauzenet;
	$belepT[5]=$u_szavazott;
    
    return $belepT;
}

function beleptet() {
    global $_POST,$hiba,$hibauezenet,$hibauzenet_prog,$db_name,$m_zart,$tiltott_IP_T,$sessid,$m_id,$_GET;

	$rovat=$_GET['rovat'];
	if(empty($rovat) and $m_id==1) $rovat=1;

    $belep=false;
    $u_login=$_POST['login'];
    $u_passw=base64_encode($_POST['passw']);
    
    $query="select uid, jogok from user where login='$u_login' and jelszo='$u_passw' and ok!='n'";
    if(!$lekerdez=@mysql_db_query($db_name,$query)) {
        $hiba=true;
        $hibauzenet.='';
        $hibauzenet_prog.='HIBA a beléptetésnél (adatbázis ellenőrzés) - login.php [#88]<br>'.mysql_error();
    }
    if(@mysql_num_rows($lekerdez)>0 and !$tiltott_IP_T[1]) {
        $most=time();
		$mostd=date('Y-m-d H:i:s');
        $belep=true;
        list($u_id,$u_jogok)=mysql_fetch_row($lekerdez);
/*
        $session_ok=false;
        do {
            $u_sessid=addsessid($u_id,$u_login);
        }
        while(!$session_ok=ellsessid($u_sessid,$u_id));
*/
        $query="update session set uid='$u_id', login='$u_login', jogok='$u_jogok', lastlogin='$most', last='$mostd', modul_id='$m_id', rovat='$rovat' where sessid='$sessid'";
        if(!@mysql_db_query($db_name,$query)) echo mysql_error();
		if($m_id>0) @mysql_db_query($db_name,"update modulok set szamlalo=szamlalo+1 where id='$m_id'");
		elseif($rovat>0) @mysql_db_query($db_name,"update rovatok set szamlalo=szamlalo+1 where id='$rovat'");	

		$query="update user set lastlogin='$mostd' where uid='$u_id'";
        if(!@mysql_db_query($db_name,$query)) echo mysql_error();
        
        $limit=86400; //1 nap
        $reglejart=$most-$limit;
        $query="delete from session where (uid='$u_id' and login='$u_login' and sessid!='$sessid') or lastlogin<='$reglejart'";
        @mysql_db_query($db_name,$query);
    }
    else {
        $belep=false;
        $hibauzenet='Hibás bejelentkezési név, vagy jelszó!';
    }

    $belepT[0]=$belep;
    $belepT[1]=$u_id;
    $belepT[2]=$u_login;
    $belepT[3]=$u_jogok;
    $belepT[4]=$hibauzenet;
    $belepT[5]=$sessid;
    
    return $belepT;
}

function addsessid($u_id,$u_login) {
    //Sessid létrehozása

    $betu1=$u_login[0];
    $datum=time();
    $szam1=mt_rand(10101010,99999999);
    $szam2=$szam1 ^ 12435687;
    $sessid = $betu1 . $szam1 . $datum . $szam2;
    $sessid=base64_encode($sessid);
    $sessid=urlencode($sessid);

    return $sessid;
}

function ellsessid($u_sessid,$u_id) {
    global $db_name;
    $sessid_ok=false;
    //Ellenőrzés, hogy van-e már ilyen sessid
    $query="select uid from session where sessid='$u_sessid'";
    if(!@$lekerdez=mysql_db_query($db_name,$query)) {
        $hiba=true;
        $hibauzenet.='';
        $hibauzenet_prog.='HIBA a beléptetésnél (sessid ellenőrzés) - login.php [#145]<br>'.mysql_error();
    }
    if(@mysql_num_rows($lekerdez)==0) {
        $sessid_ok=true;
    }

    return $sessid_ok;
}

function kilepes() {
    global $sessid,$db_name,$u_login,$m_id;
    $most=time();
	$last=date('Y-m-d H:i:s');
    
    $sessid=urlencode($sessid);
    list($oldlogin)=mysql_fetch_row(mysql_db_query($db_name,"select login from session where sessid='$sessid'"));
    $query="update session set uid=0, oldlogin='$oldlogin', login='*vendeg*', jogok='', lastlogin='$most', last='$last', modul_id='$m_id' where sessid='$sessid'";
    if(!mysql_db_query($db_name,$query)) {
        $hiba=true;
        $hibauzenet.='';
        $hibauzenet_prog.='HIBA a kiléptetésnél (sessid törlés) - login.php [#164]<br>'.mysql_error();
    }
}

function ujvendeg() {
    global $m_id,$db_name,$_SERVER;
    $ip = gethostbyaddr($_SERVER['REMOTE_ADDR']);

    $session_ok=false;
    do {
        $u_sessid=addsessid(0,'*vendeg*');
    }
    while(!$session_ok=ellsessid($u_sessid,0));
        
    $most=time();
	$last=date('Y-m-d H:i:s');
    $query="insert session set uid=0, login='*vendeg*', jogok='', lastlogin='$most', last='$last', modul_id='$m_id', ip='$ip', sessid='$u_sessid'";
    mysql_db_query($db_name,$query);

    return $u_sessid;
}


?>
