<?php

namespace Api;

class Login extends Api {

    public $requiredVersion = ['>=',4]; // API v4-től érhető el

    public function docs() {

        $docs = [];
        $docs['title'] = 'Felhasználó azonosítás';
        $docs['input'] = [
            'username' => [
                'required',
                'string',
                'A felhasználó regisztrált neve (e-mail címmel nem működik)'
            ],
            'password' => [
                'required',
                'string',
                'A felhasználó jelszava (egyszerű szövegként, egyszer talán lesz https)'
            ]
        ];

        $docs['description'] = <<<HTML
        <p>Bizonyos API funkciókhoz szükséges (pl. kedvencek szinkronizálása) vagy lehetséges (pl. visszajelzés) a felhasználó azonosítása. A megfelelő url-re JSON formátumban küldött név-jelszó páros érvényessége esetén egy token-t küld vissza a rendszer JSON formátumban.</p>
        <p><strong>Elérhető:</strong> <code>http://miserend.hu/api/v4/login</code></p>
        HTML;

        $docs['response'] = <<<HTML
        <ul>
            <li>„error”: <strong>0</strong>, ha nincs hiba. <strong>1</strong>, ha van valami hiba.</li>
            <li>„token” (<em>varchar(32)</em>): Az azonosításhoz szükséges token. Érvényességi ideje a <code>config.php</code>-ban van beállítva.</li>
            <li>„text” (opcionális): „error:1” esetén a hiba szöveges leírása</li>
        </ul>
        HTML;

        return $docs;
    }

    

    public function validateInput() {
        if (!isset($this->input['username']) OR ! isset($this->input['password'])) {
            throw new \Exception("JSON input misses variables.");
        }
    }

    public function run() {
        parent::run();
        $this->getInputJson();
        $userId = \User::login($this->input['username'], $this->input['password']);
        if (!$userId) {
            throw new \Exception("Invalid username or password.");
        }
        $token = \Token::create($userId, 'API');

        $this->return['token'] = $token;
    }

}
