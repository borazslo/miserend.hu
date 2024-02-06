<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Api;

class Report extends Api
{
    public $requiredFields = ['tid', 'pid'];

    public static function factoryCreate()
    {
        $api = new Api();
        $api->getInputJson();

        if (!isset($api->input['token'])) {
            return new ReportByAnonym();
        } else {
            return new ReportByUser();
        }
    }

    public function validateInput()
    {
        // TODO: !isValidChurchId()?
        if (!is_numeric($this->input['tid'])) {
            throw new \Exception("Wrong format of 'tid' in JSON input.");
        }
        if (!\in_array($this->input['pid'], [0, 1, 2])) {
            throw new \Exception("Wrong format of 'pid' in JSON input.");
        }
        if (2 === $this->input['pid'] && !isset($this->input['text'])) {
            throw new \Exception("In the case of 'pid=2' the 'text' field required in JSON input.");
        }
        if ($this->version > 3 && (!isset($this->input['dbdate']) || false == strtotime($this->input['dbdate']))) {
            throw new \Exception("Field 'dbdate' is required after API version 3 in JSON input.");
        }
        if (isset($this->input['timestamp']) && false == strtotime($this->input['timestamp'])) {
            throw new \Exception("Wrong format of 'timestamps' in JSON input.");
        }
    }

    public function run()
    {
        parent::run();
        $this->getInputJson();
        $this->prepareUser();
        $this->prepareRemark();

        try {
            $this->remark->save();
            $this->remark->emails();
            $this->return['text'] = 'Köszönjük. Elmentettük.';
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function prepareRemark()
    {
        $this->remark = new \App\Legacy\Model\Remark();

        $this->remark->church_id = $this->input['tid'];
        $this->remark->nev = $this->user->name;
        if (isset($this->user->email)) {
            $this->remark->email = $this->user->email;
        }
        if (isset($this->input['timestamp'])) {
            $this->remark->created_at = $this->input['timestamp'];
        }

        if ($this->user->uid > 0) {
            $this->user->active();
        }
        $this->prepareRemarkText();
    }

    public function prepareRemarkText()
    {
        if (!isset($this->input['text'])) {
            $this->input['text'] = '';
        } else {
            $this->input['text'] = sanitize($this->input['text']).'<br/>';
        }

        switch ($this->input['pid']) {
            case 0:
                $this->input['text'] .= ' Helytelen pozíció.';
                break;
            case 1:
                $this->input['text'] .= ' Helytelen miseidőpont.';
                break;
        }

        $this->remark->leiras = "Mobilalkalmazáson keresztül érkezett információ:<br/>\n".$this->input['text']."<br/>\n <i>verzió:".$this->version.', pid:'.$this->input['pid'].'</i>';
        if (isset($this->input['dbdate'])) {
            if (!is_numeric($this->input['dbdate'])) {
                $this->input['dbdate'] = strtotime($this->input['dbdate']);
            }
            $this->remark->leiras .= '<i>, adatbázis: '.date('Y-m-d H:i', $this->input['dbdate']).'</i>';

            $church = \App\Legacy\Model\Church::find($this->remark->church_id)->toArray();
            $updated = strtotime($church['frissites']);
            if ($this->input['dbdate'] < $updated) {
                $this->remark->leiras .= "<br/>\n<br/>\n<strong>Figyelem! Elavult adatok alapján történt a bejelentés!</strong>";
            }
        }
    }
}
