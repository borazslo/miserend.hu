<?php
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
			$this->to = array($this->debugger);
		}

		if(isset($this->subject) AND isset($this->content) AND isset($this->to)) { 
			if(!is_array($this->to)) $this->to = array($this->to);
			if($this->debug == 3) { print_r($this); }
			else {
				if(!mail(implode(',',$this->to),$this->subject,$this->content,$this->header))
					echo 'Valami hivba tortent ay email kuldes kozen. Kar';
			}
		} else {
			echo 'Nem tudtuk elkuldeni a mailt. Keves az adat.';
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
		$mail->subject = "Miserend - észrevétel érkezett";

		switch ($type) {
			case 'diocese':
				$mail->content = "Kedves egyházmegyei felelős!\n\n<br/><br/>Az egyházmegyéhez tartozó egyik templom adataihoz észrevétel érkezett.<br/>\n";
				$mail->content .= $this->PreparedText4Email;
			break;
			
			case 'contact':
				$mail->content = "Kedves templom karbantartó!\n\n<br/><br/>Az egyik karbantartott templomod adataihoz észrevétel érkezett.<br/>\n";
				$mail->content .= $this->PreparedText4Email;
			break;

			case 'debug':
				$mail->content = "Kedves admin!\n\n<br/><br/>Az egyik templom adataihoz észrevétel érkezett.<br/>\n";
				$mail->content .= $this->PreparedText4Email;
			break;

		}

		$mail->to = $to;
		$mail->send();

	}
} // END class 

?>
