<?php
/**
 * user class
 *
 * @package default
 * @author 
 **/
class User
{
	function __construct($uid = false) {
		if(isset($uid) AND !is_numeric($uid) AND filter_var($uid, FILTER_VALIDATE_EMAIL)) {
			$query = "SELECT * FROM user WHERE email = '".sanitize($uid)."' AND ok = 'i' LIMIT 1";
			$result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if(is_array($x)) {
            	foreach ($x as $key => $value) {
            		$this->$key = $value;
            	}
				$this->username = $x['login'];
				$this->nickname = $x['becenev'];
				$this->name = $x['nev'];
				$this->roles = explode('-',trim($this->jogok," \t\n\r\0\x0B-"));
				$this->getResponsabilities();
				return true;
            } else {
            	//TODO: kitalálni mit csináljon, ha  nincs uid-jű user. Legyen vendég?
            	// There is no user with this uid;
            	$uid = 0;
            	//return false;
            }
		}
		elseif(isset($uid) AND !is_numeric($uid)) {
			$query = "SELECT * FROM user WHERE login = '".sanitize($uid)."' AND ok = 'i' LIMIT 1";
			$result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if(is_array($x)) {
            	foreach ($x as $key => $value) {
            		$this->$key = $value;
            	}
				$this->username = $x['login'];
				$this->nickname = $x['becenev'];
				$this->name = $x['nev'];
				$this->roles = explode('-',trim($this->jogok," \t\n\r\0\x0B-"));
				$this->getResponsabilities();
				return true;
            } else {
            	//TODO: kitalálni mit csináljon, ha  nincs uid-jű user. Legyen vendég?
            	// There is no user with this uid;
            	$uid = 0;
            	//return false;
            }
		}

		if(!isset($uid) OR !is_numeric($uid) OR $uid == 0) {
			$this->loggedin = false;
			$this->uid = 0;
			$this->username = '*vendeg*';
			$this->nickname = '*vendég*';
		} else {
			$query = "SELECT * FROM user WHERE uid = $uid AND ok = 'i' LIMIT 1";
			$result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if(is_array($x)) {
            	foreach ($x as $key => $value) {
            		$this->$key = $value;
            	}
				$this->username = $x['login'];
				$this->nickname = $x['becenev'];
				$this->name = $x['nev'];
				$this->roles = explode('-',trim($this->jogok," \t\n\r\0\x0B-"));
				$this->getResponsabilities();
				return true;
            } else {
            	//TODO: kitalálni mit csináljon, hogy nincs uid-jű user. Legyen vendég?
            	// There is no user with this uid;
            	return false;
            }
		}
	}

	function checkRole($role = false) {
		if($role == false) return true;
		
		if($role == '"any"' OR $role == "'any'") {
			if(trim(preg_replace('/-/i', '', $this->jogok))  != '' ) return true;
			else return false;
		} elseif(preg_match('/^ehm:([0-9]{1,3})$/i',$role,$match) ) {
			$query = "SELECT * FROM egyhazmegye WHERE id = ".$match[1]." AND felelos = '".$this->username."' LIMIT 1";			
			$result = mysql_query($query);
			if(mysql_num_rows($result) == 1) return true;
			else return false;
		} elseif(preg_match('/(^|-)'.$role.'(-|$)/i',$this->jogok)) {
			return true;
		}
		else return false;
	}

	function getResponsabilities() {
		$this->responsible = array(
			'diocese' => array(),
			'church'=> array()
		);
		if($this->uid > 0) {
			$query = "SELECT id FROM egyhazmegye WHERE ok = 'i' AND felelos = '".$this->username."' ";
			$result = mysql_query($query);
			while($ehm = mysql_fetch_assoc($result)) {
				$this->responsible['diocese'][] = $ehm['id'];
			}
			$query = "SELECT id FROM templomok WHERE ok = 'i' AND letrehozta = '".$this->username."' ";
			$result = mysql_query($query);
			while($church = mysql_fetch_assoc($result)) {
				$this->responsible['church'][] = $church['id'];
			}
		}
	}

