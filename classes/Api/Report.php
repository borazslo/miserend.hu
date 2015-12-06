<?php

namespace Api;

class Report extends Api {

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
        printr($this);
        exit;
        try {
            $this->remark->save();
            $this->remark->emails();
            $this->return['text'] = 'Köszönjük. Elmentettük.';
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function prepareRemark() {
        $this->remark = new \Remark();
        $this->remark->tid = $this->input['tid'];
        $this->remark->name = $this->user->name;
        $this->remark->email = $this->user->email;
        if (isset($this->input['timestamp'])) {
            $this->remark->timestamp = $this->input['timestamp'];
        }
        if (isset($this->input['dbdate'])) {
            $this->remark->dbdate = $this->input['dbdate'];
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
            $this->input['text'] = sanitize($this->input['text']);
        }

        $this->remark->text = "Mobilalkalmazáson keresztül érkezett információ:\n" . $this->input['text'] . "\n <i>verzió:" . $this->version . ", pid:" . $this->input['pid'] . "</i>";
        if (isset($this->input['dbdate'])) {
            if (!is_numeric($this->input['dbdate'])) {
                $this->input['dbdate'] = strtotime($this->input['dbdate']);
            }
            $this->remark->text .= "<i>, adatbázis: " . date("Y-m-d H:i", $this->input['dbdate']) . "</i>";

            $church = new \Church($this->remark->tid);
            $updated = strtotime($church->frissites);
            if ($this->input['dbdate'] < $updated) {
                $this->remark->text .= "\n\n<strong>Figyelem! Elavult adatok alapján történt a bejelentés!</strong>";
            }
        }
    }

}
