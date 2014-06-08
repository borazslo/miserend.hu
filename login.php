<?

function belepell() {
    global $hiba,$loginhiba,$hibauzenet,$hibauzenet_prog,$fooldal_id,$db_name,$m_zart,$m_id,$sid,$_GET,$_POST,$_COOKIE;
	
	$last=date('Y-m-d H:i:s');
    $most=time();
    $belep=false;
    
   // $sid=urlencode($sid);
    
    $query="select uid,login,becenev,jogok,varos,baratok,ismerosok,lejarat,szavazott,lastlogin,ip_tiltas,ip_beleptilt from session where sessid='$sid' order by lastlogin desc";
    if(!$lekerdez=mysql_query($query)) {
        $hiba=true;
        $hibauzenet.='';
        $hibauzenet_prog.='HIBA a beléptetésnél (adatbázis ellenőrzés) - login.php [#12]<br>'.mysql_error();
    }

	list($u_id,$u_login,$u_becenev,$u_jogok,$u_varos,$u_baratok,$u_ismerosok,$u_limit,$u_szavazott,$u_lastlogin,$u_ip_tiltas,$u_ip_beleptilt)=@mysql_fetch_row($lekerdez);
    
	if($u_limit<0) $u_limit=2400; //Ha nincs beállítva (negatív érték), akkor 40 perc
    if($u_limit>0) $lejart=$most-$limit; //Ha nagyobb, akkor azt vesszük figyelembe
	else $lejart=0; //Ha nulla, akkor sosem jár le

	//Ha van loginje:
    if($u_id>0 and $u_login!='*vendeg*') {
        //Még nem járt le az azonosítója
        if($u_lastlogin>$lejart) {
            $belep=true;
            $query="update session set lastlogin='$most', last='$last', modul_id='$m_id', fooldal='$fooldal_id' where sessid='$sid'";                
			mysql_query($query);
			//$query="update user set lastlogin='$last' where uid='$u_id'";
	        //@mysql_query($query);

			//if($m_id>0) @mysql_query("update modulok set szamlalo=szamlalo+1 where id='$m_id'");				
				
        }
        else {
            $loginhiba='Lépj be<br>újra!';
        }

    }
    //Ha vendég -> még nem lépett be
    else {
        $query="update session set lastlogin='$most', last='$last', modul_id='$m_id', fooldal='$fooldal_id' where sessid='$sid'";
        mysql_query($query);
        //if($m_id>0) @mysql_query("update modulok set szamlalo=szamlalo+1 where id='$m_id'");
    }

 
	$belepT[0]=$belep;
    $belepT[1]=$u_id;
    $belepT[2]=$u_login;
    $belepT[3]=$u_jogok;
    $belepT[4]=$hibauzenet;
	$belepT[5]=$u_szavazott;

	$belepT[8]=$u_ip_tiltas;
	$belepT[9]=$u_ip_beleptilt;
	$belepT[12]=$u_varos;
	$belepT[13]=$u_becenev;
	$belepT[14]=$u_baratok;
	$belepT[15]=$u_ismerosok;

    //Ha a megadott sessid valamiért nem szerepel az adatbázisban, akkor felvesszük
    if(@mysql_num_rows($lekerdez)==0) {
        $belepT=ujvendeg();
    }
    
    return $belepT;
}

function beleptet() {
    global $_POST,$_SERVER,$hiba,$loginhiba,$hibauzenet,$hibauzenet_prog,$db_name,$m_zart,$sid,$m_id,$_GET;

	$ip=$_SERVER['REMOTE_ADDR'];
    $host = gethostbyaddr($ip);

    $belep=false;
    $u_login=$_POST['login'];
    $u_passw=base64_encode($_POST['passw']);
    
    $query="select uid, becenev, jogok, varos, nem, szuldatum, nyilvanos, ismerosok, baratok, atvett from user where login='$u_login' and jelszo='$u_passw' and ok!='n'";
    if(!$lekerdez=@mysql_query($query)) {
        $hiba=true;
        $hibauzenet.='';
        $hibauzenet_prog.='HIBA a beléptetésnél (adatbázis ellenőrzés) - login.php [#75]<br>'.mysql_error();
    }
    if(@mysql_num_rows($lekerdez)>0) { //Van ilyen felhasználó, helyesek voltak az adatok
        $most=time();
		$mostd=date('Y-m-d H:i:s');
        $belep=true;
        list($u_id,$u_becenev,$u_jogok,$u_varos,$u_nem, $u_szuldatum, $u_nyilvanos, $u_ismerosok, $u_baratok, $u_atvett)=mysql_fetch_row($lekerdez);

		//Frissítjük a session adatot
		$ma=date('m-d');
		if($ma==substr($u_szuldatum,5,5)) $szulinap=date('Y')-substr($u_szuldatum,0,4);
		else $szulinap=0;
		list($loginok)=mysql_fetch_row(mysql_query("select loginok from session where sessid='$sid'"));
		if(!strstr($loginok,"-$u_login-")) $loginok.="-$u_login-";
        $query="update session set uid='$u_id', login='$u_login', becenev='$u_becenev', jogok='$u_jogok', varos='$u_varos', baratok='$u_baratok', ismerosok='$u_ismerosok', nem='$u_nem', szulinap='$szulinap', nyilvanos='$u_nyilvanos', lastlogin='$most', last='$mostd', modul_id='$m_id', loginok='$loginok' where sessid='$sid'";
        if(!@mysql_query($query)) echo mysql_error();

		$query="update user set lastlogin='$mostd', lastip='$ip ($host)' where login='$u_login' and jelszo='$u_passw' and ok!='n'";
		if(!$lekerdez=@mysql_query($query)) {
			$hiba=true;
	        $hibauzenet.='';
		    $hibauzenet_prog.='HIBA a beléptetésnél (adatbázis ellenőrzés) - login.php [#75]<br>'.mysql_error();
		}

		//Lekérdezzük, az IP tiltás adatait
		$query="select ip_tiltas,ip_beleptilt from session where sessid='$sid'";
		if(!$lekerdez=@mysql_query($query)) {
			$hiba=true;
			$hibauzenet.='';
			$hibauzenet_prog.='HIBA az iptiltás lekérdezésnél (session tábla) - login.php [#96]<br>'.mysql_error();
		}
		list($ipT[0],$ipT[1])=mysql_fetch_row($lekerdez);

		//if($m_id>0) @mysql_query("update modulok set szamlalo=szamlalo+1 where id='$m_id'");
       
        $limit=172800; //2 nap
        $reglejart=$most-$limit;
		//A régi sessionöket töröljük
        $query="delete from session where (uid='$u_id' and login='$u_login' and sessid!='$sid') or lastlogin<='$reglejart'";
        @mysql_query($query);
    }
    else {
		//Nincs ilyen felhasználó, valamit elírhattak a belépésnél
        $belep=false;
        $loginhiba='Hibás jelszó!';
    }

    $belepT[0]=$belep;
    $belepT[1]=$u_id;
    $belepT[2]=$u_login;
    $belepT[3]=$u_jogok;
    $belepT[4]=$hibauzenet;
	$belepT[8]=$ipT[0]; //ip_tiltas
	$belepT[9]=$ipT[1]; //ip_beleptilt
	$belepT[12]=$u_varos;
	$belepT[13]=$u_becenev;
	$belepT[14]=$u_baratok;
	$belepT[15]=$u_ismerosok;
	$belepT[20]=$u_atvett;
    
    return $belepT;
}

