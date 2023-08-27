<?php

namespace Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class AutocompleteCity extends Ajax {

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
        $administratives = \Eloquent\KeywordShortcut::where('type', 'administrative')->where('value', 'LIKE', $keyword)
                        ->groupBy('value')->orderBy('value')->take(10)->get();
        foreach ($administratives as $administrative) {
            $label = preg_replace('/(' . $keywordEmpty . ')/i', '<strong>$1</strong>', $administrative->value);
            $return[$administrative->value] = ['label' => $label, 'value' => $administrative->value];
        }

        $cities = DB::table('templomok')->select('varos')->where('ok', 'i')->where('varos', 'like', $keyword)
                        ->groupBy('varos')->orderBy('varos')->take(10)->get();
        foreach ($cities as $city) {
            $label = preg_replace('/(' . $keywordEmpty . ')/i', '<strong>$1</strong>', $city->varos);
            $return[$city->varos] = ['label' => $label, 'value' => $city->varos];
        }
        ksort($return);
        $this->content = json_encode(array('results' => $return));
    }

}
