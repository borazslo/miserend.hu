<?php

namespace Html\Ajax;

class ChurchLink extends Ajax {

    public function __construct() {
        $id = \Request::IntegerRequired('id');
        $action = \Request::InArrayRequired('action', ['delete']);
        
        $link = \Eloquent\ChurchLink::find($id);
        
        if(!$link)
            throw new \Exception("There is no ChurchLink with id: ".$id);
        if(!$link->church) {
            $link->delete();
            throw new \Exception("There is no Church with id: ".$link->church_id);
        }
        
        if(!$link->church->WriteAccess) 
            throw new \Exception("Hozzáférés megtagadva.");
        
        
        header("Content-Type: text/plain"); 
        switch ($action) {
            case 'delete':
                if($link->delete()) 
                    echo 'ok';
                break;
            
        }                
    }
}
