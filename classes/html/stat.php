<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Stat extends Html {

    public function __construct() {
        parent::__construct();
        $this->setTitle('Statisztika');
        
        global $user;
        if (!$user->loggedin) {
            addMessage("Hozzáférés megtagadva!", "danger");
            $this->redirect('/');
        }

        /* 
         * Templomok frissítettsége + észrevételek: Elmúlt év
         */
        $this->s1  = [
            'labels' => ['templomok, amik akkor frissültek utoljára','beküldött észrevételek száma az adott időben'],
            'data' => [0 => [], 1 => [] ] 
            ];
        $churches = \Eloquent\Church::where('ok', 'i')
                ->countByUpdatedYear()
                ->get();
        foreach($churches as $church) {
            $this->s1['data'][0][] = [(int) $church->updated_year,$church->count_updated_year];
        }        
        $remarks = \Eloquent\Remark::countByCreatedYear()->get();
        foreach($remarks as $remark) {
            if($remark->created_year > 0)
                $this->s1['data'][1][] = [(int) $remark->created_year,$remark->count_created_year];
        }       

        /* 
         * Templomok frissítettsége + észrevételek: Elmúlt év
         */
        $this->s3['labels'] = ['templomok, amik akkor frissültek utoljára','beküldött észrevételek száma az adott időben'];
        $churches = \Eloquent\Church::where('ok', 'i')
                ->countByUpdatedMonth()
                ->where('frissites', '>', date('Y-m-d', strtotime('-1 year')))
                ->get();
        $this->s3['data'] = [0=>[],1=>[]];
        foreach($churches as $church) {
            $this->s3['data'][0][] = [$church->updated_month,$church->count_updated_month];
        }        
        $remarks= \Eloquent\Remark::countByCreatedMonth()
                ->where('created_at', '>', date('Y-m-d', strtotime('-1 year')))
                ->get();
        foreach($remarks as $remark) {
            $this->s3['data'][1][] = [$remark->created_month,$remark->count_created_month];
        }        
        
        /*
         * Templom karbantartók statisztikái
         */
        $this->s4 = ['data'=>[],'labels'=>[]];
        
        $data = \Eloquent\ChurchHolder::select('user_id',DB::raw('count(*) as count'))->groupBy('user_id')->orderBy('count')->get();
        
        foreach($data as $uid => $count ) {
            if(isset($tmp[$count->count]))
             $tmp[$count->count]++;
            else
                $tmp[$count->count] = 1;
        }   
        foreach($tmp as $k => $v)
            $this->s4['data'][] = [$k,$v];
        
        /* 
         * ExternalApi Stats 
         */
        $this->s5 = ['data'=>[],'labels'=>[]];
        $data = DB::table('stats_externalapi')->select('name','date',DB::raw('SUM(count) count'),DB::raw('CONCAT(name,date)  namedate'))->where('date','>',date('Y-m-d',strtotime('-1 month')))->groupBy('namedate')->orderBy('date','asc')->get();        
        $data = collect($data)->groupBy(['name'])->toArray(); //->transform(function($item, $k) {return $item->groupBy('name');})->toArray();
        $c = 0;        
        foreach($data as $apiname => $api) {
            $this->s5['labels'][$c] = $apiname;
            $this->s5['data'][$c] = [];
            foreach($api as $date) {
                $this->s5['data'][$c][] = [$date->date,(int) $date->count];
            }
            $c++;
        }        
        
        
    }
}