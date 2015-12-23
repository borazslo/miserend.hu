<?php

namespace Html\Ajax;

class EventsList extends Ajax {

    public function __construct() {
        $query = "SELECT name FROM events GROUP BY name";
        if (!$lekerdez = mysql_query($query))
            echo "HIBA a város keresőben!<br>$query<br>" . mysql_error();
        while ($row = mysql_fetch_row($lekerdez, MYSQL_ASSOC)) {
            $return[] = $row['name'];
        }
        $this->content = json_encode(array('events' => $return));
    }

}
