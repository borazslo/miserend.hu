<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Stat extends Html {

    public function __construct() {
        parent::__construct();
        $this->setTitle('Statisztika');
        
        global $user;
        if(!$user->loggedin) {
            addMessage("Hozzáférés megtagadva!","danger");
            $this->redirect('/');
        }
                
        $groups = \Eloquent\Church::where('ok','i')
                ->select(DB::raw('DATE_FORMAT(frissites,\'%Y\') as month'),DB::raw('COUNT(*) as count'))
                ->groupBy('month')->orderBy('month')->get();
        $s1 = [];
        foreach($groups as $group) {
            if($group->month > 0)
                $s1[] = [ $group->month , $group->count ];
        }
        $this->s1 = $this->array2jslist($s1);
        
        $groups = \Eloquent\Remark::select(DB::raw('DATE_FORMAT(created_at,\'%Y\') as month'),DB::raw('COUNT(*) as count'))
                ->groupBy('month')->orderBy('month')->get();
        $s2 = [];
        foreach($groups as $group) {
            if($group->month > 0)
                $s2[] = [ $group->month , $group->count ];
        }
        $this->s2 = $this->array2jslist($s2);
        
                $groups = \Eloquent\Church::where('ok','i')
                ->select(DB::raw('DATE_FORMAT(frissites,\'%m\') as month'),DB::raw('COUNT(*) as count'))
                        ->where('frissites','>',date('Y-m-d',strtotime('-1 year')))
                ->groupBy('month')->orderBy('month')->get();
        $s1 = [];
        foreach($groups as $group) {
            if($group->month > 0)
                $s1[] = [ $group->month , $group->count ];
        }
        $this->s3 = $this->array2jslist($s1);
        
        $groups = \Eloquent\Remark::select(DB::raw('DATE_FORMAT(created_at,\'%m\') as month'),DB::raw('COUNT(*) as count'))
                ->where('created_at','>',date('Y-m-d',strtotime('-1 year')))
                ->groupBy('month')->orderBy('month')->get();
        $s2 = [];
        foreach($groups as $group) {
            if($group->month > 0)
                $s2[] = [ $group->month , $group->count ];
        }
        $this->s4 = $this->array2jslist($s2);
        
        
    }
    
    
    
    
    
    
    function array2jslist($array) {
        $r = "";
        if(is_array($array)) {
            $r .= '[';
            foreach($array as $key => $value ) {
                $r .= $this->array2jslist($value);
                if($key < count($array) - 1) $r .= ',';
            }
            $r .= ']';
        } else {
            if(is_numeric($array))
                $r .= $array;
            else 
                $r .= "'".$array."'";
        }
        return $r;
    }
}
