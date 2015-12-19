<?php

namespace Html;

class Html {

    public $template;
    public $pageTitle = 'VPP - miserend';   
    
    function render() {
        global $user;
        $this->user = $user;
        global $twig;        
        $this->html = $twig->render($this->template, (array) $this);
    }

    function setTitle($title) {
        $this->pageTitle = $title." | Miserend";
    } 
}
