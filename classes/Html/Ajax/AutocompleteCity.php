<?php

namespace Html\Ajax;

class AutocompleteCity extends Ajax {

    public function __construct() {
        $return[] = array('label' => $_REQUEST['text'] . "* <i>(Minden " . $_REQUEST['text'] . "-al kezdődő)</i>", 'value' => $_REQUEST['text'] . "*");
        $return[] = array('label' => "*" . $_REQUEST['text'] . "* <i>(Minden " . $_REQUEST['text'] . "-t tartalmazó)</i>", 'value' => "*" . $_REQUEST['text'] . "*");

        $query = "SELECT varos, orszag FROM templomok WHERE ok = 'i' AND varos LIKE '" . $_REQUEST['text'] . "%' ";
        $query .= "GROUP BY varos ORDER BY varos LIMIT 10";
        if (!$lekerdez = mysql_query($query))
            echo "HIBA a város keresőben!<br>$query<br>" . mysql_error();
        while ($row = mysql_fetch_row($lekerdez, MYSQL_ASSOC)) {
            $return[] = array('label' => $row['varos'], 'value' => $row['varos']);
        }
        $this->content = json_encode(array('results' => $return));
    }

}
