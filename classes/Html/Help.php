<?php

namespace Html;

class Help extends Html {

    public function __construct() {
        $this->setTitle('Súgó');

        $this->content = '';

        $idT = explode('-', \Request::SimpletextRequired('id'));
        foreach ($idT as $id) {
            $help = new \Help($id);
            $this->content .= $help->html;
        }

        $this->template = 'help.twig';
    }

}
