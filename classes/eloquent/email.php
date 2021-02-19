<?php

namespace Eloquent;

class Email extends \Illuminate\Database\Eloquent\Model {
    
    public $debug;
    public $debugger;
    
    
    function addToQueue() {
        $this->status = 'queued';
        return $this->save();        
    }
    
    function send($to = false) {
        if($to) $this->to = $to;
                        
        $this->status = 'sending';
        $this->save();
        /* mail() code */
        
        global $config;
        $this->debug = $config['mail']['debug'];
        $this->debugger = $config['mail']['debugger'];
        
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
                if (!mail($this->to, $this->subject, $this->body, $this->header)) {
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
        
        $this->header = 'MIME-Version: 1.0' . "\r\n";
        $this->header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $this->header .= 'From: ' . $config['mail']['sender'] . "\r\n";        
    }
    
    function render($twigfile, $array) {
        global $twig;

        if(method_exists($array, 'toArray')) 
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
    
}
