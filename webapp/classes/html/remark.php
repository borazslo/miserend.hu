<?php

namespace Html;

class Remark extends Html {

    public $template;

    public function __construct($path) {
        $this->action = $path[0];
        $this->tid = $rid = $path[1];

        $this->church = \Eloquent\Church::find($this->tid);
        $this->disclaimer = 'Figyelem! Nem állunk közvetlen kapcsolatban a plébániákkal ezért plébániai ügyekben (pl. keresztelési okiratok, stb.) sajnos nem tudunk segíteni.';
        
        switch ($this->action) {
            case 'list':
                $this->pageList();
                $this->template = 'remark_list.twig';                        
                break;

            case 'addform':                
                $this->template = 'remark_form.twig';
                break;

            case 'add':
                $this->pageAdded();
                $this->template = 'remark.twig';
                break;
        }
    }
    
    function pageList() {
        if (\Request::Simpletext('remark') == 'modify') {
            $rid = \Request::IntegerRequired('rid');
            $remark = \Eloquent\Remark::find($rid);
            
            $remark->allapot = \Request::Simpletext('state');
            $remark->admindatum = date('Y-m-d H:i:s');
            
            $remark->appendComment(\Request::Text('adminmegj'));
            $remark->save();
            
            if($this->tid != $remark->church_id) { // Hogy ne lehessen csalni
                $this->tid = $remark->church_id;
                $this->church = \Eloquent\Church::find($this->tid);
            }
        }
       
        global $user;
        if (!$this->church->writeAccess) {
            addMessage("Hiányzó jogosultság. Elnézést.", "danger");
            return;
        }
        
        $this->church->remarks;        
    }
       
   
    function pageAdded() {
                
        $remark = new \Eloquent\Remark;

        $remark->church_id = $this->church->id;
        $remark->allapot = 'u';
        $remark->leiras = \Request::TextRequired('leiras');
        $remark->email = \Request::TextRequired('email');
        $remark->nev = \Request::Text('nev');
        if($remark->nev == '') $remark->nev = $remark->email;
        
       
        // Belépett felhasználónál hidden email és név adat volt, de nem bízunk benne
        global $user;
        if ($user->username != "*vendeg*") {
            $remark->login = $user->username;
            $remark->email = $user->email;
        }
        
        $megbizhato = \Eloquent\Remark::select('megbizhato')->where('email',$remark->email)->orderBy('created_at','desc')->limit(1)->first();
        if($megbizhato)
            $remark->megbizhato = $megbizhato->megbizhato;
        else
            $remark->megbizhato = '?';
                                
        if (!$remark->save())
            addMessage("Nem sikerült elmenteni az észrevételt. Sajánljuk.", "danger");
                
        if (!$remark->emails())
            addMessage("Nem sikerült elküldeni az értesítő emaileket.", "warning");
                
        global $config;
        $this->debug = $config['debug'];        
        
    }

}
