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
        
        // v10.10 test data
        if(isset($this->input['field'])) {
            switch ($this->input['field']) {
                case 'live_videos':
                    /*
                     {
                        "field": "live_videos",
                        "value": {
                          "id": "4444444444",
                          "status": "live_stopped"
                        }
                      }
                     */
                    break;

                case 'feed':
                    /*
                    {
                      "field": "feed",
                      "value": {
                        "item": "status",
                        "post_id": "44444444_444444444",
                        "verb": "add",
                        "published": 1,
                        "created_time": 1615407988,
                        "message": "Example post content.",
                        "from": {
                          "name": "Test Page",
                          "id": "1067280970047460"
                        }
                      }
                    }
                     */

                    break;

                default:
                    break;
            }
        }
        // Event notification exmaple
        // https://developers.facebook.com/docs/graph-api/webhooks/getting-started
        // https://developers.facebook.com/docs/pages/realtime/
        elseif(isset($this->input[0]['object'])) {
            /*
             [
                {
                  "entry": [
                    {
                      "changes": [
                        {
                          "field": "feed",
                          "value": {
                            "from": {
                              "id": "{user-id}",
                              "name": "Cinderella Hoover"
                            },
                            "item": "post",
                            "post_id": "{page-post-id}",
                            "verb": "add",
                            "created_time": 1520544814,
                            "is_hidden": false,
                            "message": "It's Thursday and I want to eat cake."
                          }
                        }
                      ],
                      "id": "{page-id}",
                      "time": 1520544816
                    }
                  ],
                  "object": "page"
                }
              ]
             
             */
            //We subscirbed 'page' data only.
            if($this->input[0]['object'] != 'page' ) throw new \Exception('FBHook reacts to page objects only.');
                                    
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