	function getRemarks($limit = false, $ago = false) {
		if($limit == false OR !is_numeric($limit)) $limit = 5;
		
		if($ago != false) $datum = " AND datum > '".date('Y-m-d H:i:s',strtotime("-".$ago))."' ";
		else $datum = '';

		$query = "SELECT id FROM eszrevetelek WHERE 
				(login = '".$this->username."' OR email = '".$this->email."') 
				".$datum." ORDER BY datum desc";
		$result = mysql_query($query);
		$this->remarksCount = mysql_num_rows($result);
		$query .= " LIMIT ".$limit.";";
		$result = mysql_query($query);
		while($remark = mysql_fetch_assoc($result)) {
			$this->remarks[$remark['id']] = new Remark($remark['id']);
		}
		return true;
	}

	function submit($vars) {
		$return = true;

		if(isset($vars['uid']) AND !is_numeric($vars['uid']) AND $vars['uid'] != '') {
			addMessage('Nincs ilyen felhasználónk!','danger');
			return false;
		}

		$dangers = array(
			'uid' => 'Probléma támadt az azonosítóval!',
			'username' => 'Probléma a felhasználónévvel! (A felhasználó nevet nem lehet megváltoztatni és nem lehet olyan név, ami már használatban van.)',
			'nickname' => 'Probléma a becenévvel!',
			'name' => 'Probléma a névvel!',
			'email' => 'Nem megfelelő email cím!',
			'volunteer' => 'Hibás értéke van az önkéntességnek!',
			'ok' => 'Csak az „i” = „igen” és a „n” = „nem” elfogadható érték az aktivitást illetően!',
			'roles' => 'Hibás formátumú jogkörök!',
			);

		foreach(array('uid','username','nickname','name','email','volunteer','ok','roles') as $input) {
			if(isset($vars[$input])) {
				if(!$this->presave($input,$vars[$input])) {
						$return = false;
						addMessage($dangers[$input],'danger');
				}
			}
		}

		if( $vars['password1'] != '' OR $vars['password2'] != '')  {
			if($vars['password1'] != $vars['password2'] OR $vars['password1'] == '' ) {
				addMessage('A két jelszó nem egyezik meg egymással','danger');
				$return = false;
			}
			else {
				if(!$this->presave('password',$vars['password1'])) {
					$return = false;
					addMessage('Sajnos nem megfelelő a jelszó!','danger');
				}
			}

		} 

		if($return == false) return false;

		if(!$vars['uid']) {	
			$pwd = $this->generatePassword();
			$this->presave('password',$pwd);

			//email küldése
			$email = new Mail();
			$email->subject = 'Regisztráció - Virtuális Plébánia Portál';
			$email->content="Kedves ".$this->username."!<br/><br/>";
			$email->content ="Köszöntünk a Virtuális Plébánia Portál felhasználói között!<br/><br/>";
			$email->content .="\n\nA belépéshez szükséges jelszó: $pwd<br/>";
			$email->content .="\nA belépést követően a BEÁLLÍTÁSOK menüben kérjük megváltoztatni a jelszót.<br><br/>";
			$email->content .="\n\nVPP \nwww.plebania.net";
			$email->to = $this->presaved['email'];
			if($email->send()) addMessage("Elküldtük az emailt az új regisztrációról.","success");
		}
		
		if(!$this->save()) {
			addMessage("Nem sikerült elmenteni. Pedig minden rendben volt előtte.","warning");
			return false;
		} else {
			if(!$vars['uid'])
				addMessage("A felhasználót sikeresen létrehoztuk.","success");
			else 
				addMessage("A változásokat elmentettük.","success");
		}
		return true;
	}

