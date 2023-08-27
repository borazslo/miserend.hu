<?php

namespace Html\Ajax;

class FormMassEmpty extends Ajax {

    public function __construct() {
        $vars = formMass($_POST['period'], $_POST['count'], false, 'period');
        $this->array2this($vars);
        $this->template = 'admin_form_mass.twig';
    }

}
