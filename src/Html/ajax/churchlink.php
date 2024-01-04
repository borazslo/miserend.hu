<?php

namespace App\Html\Ajax;

class ChurchLink extends Ajax {

    public function __construct() {
        
        $action = \App\Request::InArrayRequired('action', ['delete','add']);

        header("Content-Type: text/plain");                         
        switch ($action) {
            case 'delete':
                        
                $id = \App\Request::IntegerRequired('id');
                $link = \App\Model\ChurchLink::find($id);
                if(!$link)
                    throw new \Exception("There is no ChurchLink with id: ".$id);
                if(!$link->church) {
                    $link->delete();
                    throw new \Exception("There is no Church with id: ".$link->church_id);
                }

                if(!$link->church->WriteAccess) 
                    throw new \Exception("Hozzáférés megtagadva.");
                
                if($link->delete()) 
                    echo 'ok';
                break;
            
            case 'add':
                
                $church_id = \App\Request::IntegerRequired('church_id');
                $church = \App\Model\Church::find($church_id);
                if(!$church) {
                    throw new \Exception("There is no Church with id: ".$church_id);
                }
                if(!$church->WriteAccess) 
                    throw new \Exception("Hozzáférés megtagadva.");
                
                $link = \App\Model\ChurchLink::create([
                    'church_id' => $church_id,
                    'href' => \App\Request::TextRequired('href'),
                    'title' => \App\Request::Text('title')
                ]);
                $link->save();

                echo '<div class="church-link" data-link-id="'.$link->id.'">';
                echo $link->html;
                echo '</div>';
                
                break;
        }                
    }
}
