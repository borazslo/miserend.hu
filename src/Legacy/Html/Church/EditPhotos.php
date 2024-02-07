<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Church;

use App\Legacy\Html\Html;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditPhotos extends Html
{
    public function add(Request $request): Response
    {
        dump('todo');
        exit;
    }

    public function legacyCode()
    {
        $user = $this->getSecurity()->getUser();

        $this->input = $_REQUEST;
        $this->tid = $path[0];
        $this->church = \App\Legacy\Model\Church::find($this->tid);
        if (!$this->church) {
            throw new \Exception('Nincs ilyen templom.');
        }
        $this->church = $this->church->append(['writeAccess']);

        if (!$this->church->writeAccess) {
            throw new \Exception('Hiányzó jogosultság!');

            return;
        }

        $isForm = \App\Legacy\Request::Text('submit');
        if ($isForm) {
            $this->modify();
        }

        $this->church->photos;
        $this->title = $this->church->fullName;
    }

    public function modify()
    {
        if ($this->input['church']['id'] != $this->tid) {
            throw new \Exception('Gond van a módosítandó templom azonosítójával.');
        }

        if (isset($this->input['photos'])) {
            foreach ($this->input['photos'] as $modPhoto) {
                $origPhoto = \App\Legacy\Model\Photo::find($modPhoto['id']);
                if ($origPhoto) {
                    if ($modPhoto['flag'] == 'i') {
                        $origPhoto->flag = 'i';
                    } else {
                        $origPhoto->flag = 'n';
                    }
                    if ($modPhoto['weight'] == '' || is_numeric((int) $modPhoto['weight'])) {
                        $origPhoto->weight = $modPhoto['weight'];
                    } else {
                        $origPhoto->order = 0;
                    }
                    $origPhoto->title = $modPhoto['title'];
                    $origPhoto->save();
                    if (isset($modPhoto['delete'])) {
                        $origPhoto->delete();
                    }
                }
            }
        }

        $user = $this->getSecurity()->getUser();
        $this->church->log .= "\nFotók: ".$user->getLogin().' ('.date('Y-m-d H:i:s').')';

        switch ($this->input['modosit']) {
            case 'n':
                $this->redirect('/church/catalogue');
                break;

            case 't':
                $this->redirect('/church/'.$this->church->id);
                break;

            case 'm':
                $this->redirect('/church/'.$this->church->id.'/editschedule');
                break;

            default:
                break;
        }
    }
}
