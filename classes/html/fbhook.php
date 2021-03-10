<?php

namespace Html;

class FbHook extends \Api\Api {

    public $logDir = PATH . 'fajlok/tmp/';
    
    public function __construct($path) {
        global $config;
        
        $hub = [];
        $hub['mode'] = \Request::Simpletext('hub_mode');
        $hub['challenged'] = \Request::Integer('hub_challenge');
        $hub['verify_token'] = \Request::Simpletext('hub_verify_token');
        
        /* Verification Requests */
        if($hub['mode']) {
            $this->log('Verification Request has arrived.');
            if($hub['mode'] != 'subscribe')  throw new \Exception('hub_mode is invalid.');
            if($hub['verify_token'] != $config['fb_verify_token']) throw new \Exception('fb_verify_token is invalid.');
            $this->log('And it was ok.');
            exit;           
        }
                              
        $this->getInputJson();
        
        $this->log('Received: '.json_encode($this->input));
                
        // Event notification exmaple
        // https://developers.facebook.com/docs/graph-api/webhooks/getting-started
        // https://developers.facebook.com/docs/pages/realtime/
        if(isset($this->input['object']) AND $this->input['object'] == 'page') {
            foreach($this->input['entry'] as $entry) {
            
                // $entry['id'] = (int) page-id;
                // $entry['time'] = (int) timestamp;
                
                foreach($entry['changes'] as $change) {
                                        
                    // https://developers.facebook.com/docs/graph-api/webhooks/reference/page/
                    if($change['field'] == 'feed' AND $change['value']['verb'] == 'add' AND $change['value']['published'] == 1 AND in_array($change['value']['item'],['link','note','page','photo','post','share','status','story','video'])) {
                        //https://www.facebook.com/permalink.php?story_fbid=814898132425521&id=249586975623309    
                        $this->log($change['value']['from']['name']." has added a new ".$change['value']['item']." on Page=".$entry['id'].": ".$change['value']['message']);                                                
                    }
                    
                    if($change['field'] == 'live_videos') {
                        $this->log("Live video ".$change['value']['id']." has ".preg_replace('/live_/','',$change['value']['status']).".");
                    }                    
                }
            }              
        } else {
            throw new \Exception('FBHook reacts to page objects only.');
        }
        
    }
    
    public function log($text) {
        $file = $this->logDir.'fbhook.lag';    
        $text = date('Y-m-d H:i:s')." ".$text;
        $myfile = file_put_contents($file, $text.PHP_EOL , FILE_APPEND | LOCK_EX);
    }
    
    public function render() {
        $this->html = '';
    }
}

