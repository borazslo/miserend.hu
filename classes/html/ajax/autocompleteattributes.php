<?php

namespace Html\Ajax;

class AutocompleteAttributes extends Ajax {

    public function __construct() {
        $text = sanitize($_REQUEST['text']);

        $results = array();

        if ($_REQUEST['type'] == 'language') {
            $attributes = array();
            $tmp = unserialize(LANGUAGES);
            foreach ($tmp as $abbrev => $attribute) {
                $attributes[$abbrev] = $attribute['name'];
            }
        } else {
            $attributes = array();
            $tmp = unserialize(ATTRIBUTES);
            foreach ($tmp as $abbrev => $attribute) {
                $attributes[$abbrev] = $attribute['name'];
            }
        }

        $periods = array();
        $tmp = unserialize(PERIODS);
        foreach ($tmp as $abbrev => $period) {
            if (isset($period['description']))
                $periods[$abbrev] = $period['description'] . " héten";
            else
                $periods[$abbrev] = $period['name'] . " héten";
        }

        foreach ($attributes as $key => $val) {
            if (preg_match('/^' . $text . '/i', $key) OR preg_match('/^' . $text . '/i', $val)) {
                $results[] = array('label' => $key . " <i>(" . $val . ")</i>", 'value' => $key);
            }
        }

        foreach ($attributes as $key => $val) {
            if ($text == $key) {
                foreach ($periods as $k => $v) {
                    if ($k != '0') {
                        $results[] = array('label' => $key . $k . " <i>(" . $val . " " . $v . ")</i>", 'value' => $key . $k);
                    }
                }
            }
        }
        $this->content = json_encode(array('results' => $results));
    }

}
