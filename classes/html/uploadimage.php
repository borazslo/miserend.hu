<?php

namespace Html;

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
        
        //Mail küldése az egyházmegyei felelősnek
        $this->church->MgetReligious_administration();
        if (isset($this->church->religious_administration->diocese->responsible)) {
            foreach ($this->church->religious_administration->diocese->responsible as $responsible) {
                $responsibleUser = new \User($responsible);
                if ($responsibleUser->uid > 0) {
                    $mail = new \Eloquent\Email();                
                    $mail->render('image_diocese',$this);
                    $mail->send($responsibleUser->email);
                }
            }
        }

        if (!empty($this->church->kontaktmail)) {
            //Mail küldés az karbantartónak felelősnek
            $mail = new \Eloquent\Email();                
            $mail->render('image_contact',$this);
            $mail->send($this->church->kontaktmail);
        }

        //Mail küldése a debuggernek, hogy boldog legyen
        global $config;
        $mail = new \Eloquent\Email();
        $mail->render('image_debug',$this);
        $mail->send($config['mail']['debugger']);

        exit;
    }

}
