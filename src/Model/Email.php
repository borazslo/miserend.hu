<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use PHPMailer\PHPMailer\PHPMailer;

class Email extends \Illuminate\Database\Eloquent\Model
{
    public $debug;
    public $debugger;

    public function addToQueue($to = false)
    {
        if ($to) {
            $this->to = $to;
        }
        $this->status = 'queued';

        return $this->save();
    }

    public function send($to = false)
    {
        if ($to) {
            $this->to = $to;
        }

        $this->status = 'sending';
        $this->save();
        /* mail() code */

        global $config;
        $this->debug = $config['mail']['debug'];
        $this->debugger = $config['mail']['debugger'];

        if (1 == $this->debug) {
            $this->header .= 'Bcc: '.$this->debugger."\r\n";
        } elseif (2 == $this->debug) {
            $this->body .= ".<br/>\n<i>Originally to: ".print_r($this->to, 1).'</i>';
            $this->to = $this->debugger;
        }

        if (isset($this->subject) && isset($this->body) && isset($this->to)) {
            if (3 == $this->debug) {
                print_r($this);
            } elseif (5 == $this->debug) {
                // black hole
            } else {
                $mail = new PHPMailer(true);

                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->CharSet = 'UTF-8';

                // FOR PROD: sendmail
                if ('production' == $config['env']) {
                    $mail->isSendmail();
                } else {
                    // FOR DEV: to mailcatcher
                    $mail->isSMTP();
                    $mail->Host = 'mailcatcher';
                    $mail->Port = 1025;
                    $mail->SMTPAuth = false;
                    $mail->SMTPSecure = false;
                }

                // Recipients
                $mail->setFrom('info@miserend.hu', 'Miserend.hu');
                // $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
                $mail->addAddress($this->to);               // Name is optional
                // $mail->addReplyTo('info@example.com', 'Information');
                // $mail->addCC('cc@example.com');
                // $mail->addBCC('bcc@example.com');

                // Attachments
                // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
                // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

                // Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = $this->subject;
                $mail->Body = $this->body;
                // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                // if (!mail($this->to, $this->subject, $this->body, $this->header)) {
                if (!$mail->send()) {
                    addMessage('Valami hiba történt az email elküldése közben.', 'danger');
                    $this->status = 'error';
                    $this->save();
                } else {
                    $this->status = 'sent';

                    return $this->save();
                }
            }
        } else {
            addMessage('Nem tudtuk elküldeni az emailt. Kevés az adat.', 'danger');
        }
        $this->status = 'error';
        $this->save();

        return false;
    }

    public function __construct()
    {
        global $config;

        $this->header = 'MIME-Version: 1.0'."\r\n";
        $this->header .= 'Content-type: text/html; charset=UTF-8'."\r\n";
        $this->header .= 'From: '.$config['mail']['sender']."\r\n";
    }

    public function render($twigfile, $array)
    {
        global $twig;

        if (\is_object($array) && method_exists($array, 'toArray')) {
            $array = $array->toArray();
        }

        if (!$this->type) {
            $this->type = $twigfile;
        }

        $rendered = $twig->render('emails/'.strtolower($twigfile).'.twig', (array) $array);

        $lines = explode("\n", $rendered);
        if (!$this->subject) {
            $this->subject = $lines[0];
        }

        unset($lines[0]);
        unset($lines[1]);

        $this->body = implode("\n", $lines);

        return true;
    }
}
