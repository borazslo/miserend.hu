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
				$this->roles = explode('-',trim($this->jogok," \t\n\r\0\x0B-"));
            } else {
            	//TODO: kitalálni mit csináljon, hogy nincs uid-jű user. Legyen vendég?
            	// There is no user with this uid;
            	return false;
            }
		}
	}

	function checkRole($role = false) {
		if($role == false) return true;
		
		if($role == '"any"' OR $role = "'any'") {
			if(trim(preg_replace('/-/i', '', $this->jogok))  != '' ) return true;
			else return false;
		}

		if(preg_match('/(^|-)'.$role.'(-|$)/i',$this->jogok)) return true;
		else return false;
	}


	function submit($vars) {
		if(isset($vars['uid']) AND !is_numeric($vars['uid']) AND $vars['uid'] != '')
			return array("Ilyen felhasználónk biztosan nincs!");
	
		foreach(array('nev','becenev','email','orszag','varos','magamrol','foglalkozas','skype','msn','ok','jogok','kontakt','volunteer') as $key) {
			if(isset($vars[$key])) {
				if(!$this->presave($key,$vars[$key])) {
					$hiba[] = "Gond van a <i>".$key."</i> mezővel.";
				}
			}
		}
		if($this->id == 0) {
			if(!$this->presave('username',$vars['ulogin'])) {
					$hiba[] = "Gond van a <i>username</i> mezővel.";
			}
		}
		//TODO: a formban ujelszo helyett valami rendes
		if(!empty($vars['ujelszo'])) {
			if($vars['ujelszo'] == $vars['ujelszo1']) {
				if(!$this->presave('password',$vars['ujelszo'])) {
					$hiba[] = "Gond van a <i>jelszó</i> mezővel.";
				}
			}
			else $hiba[] ='HIBA! A beírt két jelszó nem egyezik!';
		}
		
		if(!$this->save()) $hiba[] = "Nem sikerült elmenteni. Pedig minden rendben volt előtte.";

		if($hiba) return $hiba;	

		return 1;
	}

	function presave($key,$val) {
		if(!isset($this->presaved)) $this->presaved = array();

		//TODO: check duplicate for: logn + email

		//TODO: van, amit ne engedjen, csak, amikor még tök új a cuccos.
		//TODO: törölhető oszlop: ismerosok, baratok, regip, lastip, log, adminmegj,atvett

		//TODO: a nickname - becenev esetén ez nem segít, bár nem sok dupla munka azért
		if($this->$key == $val) return true;
		
		if($val == '' AND !in_array($key,array('username','login'))) {
			$this->presaved[$key] = $val;
			return true;
		}
		

		if($key == 'uid') {
				return false;
		} elseif(in_array($key,array('username','login')))  {
				if($this->uid == 0)  {
					if(sanitize($val) == '*vendeg*' OR sanitize($val) == '') return false;
					else $this->presaved['login'] = sanitize($val);				
				} else
					return false;
				
		} elseif(in_array($key,array('jelszo','password')))  {
				$this->presaved['jelszo'] = $this->passwordEncode($val);

		} elseif($key == 'jogok') {
				if(is_array($val)) {
					foreach ($val as $k => $i) $val[$k] = trim(sanitize($i),"-");
					$this->presaved[$key] = implode('-',$val);
				} else 
					$this->presaved[$key] = trim(sanitize($val),"-");
		} elseif($key == 'roles') {
				if(!is_array($val)) $val = array($val);
				foreach ($val as $k => $i) $val[$k] = trim(sanitize($i),"-");
				$this->presaved[$key] = implode('-',$val);
				
		} elseif($key == 'nickname') {
				$this->presaved['becenev'] = sanitize($val);				

		} elseif($key == 'ok') {
				$con = array(1=>'i',0=>'n');
				if(in_array($val,array('i','n'))) $this->presaved[$key] = $val;
				elseif(in_array($val,array(1,0))) $this->presaved[$key] = $con[$val];
				else return false;				
		} elseif($key == 'nem') {
				if(in_array($val,array('0','f','n'))) $this->presaved[$key] = $val;
				else return false;
		} elseif($key == 'volunteer') {
				if(in_array($val,array(0,1))) $this->presaved[$key] = $val;
				else return false;
				
		} elseif(in_array($key,array('letrehozta','orszag','varos'))) {
				//TODO: túlzás lenne megnézni, hogy valódi name-e? (bár ha törölt... user...)
				$this->presaved[$key] = sanitize($val);
			

		} elseif(in_array($key,array('regdatum','lastlogin','lastactive'))) {
				if(strtotime($val))
					$this->presaved[$key] = date('Y-m-d H:i:s',strtotime($val));
				else return false;				
		} elseif($key == 'szuldatum') {
				if(strtotime($val))
					$this->presaved[$key] = date('Y-m-d',strtotime($val));
				else return false;				
		} elseif($key == 'nevnap') {
				if(preg_match('/^([0-9]{1,2})( |-|\.)([0-9]{1,2})(\.|$)/i',$val,$match))
					if(strtotime(date('Y')."-".$match[0].'-'.$match[2]))
						$this->presaved[$key] = date('m-d',strtotime(date('Y')."-".$match[0].'-'.$match[1]));
					else return false;
				else return false;
				

		} elseif($key == 'email') {
				if(!filter_var($val, FILTER_VALIDATE_EMAIL)) return false;
				else $this->presaved[$key] = $val;

			//TODO: lehetne itt még varázsolni
		} elseif(in_array($key,array('becenev','nev','kontakt','msn','skype','foglalkozas','magamrol','vallas'))) {
				$this->presaved[$key] = sanitize($val);				
		} elseif($key == 'csaladiallapot') {
				if(in_array($val,array('titok','egyedülálló','kapcsolatban','házas','elvált','özvegy','pap/szerzetes')))
					$this->presaved[$key] = $val;
				else return false;

		} elseif($key == 'nyilvanos') {
				//TODO: komoly fomrai követelményei vannak!!
				$this->presaved[$key] = sanitize($val);				


		} else
			return false;

		return true;
	}

	function save() {
		if(!$this->presaved) return false;

		//Set Deafult
		if(!isset($this->presaved['ok'])) $this->presaved['ok'] = $this->presave('ok','i');
		if(!isset($this->presaved['regdatum'])) $this->presaved['regdatum'] = $this->presave('regdatum',time());
		global $user;
		if(!isset($this->presaved['letrehozta'])) $this->presaved['regdatum'] = $this->presave('letrehozta',$user->username);


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
		$this->nickname = $this->nickname;
		$this->roles = explode('-',trim($this->jogok," \t\n\r\0\x0B-"));

		unset($this->presaved);

		return $this->uid;
		
	}

	function delete() {
		if($this->uid == 0) return false;

		$query="DELETE FROM user WHERE uid = ".$uid." LIMIT 1";
		if(mysql_query($query)) {
			foreach ($this as $key => $value) unset($this->$key);
			$this->loggedin = false;
			$this->uid = 0;
			$this->username = '*vendeg*';
			$this->nickname = '*vendeg*';
			return true;
		} else
			return false;
	}

	function newPassword($text) {
		$this->presave('password',$text);
		$this->save();
	}

	function passwordEncode($text) {
			return base64_encode($text);
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
				if(!mail(implode(',',$this->to),$this->subject,$this->content,$this->header))
					echo 'Valami hiba történt az email küldése közben. Kár';
			}
		} else {
			echo 'Nem tudtuk elküldeni az emailt. Kevés az adat.';
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
            	$this->tid = $thid->hol_id;
				$this->username = $this->login;
            } else {
            	// TODO: There is no remark with this rid;
            	return false;
            }
		}
	}

	function save() {
		if(!isset($this->reliable)) $this->reliable = '?';

		if($this->username != "*vendeg*") $where = " login = '".$this->username."' ";
		elseif(preg_match("^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$",$this->email)) $where = " email = '".$this->email."' ";
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
		mysql_query($query);

		//TODO: ezt teljesen ki lehetne iktatni
		$query="UPDATE templomok set eszrevetel='i' where id='".$this->tid."' LIMIT 1";
		mysql_query($query);
		
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
} // END class 

?>
