<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class Chat extends Ajax
{
    public function __construct()
    {
        global $user;
        if (!$user->checkRole("'any'")) {
            $this->content = json_encode(['result' => 'error', 'text' => 'Hiányzó jogosultság']);

            return;
        }

        switch (\App\Request::InArrayRequired('action', ['load', 'save', 'getusers'])) {
            case 'load':
                $date = date('Y-m-d H:i:s', strtotime(\App\Request::Text('date')));
                $chat = new \App\Chat();
                if (!\App\Request::Text('rev')) {
                    $comments = $chat->loadComments(['last' => $date]);
                } else {
                    $comments = $chat->loadComments(['first' => $date]);
                }

                $this->content = json_encode(['result' => 'loaded', 'comments' => $chat->comments, 'new' => \count($chat->comments), 'alert' => $chat->alert]);

                break;

            case 'save':
                $text = \App\Request::TextRequired('text');
                if (preg_match('/^\$(\w+)/si', $text, $match)) {
                    $kinek = $match[1];
                    $text = preg_replace('/^(\$\w+(:*))/si', '', $text);
                } else {
                    $kinek = '';
                }
                if ('' == trim(preg_replace('/^((\$|@)\w+(:*))/si', '', $text))) {
                    $this->content = json_encode(['result' => 'error', 'text' => 'Nem volt igazán üzenet, amit elküldhettünk volna.']);

                    return;
                }

                if (!DB::table('chat')->insert([
                        'datum' => date('Y-m-d H:i:s'),
                        'user' => $user->login,
                        'kinek' => $kinek,
                        'szoveg' => trim($text),
                    ])
                ) {
                    $this->content = json_encode(['result' => 'error', 'text' => 'Hiba a mysql küldésben!']);
                } else {
                    $this->content = json_encode(['result' => 'saved', 'text' => $text]);
                }

                break;

            case 'getusers':
                $chat = new \App\Chat();
                $this->content = json_encode(['result' => 'loaded', 'text' => $chat->getUsers('html')]);

                break;
        }
    }
}
