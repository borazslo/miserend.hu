<?php

namespace Html\Ajax;

class SwitchReliable extends Ajax {

    public function __construct() {
        if (!is_numeric($_REQUEST['rid']))
            exit;
        if (!in_array($_REQUEST['reliable'], array('i', 'n', '?', 'e')))
            exit;

        $remark = new \Remark($_REQUEST['rid']);
        $remark->changeReliability($_REQUEST['reliable']);
    }

}