	function presave($key,$val) {
		if(!isset($this->presaved)) $this->presaved = array();
		//TODO: check duplicate for: logn + email

		//TODO: van, amit ne engedjen, csak, amikor még tök új a cuccos.
		//TODO: törölhető oszlop: ismerosok, baratok, regip, lastip, log, adminmegj,atvett

		//TODO: a nickname - becenev / name - nev esetén ez nem segít, bár nem sok dupla munka azért
		//TODO: elrontja ...
		//if($this->$key == $val) return true;


		//TODO: szóljon vissza a kötelező
		if($val == '' AND in_array($key,array('username','login','email'))) {
			return false;
		}

		if($key == 'uid') {
				if($this->uid != $val) 
					return false;
		} elseif(in_array($key,array('username','login')))  {
				if($this->uid == 0)  {
					if(!checkUsername($val)) return false;
					$this->presaved['login'] =  sanitize($val);
				} elseif($this->username != $val ) {
					return false;
				}
				
		} elseif(in_array($key,array('jelszo','password')))  {
				$this->presaved['jelszo'] = password_hash($val,PASSWORD_BCRYPT);

		} elseif($key == 'roles' OR $key == 'jogok') {
				if(!is_array($val)) $val = array($val);
				foreach ($val as $k => $i) $val['jogok'] = trim(sanitize($i),"-");
				$val = array_unique($val);
				$this->presaved['jogok'] = implode('-',$val);
				
		} elseif($key == 'nickname' or $key == 'becenev') {
				$this->presaved['becenev'] = sanitize($val);				

		} elseif($key == 'name' or $key == 'nev') {
				$this->presaved['nev'] = sanitize($val);				

		} elseif($key == 'ok') {
				$con = array(1=>'i',0=>'n');
				if($val == '') $this->presaved[$key] = 'n';
				elseif(in_array($val,array('i','n'))) $this->presaved[$key] = $val;
				elseif(in_array($val,array(1,0))) $this->presaved[$key] = $con[$val];
				
				else return false;	

		} elseif($key == 'volunteer') {
				if($val == '') $this->presaved[$key] = 0;
				elseif(in_array($val,array(0,1))) $this->presaved[$key] = $val;
				else return false;
				
		} elseif(in_array($key,array('letrehozta'))) {
				//TODO: túlzás lenne megnézni, hogy valódi name-e? (bár ha törölt... user...)
				$this->presaved[$key] = sanitize($val);

		} elseif(in_array($key,array('regdatum','lastlogin','lastactive'))) {				
				if(is_numeric($val)) {
					$this->presaved[$key] = date('Y-m-d H:i:s',$val);
				} elseif(strtotime($val))
					$this->presaved[$key] = date('Y-m-d H:i:s',strtotime($val));
				else return false;				

		} elseif($key == 'email') {
				if(!filter_var($val, FILTER_VALIDATE_EMAIL)) return false;

				//TODO: dupla email címeket kiszűrni
				$this->presaved[$key] = $val;			

		} else
			return false;

		return true;
	}

	function save() {
		if(!$this->presaved) return false;

		//Set Deafult
		if($this->uid < 1) {
			if(!isset($this->presaved['ok'])) $this->presave('ok','i');
			if(!isset($this->presaved['regdatum'])) $this->presave('regdatum',time());
			global $user;
			if(!isset($this->presaved['letrehozta'])) $this->presave('letrehozta',$user->username);
		}

		foreach ($this->presaved as $key => $val) {
			$keys[] = $key;
			$vals[] = $val;
			$sets[] = $key.' = "'.$val.'"';
		}

		if($this->uid == 0 AND isset($this->presaved['login'])) {
			$query = "INSERT INTO user (".implode(', ',$keys).") VALUES ('".implode("', '", $vals)."');";
			if(!mysql_query($query)) return false;
			$this->uid = mysql_insert_id();
		} elseif($this->uid > 0 ) {
			$query = "UPDATE user SET ".implode(', ',$sets)." WHERE uid = ".$this->uid." LIMIT 1;";	
			if(!mysql_query($query)) return false;
		} 

		foreach ($this->presaved as $key => $val) $this->$key = $val;

		//TODO: ezt már egyszer leírtam
		$this->username = $this->login;
		$this->nickname = $this->becenev;
		$this->name = $this->nev;
		$this->roles = explode('-',trim($this->jogok," \t\n\r\0\x0B-"));

		unset($this->presaved);

		return $this->uid;
		
	}

	function delete() {
		if($this->uid == 0) return false;

		$query="DELETE FROM user WHERE uid = ".$this->uid." LIMIT 1";
		if(mysql_query($query)) {
			foreach ($this as $key => $value) unset($this->$key);
			$this->loggedin = false;
			$this->uid = 0;
			$this->username = '*vendeg*';
			$this->nickname = '*vendeg*';
			addMessage('Sikeresen töröltük a felhasználót.','success');
			return true;
		} else {
			addMessage('Hiba lépett fel a felhasználó törlése közben','danger');
			return false;
		}
	}

