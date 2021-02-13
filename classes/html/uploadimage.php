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

        $eszrevetel = "<a href=\"https://miserend.hu/templom/" . $this->church->id . "\">" . $this->church->nev . " (";
        if ($this->church->ismertnev != "")
            $eszrevetel .= $this->church->ismertnev . ", ";
        $eszrevetel .= $this->church->varos . ")</a><br/>\n";
        $eszrevetel .= "<img src='https://miserend.hu/" . $photo->url . "'><br/>\n";
        $eszrevetel .= $photo->title . "<br/><br/>\n";
        $eszrevetel .= "https://miserend.hu/" . $photo->url . "\n";

        $mail = new \Eloquent\Email();
        $mail->subject = "Miserend - új kép érkezett";

        //Mail küldése az egyházmegyei felelősnek
        $this->church->MgetReligious_administration();
        if (isset($this->church->religious_administration->diocese->responsible)) {
            foreach ($this->church->religious_administration->diocese->responsible as $responsible) {
                $responsibleUser = new \User($responsible);
                if ($responsibleUser->uid > 0) {
                    $mail->body = "Kedves egyházmegyei felelős!\n\n<br/><br/>Az egyházmegyéhez tartozó egyik templomhoz új kép érkezett.<br/>\n" . $eszrevetel;
                    $mail->send($responsibleUser->email);
                }
            }
        }

        if (!empty($this->church->kontaktmail)) {
            //Mail küldés az karbantartónak felelősnek
            $mail->body = "Kedves templom karbantartó!\n\n<br/><br/>Az egyik karbantartott templomhoz új kép érkezett.<br/>\n" . $eszrevetel;
            $mail->send($this->church->kontaktmail);
        }

        //Mail küldése a debuggernek, hogy boldog legyen
        $mail->body = "Kedves admin!\n\n<br/><br/>Az egyik templomhoz új kép érkezett.<br/>\n" . $eszrevetel;
        global $config;
        $mail->send($config['mail']['debugger']);

        exit;
    }

}
