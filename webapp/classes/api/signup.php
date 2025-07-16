<?php

namespace Api;

class Signup extends Api {

    public $requiredVersion = ['>=',4]; // API v4-től érhető el

     public function docs() {

        $docs = [];
        $docs['title'] = 'Új felhasználó regisztrálása';
        $docs['input'] = [
            'username' => [
                'required',
                'string(20)',
                'a felhasználó regisztrált neve. (Maximum 20 karakter. Ékezetek és speciális karakterek nélkül'
            ],
            'email' => [
                'required',
                'string',
                'a felhasználó email címe. (A regisztrációról értesítést kap.)'
            ],
            'password' => [
                'required',
                'string',
                'a jelszó egyszerű szövegként. (Jelenleg nincsen összetettségi követelmény.)'
            ],
            'nickname' => [
                'optional',
                'string',
                'Becenév vagy megszólítás.'
            ],
            'name' => [
                'optional',
                'string',
                'Teljes név.'
            ]
        ];


        $docs['description'] = <<<HTML
        <p>Az API-n keresztül lehetséges új miserend felhasználó regisztrálása is. A megfelelő url-re JSON formátumban küldött adatok esetén JSON választ ad a rendszer.</p>
        <p><strong>Elérhető:</strong> <code>http://miserend.hu/api/v4/signup</code></p>
        HTML;

        $docs['response'] = <<<HTML
        <ul>
            <li>„error”: <strong>0</strong>, ha nincs hiba. <strong>1</strong>, ha van valami hiba.</li>
            <li>„text” (opcionális): „error:1” esetén a hiba szöveges leírása. Ez olyan fontos üzeneteket tartalmazhat, mint hogy már foglalt a felhasználónév.</li>
        </ul>
        HTML;

        return $docs;
    }

    public function validateInput() {
        if (!isset($this->input['username']) OR ! isset($this->input['email']) OR ! isset($this->input['password'])) {
            throw new \Exception("JSON input misses variables: username and/or email and/or password");
        }
    }

    public function run() {
        parent::run();
        $this->getInputJson();
        $newuser = new \User();
        $validFields = array('username', 'email', 'password', 'nickname', 'name');
        $fieldsToSubmit = array();
        foreach ($validFields as $field) {
            if ($this->input[$field] AND $this->input[$field] != '') {
                $fieldsToSubmit[$field] = $this->input[$field];
            }
        }

        $success = $newuser->submit($fieldsToSubmit);

        $messages = \Message::getToShow();
        if (!$success) {
            $exceptionTexts = array();            
            foreach ($messages as $message) {
                $exceptionTexts[] = $message['text'];
            }
            throw new \Exception(implode("\n", $exceptionTexts));
        }
    }

}
