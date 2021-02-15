<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class UploadImage extends Html {

    public function __construct($path) {
        $this->tid = $path[0];
        $this->church = \Eloquent\Church::find($this->tid);
        $this->pageDescription = 'új kép feltöltése';

        if (isset($_REQUEST['upload'])) {
            $this->ajax();
            exit;
        }
    }

    function ajax() {
        $tid = $_POST['id'];
        if ($tid != $this->tid) {
            throw new \Exception("The church.id of the page and the form are not the same.");
        }

        $photo = new \Eloquent\Photo();
        $photo->church_id = $this->church->id;
        $photo->uploadFile($_FILES["FileInput"]);

        $photo->title = htmlspecialchars($_REQUEST['description']);
        $photo->save();
        echo "Siker! Feltöltöttük. Jöhet a következő!<br/><img src='" . $photo->smallUrl . "'>";

        $this->photo = $photo;
        
        
        /*
         * miserend adminiok
         * egyházmegyei felelős(ök)
         * templom feltöltésre jogosult felhasználó
         */
        $emails = [];        
        /* Miserend Adminok */
        $admins = DB::table('user')->where('jogok','LIKE','%miserend%')->where('notifications',1)->get();
        foreach($admins as $admin) {
           $emails[$admin->email] = ['image_admin',$admin->email,$admin];
        }              
        /* Egyházmegyei felelős (csak felhasználónév alapján) */
        $responsabile = DB::table('egyhazmegye')->select('user.*')->where('egyhazmegye.id',$this->church->egyhazmegye)->leftJoin('user','user.login','=','egyhazmegye.felelos')->where('notifications',1)->first();
        if($responsabile) {
            $emails[$responsabile->email] = ['image_diocese', $responsabile->email, $responsabile];
        }
        /* Templom felelős. Még csak egy!! */
        $responsabile = DB::table('user')->where('login',$this->church->letrehozta)->where('notifications',1)->first();
        if($responsabile) {
            $emails[$responsabile->email] = ['image_responsible', $responsabile->email, $responsabile];
        }
        
        foreach($emails as $email) {
            if(isset($email[2])) $this->addressee = $email[2];
            else $this->addressee = false;
            $mail = new \Eloquent\Email();                
            $mail->render($email[0],$this);
            $mail->send($email[1]);
            
        }
              
        exit;
    }

}
