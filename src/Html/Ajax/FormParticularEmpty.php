<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

class FormParticularEmpty extends Ajax
{
    public function __construct()
    {
        $vars = formPeriod($_POST['particular'], false, 'particular');
        $this->array2this($vars);
        $this->template = 'admin_form_particular.html';
    }
}
