<?php

namespace Html\Ajax;

class AutocompleteName extends Ajax {

    public function __construct() {
        $text = sanitize($_REQUEST['text']);

        $query = "SELECT idoszamitas, tol, ig FROM misek WHERE torles = '0000-00-00 00:00:00' AND idoszamitas LIKE '%" . $text . "%' ";
        if ($_REQUEST['type'] == 'period')
            $query .= " AND tmp_datumtol <> tmp_datumig ";
        elseif ($_REQUEST['type'] == 'particular')
            $query .= " AND tmp_datumtol = tmp_datumig ";
        $query .= " GROUP BY idoszamitas ORDER BY idoszamitas LIMIT 10";

        if (!$lekerdez = mysql_query($query)) {
            $this->content = "HIBA az időszak keresőben!<br>$query<br>" . mysql_error();
            return;
        }
        while ($row = mysql_fetch_row($lekerdez, MYSQL_ASSOC)) {
            preg_match('/^(.*?)( -[0-9]{1,3}| \+[0-9]{1,3}|)$/', $row['tol'], $from);
            preg_match('/^(.*?)( -[0-9]{1,3}| \+[0-9]{1,3}|)$/', $row['ig'], $to);
            if ($to[2] == '')
                $to[2] = '0';
            if ($from[2] == '')
                $from[2] = '0';
            $return[] = array('label' => preg_replace('/(' . $text . ')/i', '<b>$1</b>', $row['idoszamitas']), 'value' => $row['idoszamitas'], 'from' => $from[1], 'from2' => trim($from[2]), 'to' => $to[1], 'to2' => trim($to[2]));
        }
        $this->content = json_encode(array('results' => $return));
    }

}
