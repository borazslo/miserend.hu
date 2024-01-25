<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html;

class Remark extends Html
{
    public $template;

    public function __construct($path)
    {
        $this->action = $path['action'];
        $this->tid = $rid = $path['church_id'];

        $this->church = \App\Model\Church::find($this->tid);
        $this->disclaimer = 'Figyelem! Nem állunk közvetlen kapcsolatban a plébániákkal ezért plébániai ügyekben (pl. keresztelési okiratok, stb.) sajnos nem tudunk segíteni.';

        switch ($this->action) {
            case 'list':
                $this->pageList();
                $this->template = 'remark_list.twig';
                break;

            case 'addform':
                $this->template = 'remark_form.twig';
                break;

            case 'add':
                $this->pageAdded();
                $this->template = 'remark.twig';
                break;
        }
    }

    public function pageList()
    {
        if ('modify' == \App\Request::Simpletext('remark')) {
            $rid = \App\Request::IntegerRequired('rid');
            $remark = \App\Model\Remark::find($rid);

            $remark->allapot = \App\Request::Simpletext('state');
            $remark->admindatum = date('Y-m-d H:i:s');

            $remark->appendComment(\App\Request::Text('adminmegj'));
            $remark->save();

            if ($this->tid != $remark->church_id) { // Hogy ne lehessen csalni
                $this->tid = $remark->church_id;
                $this->church = \App\Model\Church::find($this->tid);
            }
        }

        global $user;
        if (!$this->church->writeAccess) {
            addMessage('Hiányzó jogosultság. Elnézést.', 'danger');

            return;
        }

        $this->church->remarks;
    }

    public function pageAdded()
    {
        $remark = new \App\Model\Remark();

        $remark->church_id = $this->church->id;
        $remark->allapot = 'u';
        $remark->leiras = \App\Request::TextRequired('leiras');
        $remark->email = \App\Request::TextRequired('email');
        $remark->nev = \App\Request::Text('nev');
        if ('' == $remark->nev) {
            $remark->nev = $remark->email;
        }

        // Belépett felhasználónál hidden email és név adat volt, de nem bízunk benne
        global $user;
        if ('*vendeg*' != $user->getUsername()) {
            $remark->login = $user->getUsername();
            $remark->email = $user->getEmail();
        }

        $megbizhato = \App\Model\Remark::select('megbizhato')->where('email', $remark->email)->orderBy('created_at', 'desc')->limit(1)->first();
        if ($megbizhato) {
            $remark->megbizhato = $megbizhato->megbizhato;
        } else {
            $remark->megbizhato = '?';
        }

        if (!$remark->save()) {
            addMessage('Nem sikerült elmenteni az észrevételt. Sajánljuk.', 'danger');
        }

        if (!$remark->emails()) {
            addMessage('Nem sikerült elküldeni az értesítő emaileket.', 'warning');
        }

        global $config;
        $this->debug = $config['debug'];
    }
}
