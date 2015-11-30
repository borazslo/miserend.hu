<?php

class Mail {

    function __construct() {
        global $config;
        $this->debug = $config['mail']['debug'];
        $this->debugger = $config['mail']['debugger'];

        $this->header = 'MIME-Version: 1.0' . "\r\n";
        $this->header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $this->header .= 'From: ' . $config['mail']['sender'] . "\r\n";
        $this->type = '';
    }

    function send($to = false) {
        if ($to != false)
            $this->to = $to;

        if ($this->debug == 1) {
            $this->header .= 'Bcc: ' . $this->debugger . "\r\n";
        } elseif ($this->debug == 2) {
            $this->content .= ".<br/>\n<i>Originally to: " . print_r($this->to, 1) . "</i>";
            $this->to = array($this->debugger);
        }

        if (isset($this->subject) AND isset($this->content) AND isset($this->to)) {
            if (!is_array($this->to))
                $this->to = array($this->to);
            if ($this->debug == 3) {
                print_r($this);
            } else {
                $query = "INSERT INTO emails (`type`,`to`,`header`,`subject`,`body`,`timestamp`) VALUES ('" . $this->type . "','" . implode(';', $this->to) . "','" . $this->header . "','" . $this->subject . "','" . mysql_real_escape_string($this->content) . "','" . date('Y-m-d H:i:s') . "');";
                if (!mysql_query($query))
                    addMessage('Nem sikerült elmenteni az emailt.', 'warning');
                if (!mail(implode(',', $this->to), $this->subject, $this->content, $this->header))
                    addMessage('Valami hiba történt az email elküldése közben.', 'danger');
                else
                    return true;
            }
        } else {
            addMessage('Nem tudtuk elküldeni az emailt. Kevés az adat.', 'danger');
        }
    }

}
