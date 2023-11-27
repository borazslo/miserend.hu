<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

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
        
        $results = DB::table('events')
                ->where('year','>=',date('Y', strtotime('-1 year')))
                ->orderByRaw($order)
                ->get();
                
        $years = array();
        $names = array();        
        foreach($results as $row) {
            $form[$row->name][$row->year]['input'] = array(
                'name' => 'events[' . $row->name . '][' . $row->year . '][input]',
                'value' => $row->date,
                'class' => 'input-sm')
            ;
            $form[$row->name][$row->year]['id'] = array(
                'type' => 'hidden',
                'name' => 'events[' . $row->name . '][' . $row->year . '][id]',
                'value' => $row->id);

            $years[$row->year] = $row->year;
            $names[$row->name] = $row->name;
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
            $results = DB::table('misek')
                    ->select($tolig)
                    ->whereRaw($tolig." NOT REGEXP('^([0-9]{1,4})')")
                    ->groupBy($tolig)
                    ->get();
            foreach($results as $row) {
                $name = preg_replace('/ (\+|-)([0-9]{1,3}$)/i', '', $row->$tolig);
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
            $result = DB::table('misek')
                    ->selectRaw('count(*) as sum')
                    ->whereRaw(" ig  REGEXP '^(" . $name . ")(( +| -)[0-9]{1,3})|)$' ")
                    ->first();
            if($result)
                $stats[$name] = $result->sum;            
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
                    DB::table('events')
                            ->where('id',$input['id'])
                            ->update([
                                'date' => $date
                            ]);
                } else {
                    if ($input['input'] != '') {
                        if ($name == 'new')
                            $name = sanitize($form['new']);
                        if ($name != 'new' OR $form['new'] != '') {
                            DB::table('events')
                                    ->insert([
                                        'name' => $name,
                                        'year' => $year,
                                        'date' => $input['input']
                                    ]);                            
                        }
                    }
                }
            }
        }

	   \Crons::generateMassTolIgTmp();
    }

}
