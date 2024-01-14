<?php

namespace App\Html\Ajax;

use App\Model\KeywordShortcut;
use Illuminate\Database\Capsule\Manager as DB;

class AutocompleteKeyword extends Ajax {

    public array $input;

    public function __construct() {
        $this->input = $_REQUEST;

        $return = [];
        if (!preg_match('/\*$/', $this->input['query'])) {
            $return[] = array(
                'label' => $_REQUEST['query'] . "* <i>(Minden " . $_REQUEST['query'] . "-al kezdődő)</i>",
                'value' => $_REQUEST['query'] . "*"
            );
        }

        if (!preg_match('/^\*(.*)\*$/', $this->input['query'])) {
            if (preg_match('/^\*/', $this->input['query'])) {
                $return[] = array(
                    'label' => $_REQUEST['query'] . "* <i>(Minden " . $_REQUEST['query'] . "-t tartalmazó)</i>",
                    'value' => $_REQUEST['query'] . "*"
                );
            } else if (preg_match('/\*$/', $this->input['query'])) {
                $return[] = array(
                    'label' => "*" . $_REQUEST['query'] . " <i>(Minden " . $_REQUEST['query'] . "-t tartalmazó)</i>",
                    'value' => "*" . $_REQUEST['query']
                );
            } else {
                $return[] = array(
                    'label' => "*" . $_REQUEST['query'] . "* <i>(Minden " . $_REQUEST['query'] . "-t tartalmazó)</i>",
                    'value' => "*" . $_REQUEST['query'] . "*"
                );
            }
        }

        if (!preg_match('/\*$/', $this->input['query'])) {
            $this->input['query'] .= "*";
        }

        $keyword = preg_replace("/\*/", "%", $this->input['query']);
        $keywordEmpty = preg_replace("/%/", "", $keyword);
        $administratives = KeywordShortcut::where('type', 'name')->where('value', 'LIKE', $keyword)
                        ->groupBy('value')->orderBy('value')->take(10)->get();
        foreach ($administratives as $administrative) {
            $label = preg_replace('/(' . $keywordEmpty . ')/i', '<strong>$1</strong>', $administrative->value);
            $return[$administrative->value] = ['label' => $label, 'value' => $administrative->value];
        }

        $cities = DB::table('templomok')
            ->select('nev')
            ->where('ok', 'i')
            ->where('nev', 'like', $keyword)
            ->groupBy('nev')
            ->orderBy('nev')
            ->take(10)
            ->get();

        foreach ($cities as $city) {
            $label = preg_replace('/(' . $keywordEmpty . ')/i', '<strong>$1</strong>', $city->nev);
            $return[$city->nev] = [
                'label' => $label,
                'value' => $city->nev
            ];
        }
        ksort($return);
        $this->content = json_encode(array('results' => $return));
    }

}
