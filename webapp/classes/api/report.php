<?php

namespace Api;

class Report extends Api {

    public $requiredFields = array('tid','pid');

    static function factoryCreate() {
        $api = new Api();
        $api->getInputJson();


        if (!isset($api->input['token'])) {
            return new ReportByAnonym();
        } else {
            return new ReportByUser();
        }
    }
    
    public function docs() {

        $docs = [];
        $docs['title'] = 'Visszajelzés / jelentés';
        $docs['input'] = [
            'tid' => [
                'required',
                'integer',
                'a templom azonosítója (mint az url-ben)'
            ],
            'text' => [
                'opcionális; „pid:2” esetén kötelező',
                'string',
                'szöveges üzenet'
            ],
            'timestamp' => [
                'optional',
                'string',
                'a beküldés időpontja (hiánya esetén aktuális pillanat)',
                'current timestamp'
            ],
            'email' => [
                'optional',
                'string',
                'a beküldő email címe, hogy tudjunk neki válaszolni'
            ],
            'token' => [
                'optional',
                'string',
                'bejelentkezett felhasználó esetén hasznos. Felülírja az „email” értéket. Csak v4+ esetén használható'
            ],
            'pid' => [
                'required',
                'enum(0,1,2)',
                'visszajelzés típusa: 0 - rossz pozíció, 1 - hibás mise adatok, 2 - egyéb / ill. előbbiek részletezve'
            ],
            'dbdate' => [
                '≤v3 optional, v4+ kötelező',
                'timestamp or date',
                'a használt adatbázis letöltöttségének ideje, timestamp vagy ÉÉÉÉ-HH-NN ÓÓ:PP:MM vagy ÉÉÉÉ-HH-NN',
            ]
        ];

        $docs['description'] = <<<HTML
        <p>Fontos, hogy a felhasználók tudják jelezni, ha valami hibát találnak a miserendben vagy a templom adataiban.<br>
        JSON formátumba kell küldeni az adatokat és JSON formátumban válaszol az API.</p>
        <p><strong>Elérhető:</strong> <code>http://miserend.hu/api/v3/report</code></p>
        HTML;

        $docs['response'] = <<<HTML
        <ul>
            <li>„error”: <strong>0</strong>, ha nincs hiba. <strong>1</strong>, ha van valami hiba.</li>
            <li>„text” (opcionális): „error:1” esetén a hiba szöveges leírása</li>
        </ul>
        HTML;

        return $docs;
    }



    public function validateInput() {
        //TODO: !isValidChurchId()?
        if (!is_numeric($this->input['tid'])) {
            throw new \Exception("Wrong format of 'tid' in JSON input.");
        }
        if (!in_array($this->input['pid'], array(0, 1, 2))) {
            throw new \Exception("Wrong format of 'pid' in JSON input.");
        }
        if ($this->input['pid'] === 2 AND ! isset($this->input['text'])) {
            throw new \Exception("In the case of 'pid=2' the 'text' field required in JSON input.");
        }
        if ($this->version > 3 AND ( !isset($this->input['dbdate']) OR strtotime($this->input['dbdate']) == false )) {
            throw new \Exception("Field 'dbdate' is required after API version 3 in JSON input.");
        }
        if (isset($this->input['timestamp']) AND strtotime($this->input['timestamp']) == false) {
            throw new \Exception("Wrong format of 'timestamps' in JSON input.");
        }
    }

    public function run() {
        parent::run();
        $this->getInputJson();
        $this->prepareUser();
        $this->prepareRemark();

        try {
            $this->remark->save();
            $this->remark->emails();
            $this->return['text'] = 'Köszönjük. Elmentettük.';
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function prepareRemark() {        
        $this->remark = new \Eloquent\Remark;

        $this->remark->church_id = $this->input['tid'];
        $this->remark->nev = $this->user->name;
        if(isset($this->user->email))
            $this->remark->email = $this->user->email;
        if (isset($this->input['timestamp'])) {
            $this->remark->created_at = $this->input['timestamp'];
        }
        
        if ($this->user->uid > 0) {
            $this->user->active();
        }
        $this->prepareRemarkText();
    }

    public function prepareRemarkText() {

        if (!isset($this->input['text'])) {
            $this->input['text'] = "";
        } else {
            $this->input['text'] = sanitize($this->input['text'])."<br/>";
        }

        switch ($this->input['pid']) {
            case 0:
                $this->input['text'] .= " Helytelen pozíció.";
                break;
            case 1:
                $this->input['text'] .= " Helytelen miseidőpont.";
                break;
        }

        $this->remark->leiras = "Mobilalkalmazáson keresztül érkezett információ:<br/>\n" . $this->input['text'] . "<br/>\n <i>verzió:" . $this->version . ", pid:" . $this->input['pid'] . "</i>";
        if (isset($this->input['dbdate'])) {
            if (!is_numeric($this->input['dbdate'])) {
                $this->input['dbdate'] = strtotime($this->input['dbdate']);
            }
            $this->remark->leiras .= "<i>, adatbázis: " . date("Y-m-d H:i", $this->input['dbdate']) . "</i>";

            $church = \Eloquent\Church::find($this->remark->church_id)->toArray();
            $updated = strtotime($church['frissites']);
            if ($this->input['dbdate'] < $updated) {
                $this->remark->leiras .= "<br/>\n<br/>\n<strong>Figyelem! Elavult adatok alapján történt a bejelentés!</strong>";
            }
        }
    }

}
