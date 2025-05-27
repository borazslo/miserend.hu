<?php

namespace Eloquent;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email extends \Illuminate\Database\Eloquent\Model {
    
    public $debug;
    public $debugger;
	
    
    
    function addToQueue($to = false) {
		if($to) $this->to = $to;
        $this->status = 'queued';
        return $this->save();        
    }
    
    function send($to = false) {
        if($to) $this->to = $to;
                        
        $this->status = 'sending';
        $this->save();
        
        if ($this->debug == 1) {
            $this->header .= 'Bcc: ' . $this->debugger . "\r\n";
        } elseif ($this->debug == 2) {
            $this->body .= ".<br/>\n<i>Originally to: " . print_r($this->to, 1) . "</i>";
            $this->to = $this->debugger;
        }

        if (isset($this->subject) AND isset($this->body) AND isset($this->to)) {
            if ($this->debug == 3) {
                print_r($this);
            } else if ($this->debug == 5) {
                // black hole
            } else {
			
				$mail = $this->createMailer(); 

				//$mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
				$mail->addAddress($this->to);               //Name is optional
				//$mail->addReplyTo('info@example.com', 'Information');
				//$mail->addCC('cc@example.com');
				//$mail->addBCC('bcc@example.com');

				//Attachments
				//$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
				//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

				//Content
				$mail->isHTML(true);                                  //Set email format to HTML
				$mail->Subject = $this->subject;
				$mail->Body    = $this->body;
				//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
						
                if (!$mail->send()) {
                    addMessage('Valami hiba történt az email elküldése közben.', 'danger');
                    $this->status = "error";
                    $this->save();
                } else {
                    $this->status = 'sent';
                    return $this->save();
                }
            }
        } else {            
            addMessage('Nem tudtuk elküldeni az emailt. Kevés az adat.', 'danger');
        }
        $this->status = "error";
        $this->save();
        return false;
    }
    
    function __construct() {   
        global $config;
        
        $this->debug = $config['mail']['debug'];
        $this->debugger = $config['mail']['debugger'];
		
        $this->header = 'MIME-Version: 1.0' . "\r\n";
        $this->header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $this->header .= 'From: ' . $config['mail']['sender'][0] . "\r\n";        
    }
    
    function render($twigfile, $array) {
        global $twig;

        if(is_object($array) AND method_exists($array, 'toArray')) 
            $array = $array->toArray();
        
        if(!$this->type)
            $this->type = $twigfile;
                
        $rendered = $twig->render('emails/' . strtolower($twigfile) . '.twig', (array) $array);        
                
        $lines=explode("\n", $rendered);
        if(!$this->subject)
            $this->subject = $lines[0];
        
        unset($lines[0]); unset($lines[1]);
        
        $this->body = implode("\n", $lines);
                
        return true;
    }
	
	function createMailer() {
		$mailer = new PHPMailer(true);
		
		//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
		$mailer->CharSet = "UTF-8";
		$mailer->isSMTP();
		
		global $config;
		if( isset($config['smtp']) ) {
			foreach($config['smtp'] as $key => $value ) {
				$mailer->$key = $value;
			}
		}
		
		$mailer->setFrom($config['mail']['sender'][0],$config['mail']['sender'][1]);		
		
		return $mailer;
	}
	
	function test($content = false) {
        global $user;

		$mailer = $this->createMailer();
		try {
			$connection = $mailer->SmtpConnect();
		} catch(Exception $error) {
			return "PHPMailer Failed to connect : " . $error;
		}
		
		$mailer->addAddress($this->debugger);               
		$mailer->isHTML(true);                                  
		$mailer->Subject = 'miserend.hu - egészség ellenőrzés';
		$mailer->Body    = '';
		if($content) {
            $mailer->Body .= "\n\n" . $content;
        }   

		if(!$mailer->send()) {
			return "Valami hiba történt teszt email kiküldése közben.";
		}
			
		return "OK";
	
	}
    
}
