<?php

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

class Chat
{

    public $limit = 10;
    public $alert = 0;

    function load()
    {
        $this->loadComments();
        $this->lastcomment = isset($this->comments[0]['datum_raw']) ? $this->comments[0]['datum_raw'] : false;
        $this->getUsers('html');
    }

    function loadComments($args = [])
    {
        global $user, $twig;

        $this->comments = [];

        $loginkiir1 = urlencode($user->login);

        $comments = DB::table('chat')->where(function ($query) {
            global $user;
            $query->orWhere('kinek', '')
                ->orWhere('kinek', $user->login)
                ->orWhere('user', $user->login);
        })
            ->orderBy('datum', 'DESC')
            ->limit($this->limit);
        if (isset($args['last'])) {
            $comments = $comments->where('datum', '>', $args['last']);
        }
        if (isset($args['first'])) {
            $comments = $comments->where('datum', '<', $args['first']);
        }

        $comments = $comments->get();
        $comments = collect($comments)->map(function ($x) {
            return (array)$x;
        })->toArray();

        foreach ($comments as $row) {

            $row['datum_raw'] = $row['datum'];
            if (date('Y', strtotime($row['datum'])) < date('Y')) {
                $row['datum'] = date('Y.m.d.', strtotime($row['datum']));
            } elseif (date('m', strtotime($row['datum'])) < date('m')) {
                $row['datum'] = date('m.d.', strtotime($row['datum']));
            } elseif (date('d', strtotime($row['datum'])) < date('d')) {
                $row['datum'] = date('m.d. H:i', strtotime($row['datum']));
            } else {
                $row['datum'] = date('H:i', strtotime($row['datum']));
            }

            if ($row['user'] == $user->login) {
                $row['color'] = '#394873';
            } elseif ($row['kinek'] == $user->login) {
                $row['color'] = 'red';
            } elseif (preg_match('/@'.$user->login.'([^a-zA-Z]{1}|$)/i', $row['szoveg'])) {
                $row['color'] = 'red';
            }

            if ($row['kinek'] != '') {
                if ($row['kinek'] == $user->login) {
                    $loginkiir2 = urlencode($user->login);
                } else {
                    $loginkiir2 = urlencode($row['kinek']);
                }

                $row['jelzes'] = "<span class='response_closed link' title='Válasz csak neki' data-to='".$row['kinek']."' ><img src=img/lakat.gif align=absmiddle height='13' border=0><i> ".$row['kinek']."</i></span>: ";
                //$row['jelzes'] .= "<a class='response_open link' title='Nyilvános válasz / említés' data-to='".$row['kinek']."'><i> ".$row['kinek']."</i></a>: ";
            }

            $row['szoveg'] = preg_replace(
                '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@',
                '<a href="$1" target="_blank">$1</a>',
                $row['szoveg']
            );
            $row['szoveg'] = preg_replace('@>(https?://miserend\.hu/)@', '>', $row['szoveg']);
            $row['szoveg'] = preg_replace(
                '/@(\w+)/i',
                '<span class="response_open" data-to="$1" style="background-color: rgba(0,0,0,0.15);">$1</span>',
                $row['szoveg']
            );


            $row['html'] = $twig->render('chat/chatcomment.twig', array('comment' => $row));
            if ($row['user'] != $user->login) {
                $this->alert++;
            }

            $this->comments[] = $row;
        }

        return $this->comments;

    }

    function getUsers($format = false)
    {
        global $user;
        $return = array();

        $onlineUsers = DB::table('user')
            ->select('login')
            ->where('jogok', '!=', '')
            ->where('lastactive', '>=', date('Y-m-d H:i:s', strtotime("-5 minutes")))
            ->where('login', '<>', $user->login)
            ->orderBy('lastactive', 'DESC')
            ->get();

        foreach ($onlineUsers as $onlineUser) {
            $return[] = $onlineUser->login;
        }

        if ($format == 'html') {
            foreach ($return as $k => $i) {
                $return[$k] = '<span class="response_closed" data-to="'.$i.'" style="background-color: rgba(0,0,0,0.15);">'.$i.'</span>';
            }
            $text = '<strong>Online adminok:</strong> '.implode(', ', $return);
            if (count($return) == 0) {
                $text = '<strong><i>Nincs (más) admin online.</i></strong>';
            }
            $return = $text;
        }
        $this->users = $return;

        return $this->users;
    }

}
