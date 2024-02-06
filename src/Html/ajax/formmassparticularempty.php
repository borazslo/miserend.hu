<?php

namespace App\Html\Ajax;

class FormMassParticularEmpty extends Ajax {

    public function __construct() {
        $vars = formMass($_POST['particular'], $_POST['count'], false, 'particular');
        $this->array2this($vars);
        $this->template = 'admin_form_mass_particular.html';
    }

}
