<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Ajax;

class AutocompleteAttributes extends Ajax
{
    public function __construct()
    {
        $text = sanitize($_REQUEST['text']);

        $results = [];

        if ('language' == $_REQUEST['type']) {
            $attributes = [];
            $tmp = unserialize(LANGUAGES);
            foreach ($tmp as $abbrev => $attribute) {
                $attributes[$abbrev] = $attribute['name'];
            }
        } else {
            $attributes = [];
            $tmp = unserialize(ATTRIBUTES);
            foreach ($tmp as $abbrev => $attribute) {
                $attributes[$abbrev] = $attribute['name'];
            }
        }

        $periods = [];
        $tmp = unserialize(PERIODS);
        foreach ($tmp as $abbrev => $period) {
            if (isset($period['description'])) {
                $periods[$abbrev] = $period['description'].' héten';
            } else {
                $periods[$abbrev] = $period['name'].' héten';
            }
        }

        foreach ($attributes as $key => $val) {
            if (preg_match('/^'.$text.'/i', $key) || preg_match('/^'.$text.'/i', $val)) {
                $results[] = ['label' => $key.' <i>('.$val.')</i>', 'value' => $key];
            }
        }

        foreach ($attributes as $key => $val) {
            if ($text == $key) {
                foreach ($periods as $k => $v) {
                    if ('0' != $k) {
                        $results[] = ['label' => $key.$k.' <i>('.$val.' '.$v.')</i>', 'value' => $key.$k];
                    }
                }
            }
        }
        $this->content = json_encode(['results' => $results]);
    }
}
