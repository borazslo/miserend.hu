<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html;

use App\Model;
use App\Model\Church;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Remark extends Html
{
    public $template;

    public function add(Request $request): Response
    {
        $user = $this->getSecurity()->getUser();
        $config = $this->getConfig();

        $churchId = $request->attributes->getInt('church_id');

        if ($request->getMethod() === 'POST') {
            $remark = new Model\Remark();

            $remark->church_id = $churchId;
            $remark->allapot = 'u';
            $remark->leiras = $request->request->get('leiras');
            $remark->email = $request->request->get('email');
            $remark->nev = $request->request->get('nev');

            if ('' == $remark->nev) {
                $remark->nev = $remark->email;
            }

            // Belépett felhasználónál hidden email és név adat volt, de nem bízunk benne
            if ('*vendeg*' != $user->getUsername()) {
                $remark->login = $user->getUsername();
                $remark->email = $user->getEmail();
            }

            $megbizhato = Model\Remark::select('megbizhato')->where('email', $remark->email)->orderBy('created_at', 'desc')->limit(1)->first();
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

            return $this->render('remark.twig', [
                'debug' => $config['debug'],
                'church' => Church::find($churchId),
                'disclaimer' => 'Figyelem! Nem állunk közvetlen kapcsolatban a plébániákkal ezért plébániai ügyekben (pl. keresztelési okiratok, stb.) sajnos nem tudunk segíteni.',
            ]);
        }

        return $this->render('remark_form.twig', [
            'tid' => $churchId,
            'church' => Church::find($churchId),
            'disclaimer' => 'Figyelem! Nem állunk közvetlen kapcsolatban a plébániákkal ezért plébániai ügyekben (pl. keresztelési okiratok, stb.) sajnos nem tudunk segíteni.',
        ]);
    }

    public function pageList(Request $request): Response
    {
        /*this->action = $path['action'];
$this->tid = $rid = $path['church_id'];

$this->church = \App\Model\Church::find($this->tid);
$this->disclaimer = ;

switch ($this->action) {
    case 'list':
        $this->pageList();
        $this->template = ;
        break;
}*/
        if ('modify' == \App\Request::Simpletext('remark')) {
            $rid = \App\Request::IntegerRequired('rid');
            $remark = \App\Model\Remark::find($rid);

            $remark->allapot = \App\Request::Simpletext('state');
            $remark->admindatum = date('Y-m-d H:i:s');

            $remark->appendComment(\App\Request::Text('adminmegj'));
            $remark->save();

            if ($this->tid != $remark->church_id) { // Hogy ne lehessen csalni
                $this->tid = $remark->church_id;
                $this->church = Church::find($this->tid);
            }
        }

        $user = $this->getSecurity()->getUser();
        if (!$this->church->writeAccess) {
            addMessage('Hiányzó jogosultság. Elnézést.', 'danger');

            exit;
        }

        $this->church->remarks;

        exit;
    }
}
