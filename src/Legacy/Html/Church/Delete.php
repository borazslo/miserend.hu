<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Church;

class Delete extends \App\Legacy\Html\Html
{
    public function __construct($path)
    {
        $user = $this->getSecurity()->getUser();
        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod a templomot törölni.');
        }

        $this->input['tid'] = $path[0];

        $this->church2delete = \App\Legacy\Model\Church::find($this->input['tid']);
        if (0 == $this->church2delete->id) {
            addMessage('Nincs ilyen templom!', danger);

            return;
        }

        $this->input['confirmation'] = \App\Legacy\Request::SimpleText('confirmation');
        if (!$this->input['confirmation']) {
            $this->askConfirmation();

            return;
        } else {
            $this->delete();
        }
    }

    public function delete()
    {
        $user = $this->getSecurity()->getUser();

        $comment = \App\Legacy\Request::Text('comment');
        if ('' != $this->church2delete->adminmegj) {
            $this->church2delete->adminmegj .= "\n";
        }
        $this->church2delete->adminmegj .= 'Törölte '.$user->getLogin();
        if ($comment) {
            $this->church2delete->adminmegj .= ': '.$comment;
        }
        $this->church2delete->ok = 'n';
        $this->church2delete->log .= "\nDel: ".$user->getLogin().' ('.date('Y-m-d H:i:s').')';
        $this->church2delete->save();

        $this->church2delete->delete();
        addMessage('A templomot sikeresen töröltük.', 'info');
        header('Location: /templom/list');
    }

    public function askConfirmation()
    {
        // church/delete.twig
    }
}
