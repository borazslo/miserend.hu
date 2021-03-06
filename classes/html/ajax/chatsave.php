<?php

namespace Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class ChatSave extends Ajax {

    public function __construct() {
        global $user;
        if (!$user->checkRole("'any'")) {
            $this->content = json_encode(array('result' => 'error', 'text' => 'Hiányzó jogosultság'));
            return;
        }
        $text = sanitize($_REQUEST['text']);
        if (preg_match('/^\$(\w+)/si', $text, $match)) {
            $kinek = $match[1];
            $text = preg_replace('/^(\$\w+(:*))/si', "", $text);
        } else
            $kinek = "";
        if (trim(preg_replace('/^((\$|@)\w+(:*))/si', "", $text)) == '') {
            $this->content = json_encode(array('result' => 'error', 'text' => 'Nem volt igazán üzenet, amit elküldhettünk volna.'));
            return;
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        $host = gethostbyaddr($ip);
        $ipkiir = "$ip ($host)";
        
        if( DB::table('chat')->insert([
                'datum' => date('Y-m-d H:i:s'),
                'user' => $user->login,
                'kinek' => $kinek,
                'szoveg' => trim($text)
            ])
          ) {
            $this->content = json_encode(array('result' => 'error', 'text' => 'Hiba a mysql küldésben!'));
        } else {
            $this->content = json_encode(array('result' => 'saved', 'text' => $query));
        }
    }

}
