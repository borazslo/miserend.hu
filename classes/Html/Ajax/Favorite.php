<?php

namespace Html\Ajax;

class Favorite extends Ajax {

    public function __construct() {
        if (!is_numeric($_REQUEST['tid'])) {
            return;
        }
        if ($_REQUEST['method'] == 'add') {
            mysql_query("INSERT INTO favorites (uid,tid) VALUES (" . $user->uid . "," . $_REQUEST['tid'] . ");");
        } else if ($_REQUEST['method'] == 'del') {
            mysql_query("DELETE FROM favorites WHERE uid = " . $user->uid . " AND tid = " . $_REQUEST['tid'] . " LIMIT 1");
        }
    }

}
