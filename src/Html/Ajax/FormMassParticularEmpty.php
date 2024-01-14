<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

class FormMassParticularEmpty extends Ajax
{
    public function __construct()
    {
        $vars = formMass($_POST['particular'], $_POST['count'], false, 'particular');
        $this->array2this($vars);
        $this->template = 'admin_form_mass_particular.html';
    }
}
