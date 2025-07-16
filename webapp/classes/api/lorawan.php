<?php

namespace Api;

class LoRaWAN extends Api {

    public $requiredFields = array('deduplicationId','time','deviceInfo','object');

    public $requiredVersion = ['>=',4]; // API v4-től érhető el

     public function docs() {

        $docs = [];
        $docs['title'] = 'Gyóntatás jelentése LoRaWAN eszközről';
        $docs['input'] = [
            'deduplicationId' => [
                'required',
                'UUID',
                'Fontos, hogy minden külön adat külön UUID-vel érkezzen. Kétszer azonos adatot nem fogadunk.'
            ],
            'time' => [
                'required',
                'timestamp',
                'Az esemény időbélyege YYYY-MM-DDTHH:MM:SS.sss+00:00 formátumban'
            ],
            'deviceInfo' => [
                'required',
                'array',
                'Az eszköz információi, beleértve a devEui-t és a címkéket.',
            ],
            'deviceInfo/tags/local_id' => [
                'required',
                'integer',
                'Egy misézőhelyen több eszköz is lehet, ezért szükséges a helyi azonosító.',
            ],
            'deviceInfo/tags/templom_id' => [
                'required',
                'integer',
                'A misézőhely azonosítója.',
            ],            
            'deviceInfo/devEui' => [
                'required',
                '',
                'A konkrét eszköz egyedi azonosítója, hexadecimális formátumban (16 karakter).',
            ],
            'object' => [
                'required',
                'object',
                'Az eszközről itt jönnek a státuszt érintő adatok.',
            ],
            'object/Mód' => [
                'required',
                'enum(1,2)',
                'Az eszköz működési módja: 1 - ajtó állapot, 2 - vízszivárgás érzékelés.',
            ],
            'object/Status_Leak' => [
                'optional/required',
                'enum(1,0)',
                'Mód 2 esetén kötelező. Az eszköz vízszivárgás érzékelésének állapota: 1 - vízszivárgás, 0 - nincs vízszivárgás.',
            ],
            'object/Status_Door' => [
                'optional/required',
                'enum(1,0)',
                'Mód 1 esetén kötelező. Az eszköz ajtó állapotának érzékelése: 1 - nyitva, 0 - zárva.',
            ],                        
        ];

        $docs['description'] = <<<HTML
        <strong><i>Ez még egy kísérleti API, használata csak saját felelősségre!</i></strong>
        <p>Ez az API lehetővé teszi a LoRaWAN eszközök által küldött gyóntatási adatok jelentését. A rendszer ellenőrzi a bemeneti adatokat, és ha minden rendben van, elmenti az adatokat az adatbázisba.</p>
        <p>A jelenleg használt eszközök egyedi kommunikációs gyakorlata miatt van szükség ilyen részletes és szokatlan bemeneti adatokra.</p>
        <p>További információ a gyóntatásokról és a LoRaWAN eszközökről a <a href="/staticpage/confessions">dokumentációban</a> található.</p>
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
        
        if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $this->input['deduplicationId'])) {
            throw new \Exception("JSON input 'deduplicationId' is not in valid form.");
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}\+\d{2}:\d{2}$/', $this->input['time'])) {
            throw new \Exception("JSON input 'time' is not in valid form. Expected format: YYYY-MM-DDTHH:MM:SS.sss+00:00");
        }

        if (!is_array($this->input['deviceInfo'])) {
            throw new \Exception("JSON input 'deviceInfo' should be an array.");
        }
        if (!isset($this->input['deviceInfo']['tags']['local_id'])|| !is_numeric($this->input['deviceInfo']['tags']['local_id'])) {
            throw new \Exception("JSON input 'deviceInfo[tags][local_id]' is required and should be a number.");
        }
        if (!isset($this->input['deviceInfo']['tags']['templom_id']) || !is_numeric($this->input['deviceInfo']['tags']['templom_id'])) {
            throw new \Exception("JSON input 'deviceInfo[tags][templom_id]' is required and should be a number.");
        }
        if (!isset($this->input['deviceInfo']['devEui']) || !preg_match('/^[a-f0-9]{16}$/', $this->input['deviceInfo']['devEui'])) {
            throw new \Exception("JSON input 'deviceInfo[deveui]' is required and should be in a valid format.");
        }

        if (!is_array($this->input['object'])) {
            throw new \Exception("JSON input 'object' should be an array.");
        }


	}

    public function run() {
        parent::run();

        $this->getInputJson();


        $confession = new \Eloquent\Confession();
        
        if ($this->input['object']['Mód'] == 1) {
            $confession->status = ($this->input['object']['Satus_Door'] == 1) ? 'ON' : 'OFF';
        }
        if ($this->input['object']['Mód'] == 2) {
            $confession->status = ($this->input['object']['Status_Leak'] == 1) ? 'ON' : 'OFF';
        }

        
        $confession->local_id = $this->input['deviceInfo']['tags']['local_id'];
        $confession->church_id = $this->input['deviceInfo']['tags']['templom_id'];
        $confession->deduplicationId = $this->input['deduplicationId'];
        $confession->timestamp = date('Y-m-d H:i:s', strtotime($this->input['time']));
        $confession->fulldata = json_encode($this->input['object']);
        //$confession->fulldata = json_encode($this->input);
        $confession->save();
        
    }

}
