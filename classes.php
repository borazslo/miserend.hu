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
            } else {
            	// There is no user with this uid;
            	return false;
            }
		}
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

	function send($to = null) {

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
	function __construct() {

	}

	function sendMail($type,$to) {
		$mail = new Mail();
		if(!isset($this->EmailSubject)) $mail->subject = "Miserend - észrevétel";
		else $mail->subject = $this->EmailSubject;

		$mail->content .= "------------------<br/>\n";

		switch ($type) {
			case 'diocese':
				$mail->content .= "Kedves egyházmegyei felelős!\n\n<br/><br/>Az egyházmegyéhez tartozó egyik templom adataihoz észrevétel érkezett.<br/>\n";
				$mail->content .= $this->PreparedText4Email;
			break;
			
			case 'contact':
				$mail->content .= "Kedves templom karbantartó!\n\n<br/><br/>Az egyik karbantartott templomod adataihoz észrevétel érkezett.<br/>\n";
				$mail->content .= $this->PreparedText4Email;
			break;

			case 'debug':
				$mail->content .= "Kedves admin!\n\n<br/><br/>Az egyik templom adataihoz észrevétel érkezett.<br/>\n";
				$mail->content .= $this->PreparedText4Email;
			break;

		}

		$mail->content .= "------------------<br/>\n";
		$mail->content .= "Köszönjük a munkádat!<br/>\nVPP";

		$mail->to = $to;
		$mail->send();

	}
} // END class 

?>