	function generatePassword($length = 8) {
	    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	    $count = mb_strlen($chars);

	    for ($i = 0, $result = ''; $i < $length; $i++) {
	        $index = rand(0, $count - 1);
	        $result .= mb_substr($chars, $index, 1);
	    }

	    return $result;
	}

	function newPassword($text) {
		$this->presave('password',$text);
		$this->save();
	}

	function active() {
		mysql_query("UPDATE user SET lastactive = '".date('Y-m-d H:i:s')."' WHERE uid = ".$this->uid." LIMIT 1;");
	}

	function getFavorites() {
		$favorites = array();
	    $results = mysql_query("
	    	SELECT f.tid,t.nev,t.ismertnev,t.varos FROM favorites f
	    		LEFT JOIN templomok t ON t.id = f.tid
	    	WHERE t.ok = 'i' AND f.tid IS NOT NULL AND f.uid = ".$this->uid."
	    	ORDER BY nev;");
	    while($row=mysql_fetch_row($results,MYSQL_ASSOC)) {
	    	$row['li'] = "<a class='link' href='?templom=".$row['tid']."'>".$row['nev'];
	    	if($row['ismertnev'] != '') $row['li'] .= " (".$row['ismertnev'].")";
	    	$row['li'] .= "</a>, ".$row['varos'];
	    	$favorites[$row['tid']] = $row;
	    }
	    $this->favorites = $favorites;
	    return $favorites;
	}

	function addFavorites($tids) {
		if(!is_array($tids)) $tids = array($tids);
		foreach($tids as $tid) { 
			if(!is_numeric($tid)) return false; 
		}
		foreach($tids as $key => $tid) { 
			if( getChurch($tid) == array() ) unset($tids[$key]); 
		}

		$query = "INSERT IGNORE INTO favorites (uid,tid) VALUES ";
		foreach($tids as $key => $tid) {
			$query .= "(".$this->uid.",".$tid.")";
			if($key < count($tids) -1 ) $query .= ", ";
		}
		$query .= ";";
		if(mysql_query($query)) return true;
		else return false;
	}

	function removeFavorites($tids) {
		if(!is_array($tids)) $tids = array($tids);
		foreach($tids as $tid) { 
			if(!is_numeric($tid)) return false; 
		}

		$query = "DELETE FROM favorites WHERE ";
		foreach($tids as $key => $tid) {
			$query .= "( uid = ".$this->uid." AND tid = ".$tid.")";
			if($key < count($tids) -1 ) $query .= " OR ";
		}
		$query .= ";";
		if(mysql_query($query)) return true;
		else return false;
	}

} // END class 

/**
 * email
 *
 * @package default
 * @author 
 **/
class Mail
{
	function __construct() {
		global $config;
		$this->debug = $config['mail']['debug'];
		$this->debugger = $config['mail']['debugger'];

		$this->header  = 'MIME-Version: 1.0' . "\r\n";
		$this->header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$this->header .= 'From: '.$config['mail']['sender'] . "\r\n";
		$this->type = '';
	}

