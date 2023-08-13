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
                echo "Kérés:<pre>";
                
                $json = $_REQUEST['json'];
                if($json != '' ) {
                    $json = json_decode($json);
                    if(!$json)
                        echo "Hibás JSON data!<br/>\n";
                }
                if(!$json)
                    $json = new Object();
    
                
                if($_FILES['fileToUpload']['tmp_name']!='') {
                    
                    $imagedata = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
                         // alternatively specify an URL, if PHP settings allow
                    $json->photo = base64_encode($imagedata);
                    $json->photo  = 'data:'.$_FILES['fileToUpload']['type'].';base64,'.base64_encode(file_get_contents($_FILES['fileToUpload']['tmp_name']));
                    echo '<img src="'.$json->photo.'" height="120"><br/>';        
                }

                $path = explode("/",$_REQUEST['url']);
                $request = array(
                    'q'=> $path[1],
                    'action'=> $path[3],
                    'v'=> intval(ltrim($path[2],'v'))
                );
                                    
                $url = $_REQUEST['url'];
                                
                print_r($url); echo "<br/>";
                print_r($json);
                echo "</pre><hr>Válasz:";
                $response = $this->sendJson($url,$json);                
                printr($response);
                                
            exit;
        }
        
    }
    
    function sendJson($url, $content) {

        if (!preg_match('/^http:\/\//i', $url)) {
            global $config;
            $url = $config['path']['domain'] . $url;
        }        
        $contentEncoded = json_encode($content);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $contentEncoded);
        
        $response = curl_exec($curl);
        if (!$responseArray = json_decode($response, true)) {
            $responseArray = $response;
        } else
            $responseArray['status'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        return $responseArray;
    }
   
}
