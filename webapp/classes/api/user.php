<?php

namespace Api;

class User extends Api {

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
        <li>„favorites": A felhasználó kedvenc misézőhelyeinek azonosítóinak listája.</li>
        <li>„responsibilities": A felhasználó által gondozott misézőhelyek azonosítóinak listája.</li>
        <li>„text” (opcionális): „error:1” esetén a hiba szöveges leírása.</li>
        </ul>
        HTML;

        return $docs;
    }

    
    public function validateInput() {
        if (!isset($this->input['token'])) {
            throw new \Exception("JSON input misses token.");
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
        
        $data = array(
            'username' => $user->username,
            'nickname' => $user->nickname,
            'name' => $user->name,
            'email' => $user->email,
            'favorites' => [],
            'responsibilities' => []
        );


        $user->getFavorites();
        foreach ($user->favorites as $favorite)
            $data['favorites'][] = $favorite['tid'];


        $user->getResponsabilities();
        $responsibilities = [];        
        if (isset($user->responsibilities['church']['allowed']) && is_iterable($user->responsibilities['church']['allowed'])) {
            foreach ($user->responsibilities['church']['allowed'] as $church) {
            $responsibilities[] = $church->church_id;
            }
        }
        $data['responsibilities'] = $responsibilities;
                
        $this->return['user'] = $data;
    }

}
