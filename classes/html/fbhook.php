<?php

namespace Html;

class FbHook extends Html {

    public function __construct($path) {
        global $config;
        
        $hub = [];
        $hub['mode'] = \Request::Simpletext('hub_mode');
        $hub['challenged'] = \Request::Integer('hub_challenge');
        $hub['verify_token'] = \Request::Simpletext('hub_verify_token');
        
        /* Verification Requests */
        if($hub['mode']) {
            if($hub['mode'] != 'subscribe')  throw new \Exception('hub_mode is invalid.');
            if($hub['verify_token'] != $config['fb_verify_token']) throw new \Exception('fb_verify_token is invalid.');

            echo $hub['challenged'];
            exit;           
        }
    }

}

