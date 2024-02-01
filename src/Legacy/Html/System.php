<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html;

class System extends Html
{
    public function __construct()
    {
        parent::__construct();
        $this->setTitle('System');

        $user = $this->getSecurity()->getUser();

        if (!$user->isadmin) {
            addMessage('Hozzáférés megtagadva!', 'danger');
            $this->redirect('/');
        }

        global $config;
        $this->content = '<h3>`$ config`</h3><pre>'.print_r($config, 1).'</pre>';

        $this->template = 'layout.twig';
    }
}
