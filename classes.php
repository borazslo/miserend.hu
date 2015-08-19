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
		if(!isset($uid) OR !is_numeric($uid)) {
			$this->loggedin = false;
			$this->uid = 0;
			$this->username = '*vendeg*';
			$this->nickname = '*vendeg*';
		} else {
			$query = "SELECT * FROM user WHERE uid = $uid AND ok = 'i' LIMIT 1";
			$result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if(is_array($x)) {
            	foreach ($x as $key => $value) {
            		$this->$key = $value;
            	}
            	$this->loggedin = true;
				$this->username = $x['login'];
				$this->nickname = $x['becenev'];
				$this->jogok = trim($this->jogok," \t\n\r\0\x0B-");
				$this->roles = explode('-',$this->jogok);
            } else {
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
	function __construct($tid) {
		global $user;
		$this->name = "vendég";
		$this->username = $user->username;
		//email fakultatív
		//megbízható?? reliable
		$this->timestamp = date('Y-m-d H:i:s');
		$this->tid = $tid;
		$this->state = 'u';
		$this->text = "";
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