	function send($to = false) {
		if($to != false) $this->to = $to;

		if($this->debug == 1) {
			$this->header .= 'Bcc: '. $this->debugger . "\r\n";
		} elseif ($this->debug == 2) {
			$this->content .= ".<br/>\n<i>Originally to: ".print_r($this->to,1)."</i>";
			$this->to = array($this->debugger);
		}

		if(isset($this->subject) AND isset($this->content) AND isset($this->to)) { 
			if(!is_array($this->to)) $this->to = array($this->to);
			if($this->debug == 3) { print_r($this); }
			else {
				$query = "INSERT INTO emails (`type`,`to`,`header`,`subject`,`body`,`timestamp`) VALUES ('".$this->type."','".implode(';',$this->to)."','".$this->header."','".$this->subject."','". mysql_real_escape_string($this->content)."','".date('Y-m-d H:i:s')."');";
				if(!mysql_query($query)) 
					addMessage('Nem sikerült elmenteni az emailt.','warning');
				if(!mail(implode(',',$this->to),$this->subject,$this->content,$this->header))
					addMessage('Valami hiba történt az email elküldése közben.','danger');
				else return true;
			}
		} else {
			addMessage('Nem tudtuk elküldeni az emailt. Kevés az adat.','danger');
		}

	}

} // END class 

/**
 * eszrevetel
 *
 * @package miserend
 * @author 
 **/
class Remark
{
	function __construct($rid = false) {
		if(!isset($rid) OR !is_numeric($rid)) {
			global $user;
			$this->name = $user->nev;
			$this->username = $user->username;
			//email fakultatív
			//TODO: megbízható?? reliable
			$this->timestamp = date('Y-m-d H:i:s');
			$this->state = 'u';
			$this->text = "";
		} else {
			$query = "SELECT * FROM  eszrevetelek WHERE id = $rid LIMIT 1";
			$result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if(is_array($x)) {
            	foreach ($x as $key => $value) {
            		$this->$key = $value;
            	}
            	$this->rid = $this->id;
            	$this->tid = $this->hol_id;
				$this->username = $this->login;

				if($this->username != '') {
						$this->user = new User($this->username);
				}

		    	$this->marker['url'] = "javascript:OpenScrollWindow('naplo.php?kod=templomok&id=".$this->hol_id."',550,500);";
				if($this->allapot == 'u') { 
			        $this->marker['text'] = "Új észrevétel!";
			        $this->marker['html'] = "<img src=img/csomag.gif title='".$this->marker['text']."' align=absmiddle border=0> "; 
			        $this->marker['mark'] = 'u';
			    } elseif($this->allapot == 'f') { 
			        $this->marker['text'] = "Észrevétel javítása folyamatban!";
			        $this->marker['html'] = "<img src=img/csomagf.gif title='".$this->marker['text']."' align=absmiddle border=0> "; 			     
			        $this->marker['mark'] = 'f';
			    } elseif($this->allapot == 'j') { 
			        $this->marker['text'] = "Észrevétel feldolgova.";
			        $this->marker['html'] = "<img src=img/csomag1.gif title='".$this->marker['text']."' align=absmiddle border=0> "; 
			        $this->marker['mark'] = 'j';
			    } else {
			        $this->marker['text'] = "Nincsenek állapota";
			        $this->marker['html'] = "<span class='alap' title='".$this->marker['text']."'>(nincs)</span>"; 
			        $this->marker['mark'] = false; 
			    }

				$this->church = getChurch($this->hol_id);

            } else {
            	// TODO: There is no remark with this rid;
            	return false;
            }
		}
	}

