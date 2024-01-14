<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Church;

class EditPhotos extends \App\Html\Html
{
    public function __construct($path)
    {
        global $user;

        $this->input = $_REQUEST;
        $this->tid = $path[0];
        $this->church = \App\Model\Church::find($this->tid);
        if (!$this->church) {
            throw new \Exception('Nincs ilyen templom.');
        }
        $this->church = $this->church->append(['writeAccess']);

        if (!$this->church->writeAccess) {
            throw new \Exception('Hiányzó jogosultság!');

            return;
        }

        $isForm = \App\Request::Text('submit');
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
                $origPhoto = \App\Model\Photo::find($modPhoto['id']);
                if ($origPhoto) {
                    if ('i' == $modPhoto['flag']) {
                        $origPhoto->flag = 'i';
                    } else {
                        $origPhoto->flag = 'n';
                    }
                    if ('' == $modPhoto['weight'] || is_numeric((int) $modPhoto['weight'])) {
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

        global $user;
        $this->church->log .= "\nFotók: ".$user->login.' ('.date('Y-m-d H:i:s').')';

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
