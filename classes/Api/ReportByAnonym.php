<?php

namespace Api;

class ReportByAnonym extends Report {

    public function prepareUser() {
        $this->user = new User();
        $this->user->name = "Mobil felhasználó";
        if ($this->input['email']) {
            $this->user->email = sanitize($this->input['email']);
        }
    }

}
