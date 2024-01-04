<?php

namespace App\Html\Ajax;

class FormPeriodEmpty extends Ajax {

    public function __construct() {
        $vars = formPeriod($_POST['period'], false, 'period');
        $this->array2this($vars);
        $this->template = 'admin_form_period.html';
    }

}
