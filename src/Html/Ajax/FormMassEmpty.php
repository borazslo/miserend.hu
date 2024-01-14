<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

class FormMassEmpty extends Ajax
{
    public function __construct()
    {
        $vars = formMass($_POST['period'], $_POST['count'], false, 'period');
        $this->array2this($vars);
        $this->template = 'admin_form_mass.twig';
    }
}
