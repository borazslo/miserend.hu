<?php

namespace App\Api;

class ReportByAnonym extends Report {

    public function prepareUser() {
        $this->user = new \App\User();
        $this->user->name = "Mobil felhasználó";
        if (isset($this->input['email'])) {
            $this->user->email = sanitize($this->input['email']);
        }
    }

}
