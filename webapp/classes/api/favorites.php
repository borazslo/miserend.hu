<?php

namespace Api;

class Favorites extends Api {

    public $requiredVersion = ['>=',4]; // API v4-től érhető el

     public function docs() {

        $docs = [];
        $docs['title'] = 'Felhasználó kedvenc templomai';
        $docs['input'] = [
            'token' => [
                'required',
                'integer',
                'Egy érvényes token'
            ],
            'add' => [
                'optional',
                'list of integer', 
                'A kedvencekhez hozzáadni kívánt templomok azonosítójának listája/tömbje.',
                ],
            'remove' => [
                'optional',
                'list of integer', 
                'A kedvencekből törölni kívánt templomok azonosítójának listája/tömbje.',
                ]                
        ];

        $docs['description'] = <<<HTML
        <p>A felhasználó kedven templomait le lehet kérdezni, valamint hozzá lehet adni vagy el lehet belőle venni a megfelelő url-re JSON formátumban küldött token érvényessége esetén. A rendszer JSON formátumban válaszol a kedvenc templomok megújult listájával. Először a hozzáadást hajtja végre, majd a törlést. Nem tér vissza hibajelzéssel, ha az adott templomazonosító már szerepel a kedvencek között. És akkor sem, ha olyan törlésére kerül sor, ami nem is szerepelt a kedvencek között.</p>
        <p><strong>Elérhető:</strong> <code>http://miserend.hu/api/v4/user/favorites</code></p>
        HTML;

        $docs['response'] = <<<HTML
        <ul>
            <li>„error”: <strong>0</strong>, ha nincs hiba. <strong>1</strong>, ha van valami hiba.</li>
            <li>„favorites": A felhasználó kedvenc templomainak azonosítóinak frissült listája/tömbje.</li>
            <li>„text” (opcionális): „error:1” esetén a hiba szöveges leírása</li>
        </ul>
        HTML;

        return $docs;
    }

    public function validateInput() {
        if (!isset($this->input['token'])) {
            throw new \Exception("JSON input misses token.");
        }
        foreach (array('add', 'remove') as $method) {
            if (isset($this->input[$method])) {
                if (!is_array($this->input[$method]) AND ! is_numeric($this->input[$method])) {
                    throw new \Exception("Wrong format of '$method' in JSON input.");
                } elseif (!is_array($this->input[$method])) {
                    $this->input[$method] = array($this->input[$method]);
                }
                foreach ($this->input[$method] as $tid) {
                    if (!is_numeric($tid)) {
                        throw new \Exception("Wrong value in '$method' of JSON input.");
                    }
                }
            }
        }
    }

    public function run() {
        parent::run();
        $this->getInputJson();

        $token = \Eloquent\Token::where('name',$this->input['token'])->first();
        if(!$token or !$token->isValid) {
            throw new \Exception("Invalid token.");
        }    

        //TODO: delete global somehow
        global $user;
        $user = new \User($token->uid);

        if (isset($this->input['remove'])) {
            if (!$user->removeFavorites($this->input['remove'])) {
                throw new \Exception("Could not remove favorites.");
            }
        }
        if (isset($this->input['add'])) {
            if (!$user->addFavorites($this->input['add'])) {
                throw new \Exception("Could not add favorites.");
            }
        }

        $favorites = array();
        $user->getFavorites();
        foreach ($user->favorites as $favorite) {
            $favorites[] = $favorite['tid'];
        }

        $this->return['favorites'] = $favorites;
        
    }

}
