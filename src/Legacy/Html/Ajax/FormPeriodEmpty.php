<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Ajax;

class FormPeriodEmpty extends Ajax
{
    public function __construct()
    {
        $vars = formPeriod($_POST['period'], false, 'period');
        $this->array2this($vars);
        $this->template = 'admin_form_period.html';
    }
}