function addsessid($u_id,$u_login) {
    //Sessid létrehozása

    $betu1=$u_login[0];
    $datum=time();
	$szamveg=substr($datum,-4);
    $szam1=mt_rand(10101010,99999999);
    $szam2=$szam1 ^ 12435687;
    $sessid = $betu1 . $szam1 . $datum . $szam2;
    $sessid=base64_encode($sessid).$szamveg;
    $sessid=urlencode($sessid);

    return $sessid;
}

function ellsessid($u_sessid,$u_id) {
    global $db_name;
    $sessid_ok=false;
    //Ellenőrzés, hogy van-e már ilyen sessid
    $query="select uid from session where sessid='$u_sessid'";
    if(!@$lekerdez=mysql_query($query)) {
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
    global $sid,$db_name,$u_login,$m_id;
    $most=time();
	$last=date('Y-m-d H:i:s');
    
    //$sessid=urlencode($sessid);
    list($oldlogin)=mysql_fetch_row(mysql_query("select login from session where sessid='$sid'"));
    $query="update session set uid=0, oldlogin='$oldlogin', login='*vendeg*', jogok='', lastlogin='$most', last='$last', modul_id='$m_id' where sessid='$sid'";
    if(!mysql_query($query)) {
        $hiba=true;
        $hibauzenet.='';
        $hibauzenet_prog.='HIBA a kiléptetésnél (sessid törlés) - login.php [#164]<br>'.mysql_error();
    }
}

function ujvendeg() {
    global $m_id,$db_name,$_SERVER,$fooldal_id,$sid;
	$ip=$_SERVER['REMOTE_ADDR'];
    $host = gethostbyaddr($ip);

	$ipT=ip_ell();
	
	if(!strstr($host,'googlebot')) { //robotnál nem csináljuk!

		if(empty($sid)) {
			$session_ok=false;
	    	do {
			    $u_sessid=addsessid(0,'*vendeg*');
	    	}
			while(!$session_ok=ellsessid($u_sessid,0));
		}
		else {
			$u_sessid=$sid;
		}
		$most=time();
		$last=date('Y-m-d H:i:s');
		$query="insert session set uid=0, login='*vendeg*', jogok='', lastlogin='$most', last='$last', fooldal='$fooldal_id', modul_id='$m_id', host='$host', ip='$ip', ip_tiltas='$ipT[0]', ip_beleptilt='$ipT[1]', sessid='$u_sessid'";
    		mysql_query($query);
	}
	$belepT[0]=false; //belep
    	$belepT[1]='0'; //u_id
    	$belepT[2]='*vendeg*'; //u_login	
    	$belepT[3]=''; //u_jogok
    	$belepT[4]=''; //Hibaüzenet
	$belepT[5]=''; //u_szavazott
	$belepT[6]=''; //u_oldal
	$belepT[7]=''; //u_beosztas
	$belepT[8]=$ipT[0]; //ip_tiltas
	$belepT[9]=$ipT[1]; //ip_beleptilt
	$belepT[10]=$u_sessid; //u_sessid
	$belepT[13]='*vendeg*'; //u_becenev

    return $belepT;
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

function nyelvmeghatarozas() {
	global $modul_url,$linkveg;
//Nyelv meghatározása
    $lang=$_POST['lang'];
    if(!isset($lang)) $lang=$_GET['lang'];
    if($lang=='hu') $lang='';
        
    if(!@include_once("$modul_url/szotar/alapszotar$lang.inc")) {
        $hiba=true;
        $hibauzenet_prog.='<br>Sorry, not translated this language!';
    }

	if(!empty($lang)) {
		$linkveg.="&lang=$lang";
	}
}


?>
