<?php

namespace Api;

class Login extends Api {

    public $requiredVersion = ['>=',4]; // API v4-től érhető el

    public function docs() {

        $docs = [];
        $docs['title'] = 'Felhasználó adatainak lekérdezése';
        $docs['input'] = [
            'token' => [
                'required',
                'string',
                'Egy érvényes token'
            ]
        ];

        $docs['description'] = <<<HTML
        <p>A megfelelő url-re JSON formátumban küldött token érvényessége esetén a rendszer JSON formátumban visszaküldi a felhasználó adatait.</p>
        <p><strong>Elérhető:</strong> <code>http://miserend.hu/api/v4/user</code></p>
        HTML;

        $docs['response'] = <<<HTML
        <ul>
        <li>„error": <strong>0</strong>, ha nincs hiba. <strong>1</strong>, ha van valami hiba.</li>
        <li>„username”: A felhasználó felhasználó neve. (Soha nem üres.)</li>
        <li>„nickname”: Becenév vagy megszólítás. (Lehetséges, hogy üres.)</li>
        <li>„name”: Teljes név. (Lehetséges, hogy üres.)</li>
        <li>„email”: A felhasználó email címe. (Elvileg nem lehet üres.)</li>
        <li>„favorites": A felhasználó kedvenc templomainak azonosítóinak listája/tömbje.</li>
        <li>„text” (opcionális): „error:1” esetén a hiba szöveges leírása.</li>
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
