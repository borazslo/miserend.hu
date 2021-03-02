<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Apitest extends Html {

    public function __construct() {
        parent::__construct();
        $this->setTitle('API tesztelés');

        global $user;
        if (!$user->isadmin) {
            addMessage("Hozzáférés megtagadva!", "danger");
            $this->redirect('/');
        }

        
   
        if(isset($_REQUEST['json'])) {
                $json = array();
    
                if($_FILES['fileToUpload']['tmp_name']!='') {
                    
                    $imagedata = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
                         // alternatively specify an URL, if PHP settings allow
                    $json['photo'] = base64_encode($imagedata);

                    $json['photo']  = 'data:'.$_FILES['fileToUpload']['type'].';base64,'.base64_encode(file_get_contents($_FILES['fileToUpload']['tmp_name']));
                    echo '<img src="'.$json['photo'].'" height="120">';        
                }

                $path = explode("/",$_REQUEST['url']);

                $request = array(
                    'q'=> $path[1],
                    'action'=> $path[3],
                    'v'=> intval(ltrim($path[2],'v'))
                );

                foreach($_REQUEST['json'] as $var) {
                    if($var['key']!='') {
                       if(is_numeric($var['value']))
                            $json[$var['key']] = intval($var['value']);
                       else
                           $json[$var['key']] = $var['value'];
                    }
                }

                $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://';
                $url = $protocol .$_SERVER['SERVER_NAME'].$_REQUEST['url'];
                $response = sendJson($url,$json);
                printr($response);

            exit;
        }
        
    }

   
}
