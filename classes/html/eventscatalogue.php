<?php

namespace Html;

class EventsCatalogue extends Html {

    public function __construct($path) {
        global $user;

        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod megnézni az események listáját.');
        }

        if (isset($_REQUEST['save']))
            $this->save($_REQUEST);

        if (isset($_REQUEST['order']) AND in_array($_REQUEST['order'], array('year, date', 'name'))) {
            $order = $_REQUEST['order'];
        } else {
            $order = false;
        }

        $return = $this->form($order);
        foreach ($return as $key => $value) {
            $this->$key = $value;
        }
    }

    function form($order = false) {
        if (!$order)
            $order = 'year, date';
        $query = "SELECT * FROM events WHERE year >= " . date('Y', strtotime('-1 year')) . " ORDER BY " . $order . ";";
        $result = mysql_query($query);
        $years = array();
        $names = array();
        while (($row = mysql_fetch_array($result, MYSQL_ASSOC))) {
            $name = $row['name'];
            $form[$name][$row['year']]['input'] = array(
                'name' => 'events[' . $name . '][' . $row['year'] . '][input]',
                'value' => $row['date'],
                'class' => 'input-sm')
            ;
            $form[$name][$row['year']]['id'] = array(
                'type' => 'hidden',
                'name' => 'events[' . $name . '][' . $row['year'] . '][id]',
                'value' => $row['id']);

            $years[$row['year']] = $row['year'];
            $names[$name] = $name;
        }
        //new line
        //$array_shift(array_values($form))
        $newname = array(
            'name' => 'newname',
            'size' => 12);
        global $twig;
        $names['new'] = 'new';

        $years[date('Y')] = date('Y');
        $years[date('Y', strtotime('+1 years'))] = date('Y', strtotime('+1 years'));

        foreach (array('tol', 'ig') as $tolig) {
            $query = "SELECT " . $tolig . " FROM misek WHERE " . $tolig . " NOT REGEXP('^([0-9]{1,4})') GROUP BY " . $tolig . " ";
            $result = mysql_query($query);
            while (($row = mysql_fetch_array($result, MYSQL_ASSOC))) {
                $name = preg_replace('/ (\+|-)([0-9]{1,3}$)/i', '', $row[$tolig]);
                if (!isset($names[$name]))
                    $names[$name] = $name;
            }
        }
        foreach ($names as $name) {
            foreach ($years as $year) {
                if (!isset($form[$name][$year])) {
                    $form[$name][$year] = array(
                        'input' => array(
                            'name' => 'events[' . $name . '][' . $year . '][input]',
                            'size' => '8'));
                }
            }
            //stats
            $query = "SELECT count(*) as sum FROM misek where ig  REGEXP '^(" . $name . ")(( +| -)[0-9]{1,3})|)$'";
            $result = mysql_query($query);
            $row = mysql_fetch_array($result, MYSQL_ASSOC);
            $stats[$name] = $row['sum'];
        }


        return array('form' => $form, 'names' => $names, 'years' => $years, 'stats' => $stats);
    }

    function save($form) {
        foreach ($form['events'] as $name => $years) {
            foreach ($years as $year => $input) {
                if (isset($input['id'])) {
                    if ($input['input'] != '')
                        $date = date('Y-m-d', strtotime($input['input']));
                    else
                        $date = '';
                    $query = "UPDATE events SET `date` = '" . $date . "' WHERE id = " . $input['id'] . " LIMIT 1";
                    mysql_query($query);
                } else {
                    if ($input['input'] != '') {
                        if ($name == 'new')
                            $name = sanitize($form['new']);
                        if ($name != 'new' OR $form['new'] != '') {
                            $query = "INSERT INTO events (name,year,date) VALUES ('" . $name . "','" . $year . "','" . $input['input'] . "');";
                            mysql_query($query);
                            //echo $query."<br/>";
                        }
                    }
                }
            }
        }

        generateMassTmp();
    }

}
