<?php

namespace Html\Ajax;

class FormParticularEmpty extends Ajax {

    public function __construct() {
        $vars = formPeriod($_POST['particular'], false, 'particular');
        $this->array2this($vars);
        $this->template = 'admin_form_particular.html';
    }

}