	function save() {
		if(!isset($this->reliable)) $this->reliable = '?';

		if($this->username != "*vendeg*") $where = " login = '".$this->username."' ";
		elseif(preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/i",$this->email)) $where = " email = '".$this->email."' ";
		if(isset($where)) {
			$query="SELECT megbizhato FROM eszrevetelek where $where order by datum DESC LIMIT 1";
			$lekerdez=mysql_query($query);
			list($megbizhato)=mysql_fetch_row($lekerdez);
			if(!empty($megbizhato)) $this->reliable=$megbizhato;	
		}
		
		$query="INSERT eszrevetelek set 
			nev='".$this->name."', 
			login='".$this->username."', 
			megbizhato='".$this->reliable."', 
			datum='".$this->timestamp."', 
			hol_id='".$this->tid."', 
			allapot='".$this->state."',
			leiras='".sanitize($this->text)."'";
		if(isset($this->email)) $query .= ", email='".$this->email."'";
		
		if(!mysql_query($query)) return false;

		//TODO: ezt teljesen ki lehetne iktatni
		$query="UPDATE templomok set eszrevetel='i' where id='".$this->tid."' LIMIT 1";

		if(!mysql_query($query)) return false;
		
		return true;
	}


	function emails() {
		global $config;

		$query="select nev,ismertnev,varos,egyhazmegye, kontaktmail from templomok where id = ".$this->tid." limit 0,1";
		$lekerdez=mysql_query($query);
		$templom=mysql_fetch_assoc($lekerdez);
		$eszrevetel.= "<a href=\"http://miserend.hu/?templom=".$this->tid."\">".$templom['nev']." (";
		if($templom['ismertnev'] != "" ) $eszrevetel .= $templom['ismertnev'].", ";
		$eszrevetel .= $templom['varos'].")</a><br/>\n";
		if(isset($this->email))
			$eszrevetel.= "<i><a href=\"mailto:".$this->email."\" target=\"_blank\">".$this->name."</a> (".$this->username."):</i><br/>\n";
		else
			$eszrevetel.= "<i>".$this->name." (".$this->username."):</i><br/>\n";
		$eszrevetel.= $this->text."<br/>\n";
		
		$query="select email from egyhazmegye where id='".$templom['egyhazmegye']."'";
		$lekerdez=mysql_query($query);
		list($felelosmail)=mysql_fetch_row($lekerdez);

		$this->PreparedText4Email = $eszrevetel;

		//Mail küldés az egyházmegyei felelősnek
		if(!empty($felelosmail) AND $felelosmail != '') { $this->SendMail('diocese',$felelosmail);}
		
		//Mail küldés a karbantartónak / felelősnek
		if(!empty($templom['kontaktmail']) AND $templom['kontaktmail'] != '') { $this->SendMail('contact',$templom['kontaktmail']);}

		//Mail küldése a debuggernek, hogy boldog legyen
		$this->SendMail('debug',$config['mail']['debugger']);
		
		return true;
	}

	function sendMail($type,$to) {
		$mail = new Mail();
		if(!isset($this->EmailSubject)) $mail->subject = "Miserend - észrevétel (".$this->tid.")";
		else $mail->subject = $this->EmailSubject;

		switch ($type) {
			case 'diocese':
				$mail->content .= "<strong>Kedves egyházmegyei felelős!</strong>\n\n<br/><br/>Az egyházmegyéhez tartozó egyik templom adataihoz észrevétel érkezett.<br/>\n";
				$mail->content .= "<div style='margin: 5px 5px 5px 20px;background-color:#D1DDE9;padding:4px;'>\n";
				$mail->content .= $this->PreparedText4Email;
			break;
			
			case 'contact':
				$mail->content .= "<strong>Kedves templom karbantartó!</strong>\n\n<br/><br/>Az egyik karbantartott templomod adataihoz észrevétel érkezett.<br/>\n";
				$mail->content .= "<div style='margin: 5px 5px 5px 20px;background-color:#D1DDE9;padding:4px;'>\n";
				$mail->content .= $this->PreparedText4Email;
			break;

			case 'debug':
				$mail->content .= "<strong>Kedves admin!</strong>\n\n<br/><br/>Az egyik templom adataihoz észrevétel érkezett.<br/>\n";
				$mail->content .= "<div style='margin: 5px 5px 5px 20px;background-color:#D1DDE9;padding:4px;'>\n";
				$mail->content .= $this->PreparedText4Email;
			break;

		}

		$mail->content .= "</div>\n";
		$mail->content .= "<strong>Köszönjük a munkádat!</strong><br/>\nVPP";

		$mail->content = "<div style='display: none; visibility: hidden; color: #ffffff; font-size: 0px;'>".$this->PreparedText4Email."\n\n</div>".$mail->content;

		$mail->to = $to;
		$mail->send(); 

	}

	function changeReliability($reliability) {
		if(!in_array($reliability, array('i','n','?','e'))) return false;
		if($this->reliable == $reliability) return true;

		$this->reliable = $reliability;

		//A megbízhatóságot az összes beküldésénél átállítjuk
		// Gyakorlatilag az email az igazi azonosító.
		// TODO: akarunk mit kezdeni az *vendeg* de email nélkül?
		if($this->email != '') {
			$query = "UPDATE eszrevetelek SET megbizhato = '".$reliability."' WHERE email = '".$this->email."' ;";
			mysql_query($query);
		} else
			return false;
	}

	function addComment($text) {
		global $user;
		 $newline = "\n<img src='img/edit.gif' align='absmiddle' title='".$user->username." (".date('Y-m-d H:i:s').")'>".$text; 
		 $query = 'UPDATE eszrevetelek SET adminmegj = CONCAT(IFNULL(adminmegj,""), "'.$newline.'") WHERE id = '.$this->id." LIMIT 1";
		 if(!mysql_query($query)) addMessage("Nem sikerült a megjegyzést bővíteni.",'warning');
	}

} // END class 

?>
