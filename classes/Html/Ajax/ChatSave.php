<?php

namespace Html\Ajax;

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
        $query = "INSERT INTO chat (datum, user, kinek, szoveg, ip) VALUES ('" . date('Y-m-d H:i:s') . "','" . $user->login . "','" . $kinek . "','" . trim($text) . "','" . $ipkiir . "' );";
        $rv = mysql_query($query);
        if ($rv === false) {
            $this->content = json_encode(array('result' => 'error', 'text' => 'Hiba a mysql küldésben!'));
        } else {
            $this->content = json_encode(array('result' => 'saved', 'text' => $query));
        }
    }

}
