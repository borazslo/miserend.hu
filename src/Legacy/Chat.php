<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

use Illuminate\Database\Capsule\Manager as DB;

class Chat
{
    public function __construct(
        private readonly Security $security,
    )
    {
    }

    public int $limit = 10;
    public int $alert = 0;

    /**
     * @return array{
     *     comments: array,
     *     last_comment_at: int|null,
     *     rendered_users: array,
     * }
     */
    public function load(): array
    {
        $comments = $this->loadComments();
        $lastCommentAt = $comments[0]['datum_raw'] ?? null;
        $renderedUsers = $this->renderUsers();

        return [
            'comments' => $comments,
            'last_comment_at' => $lastCommentAt,
            'rendered_users' => $renderedUsers,
        ];
    }

    /** @todo sorrendezes helyreallitasa ha hasznalva van egyaltalan */
    public function loadComments(array $args = []): array
    {
        $user = $this->security->getUser();

        $commentsQueryBuilder = DB::table('chat')
            ->orWhere('kinek', '')
            ->orWhere('kinek', $user->getLogin())
            ->orWhere('user', $user->getLogin())
            ->orderBy('datum', 'DESC')
            ->limit($this->limit);

        if (isset($args['last'])) {
            $commentsQueryBuilder->where('datum', '>', $args['last']);
        }
        if (isset($args['first'])) {
            $commentsQueryBuilder->where('datum', '<', $args['first']);
        }

        $comments = collect($commentsQueryBuilder->get())->map(function ($x) {
            return (array) $x;
        })->toArray();

        $buffer = [];
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

            if ($row['user'] == $user->getLogin()) {
                $row['color'] = '#394873';
            } elseif ($row['kinek'] == $user->getLogin()) {
                $row['color'] = 'red';
            } elseif (preg_match('/@'.$user->getLogin().'([^a-zA-Z]{1}|$)/i', $row['szoveg'])) {
                $row['color'] = 'red';
            }

            if ('' != $row['kinek']) {
                if ($row['kinek'] == $user->getLogin()) {
                    $loginkiir2 = urlencode($user->getLogin());
                } else {
                    $loginkiir2 = urlencode($row['kinek']);
                }

                $row['jelzes'] = "<span class='response_closed link' title='Válasz csak neki' data-to='".$row['kinek']."' ><img src=img/lakat.gif align=absmiddle height='13' border=0><i> ".$row['kinek'].'</i></span>: ';
                // $row['jelzes'] .= "<a class='response_open link' title='Nyilvános válasz / említés' data-to='".$row['kinek']."'><i> ".$row['kinek']."</i></a>: ";
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

            if ($row['user'] != $user->getLogin()) {
                ++$this->alert;
            }

            $buffer[] = $row;
        }

        return $buffer;
    }

    /**
     * @param string|bool $format
     * @return array|string
     */
    public function getUsers(): array
    {
        $user = $this->security->getUser();
        $users = [];

        $onlineUsers = DB::table('user')
            ->select('login')
            ->where('jogok', '!=', '')
            ->where('lastactive', '>=', date('Y-m-d H:i:s', strtotime('-5 minutes')))
            ->where('login', '<>', $user->getLogin())
            ->orderBy('lastactive', 'DESC')
            ->get();

        foreach ($onlineUsers as $onlineUser) {
            $users[] = $onlineUser->login;
        }

        return $users;
    }
}
