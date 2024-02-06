<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Email;

class Email extends \App\Legacy\Html\Html
{
    public function __construct($path)
    {
        if (!$this->checkPermission()) {
            throw new \Exception('Nincs jogod levélküldéshez.');
        }

        $this->mail = new \App\Legacy\Model\Email();
        if (isset($_REQUEST['send'])) {
            $this->send();
            $this->template = 'layout_simpliest.twig';
            $this->body = 'Köszönjük, elküldtük.';
            unset($this->mail);
        } else {
            $this->preparePage($path);
        }
    }

    public function send()
    {
        $this->mail->to = \App\Legacy\Request::TextRequired('email');
        $this->mail->subject = \App\Legacy\Request::TextRequired('subject');
        $this->mail->body = \App\Legacy\Request::TextRequired('text');
        $this->mail->type = \App\Legacy\Request::Simpletext('type');
        if (!$this->mail->send()) {
            addMessage('Nem sikerült elküldeni az emailt. Bocsánat.', 'danger');
        }
    }

    public function preparePage($path)
    {
        $id = \App\Legacy\Request::Integer('id');

        if ($id) {
            $this->mail = \App\Legacy\Model\Email::find($id);
        }
    }

    public function checkPermission()
    {
        $user = $this->getSecurity()->getUser();
        $this->user = $user;
        if (!$this->user->checkRole('"any"')) {
            return false;
        }

        return true;
    }
}
