<?php

namespace Html\Ajax;

class AutocompleteEvents extends Ajax {

    public function __construct() {
        if ($_REQUEST['text'] == '' OR preg_match('/^[0-9]{1}/i', $_REQUEST['text'])) {
            $return[] = array('label' => '<i>hónap és nap (hh-nn)</i>', 'value' => date('m-d'));
            $return[] = array('label' => '<i>pontos dátum (éééé-hh-nn)</i>', 'value' => date('Y-m-d'));
            $return[] = array('label' => '<i>vagy megfelelő kifejezés</i>', 'value' => '');
        }

        $query = "SELECT name FROM events WHERE name  LIKE '%" . $_REQUEST['text'] . "%' GROUP BY name ORDER BY name LIMIT 8";
        if (!$lekerdez = mysql_query($query))
            $return[] = array('label' => 'hiba', 'value' => "HIBA az esemény keresőben!<br>$query<br>" . mysql_error());
        while ($row = mysql_fetch_row($lekerdez, MYSQL_ASSOC)) {
            $return[] = array('label' => $row['name'], 'value' => $row['name']);
        }
        $this->content = json_encode(array('results' => $return));
    }

}
