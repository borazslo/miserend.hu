<?php

namespace Html\Email;

class RemarkFeedback extends Email {

    public function preparePage($path) {
        $this->setTitle("Észrevételre reagálás");

        $rid = $path[0];

        $textvars = array();
        if (!is_numeric($rid)) {
            throw new \Exception("Helytelen észrevétel azonosító.");
        }

        $this->remark = new \Remark($rid);
        $this->church = $this->remark->church;

        $this->mail->to = $this->remark->email;
        $this->mail->subject = "Miserend - Köszönjük az észrevételt!";

        if (isset($path[1])) {
            $type = $path[1];
        } else {
            $type = \Request::Text('type');
        }

        if ($type) {
            $this->mail->type = "feedback_" . $type;
            global $twig;
            $body = $twig->render('email/remarkfeedback' . strtolower($type) . '.twig', (array) $this);
            $this->mail->body= $body;
        }
    }

    public function send() {
        parent::send();
        $rid = \Request::Integer('rid');
        $remark = new \Remark($rid);
        $remark->addComment("email küldve: " . $this->mail->type);
    }

}
