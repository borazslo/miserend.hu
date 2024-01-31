<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\User;

class MaintainedChurches extends \App\Html\Html
{
    public function __construct()
    {
        $this->setTitle('Módosítható templomok és miserendek');
        $this->title = 'Módosítható templomok és miserendek';

        $user = $this->getSecurity()->getUser();
        if (!\is_array($user->responsible['church'])) {
            addMessage('Nincs olyan templom, amit módosíthatnál.', 'info');

            return false;
        }

        foreach ($user->responsible['church'] as $tid) {
            try {
                $this->churches[$tid] = \App\Model\Church::find($tid);
            } catch (\Exception $e) {
                addMessage($e->getMessage(), 'info');
            }
        }

        $this->columns2 = true;
    }
}
