<?php

namespace App\Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class AutocompleteKeyword extends Ajax {

    public function __construct() {
        $this->input = $_REQUEST;
        if (!preg_match('/\*$/', $this->input['text'])) {
            $return[] = array('label' => $_REQUEST['text'] . "* <i>(Minden " . $_REQUEST['text'] . "-al kezdődő)</i>", 'value' => $_REQUEST['text'] . "*");
        }
        if (!preg_match('/^\*(.*)\*$/', $this->input['text'])) {
            if (preg_match('/^\*/', $this->input['text'])) {
                $return[] = array('label' => $_REQUEST['text'] . "* <i>(Minden " . $_REQUEST['text'] . "-t tartalmazó)</i>", 'value' => $_REQUEST['text'] . "*");
            } else if (preg_match('/\*$/', $this->input['text'])) {
                $return[] = array('label' => "*" . $_REQUEST['text'] . " <i>(Minden " . $_REQUEST['text'] . "-t tartalmazó)</i>", 'value' => "*" . $_REQUEST['text']);
            } else {
                $return[] = array('label' => "*" . $_REQUEST['text'] . "* <i>(Minden " . $_REQUEST['text'] . "-t tartalmazó)</i>", 'value' => "*" . $_REQUEST['text'] . "*");
            }
        }
        if (!preg_match('/\*$/', $this->input['text'])) {
            $this->input['text'] .= "*";
        }
        $keyword = preg_replace("/\*/", "%", $this->input['text']);
        $keywordEmpty = preg_replace("/%/", "", $keyword);
        $administratives = \App\Model\KeywordShortcut::where('type', 'name')->where('value', 'LIKE', $keyword)
                        ->groupBy('value')->orderBy('value')->take(10)->get();
        foreach ($administratives as $administrative) {
            $label = preg_replace('/(' . $keywordEmpty . ')/i', '<strong>$1</strong>', $administrative->value);
            $return[$administrative->value] = ['label' => $label, 'value' => $administrative->value];
        }

        $cities = DB::table('templomok')->select('nev')->where('ok', 'i')->where('nev', 'like', $keyword)
                        ->groupBy('nev')->orderBy('nev')->take(10)->get();
        foreach ($cities as $city) {
            $label = preg_replace('/(' . $keywordEmpty . ')/i', '<strong>$1</strong>', $city->nev);
            $return[$city->nev] = ['label' => $label, 'value' => $city->nev];
        }
        ksort($return);
        $this->content = json_encode(array('results' => $return));
    }

}
