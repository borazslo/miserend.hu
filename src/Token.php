<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

class Token
{
    public static function create($forUserId, $type): string
    {
        global $config;

        if (isset($_COOKIE['token'])) {
            Model\Token::where('name', $_COOKIE['token'])->delete();
        }

        $timeout = date('Y-m-d H:i:s', strtotime('+'.$config['token'][$type]));
        $token = Model\Token::create([
            'name' => md5(uniqid(mt_rand(), true)),
            'type' => $type,
            'uid' => $forUserId,
            'timeout' => $timeout,
        ]);
        $token->save();

        setcookie('token', $token->name, strtotime($timeout), '/', '', false, true);  // https?
        $_COOKIE['token'] = $token->name;

        return $token->name;
    }

    public static function delete(): void
    {
        if (isset($_COOKIE['token'])) {
            Model\Token::where('name', $_COOKIE['token'])->delete();
            setcookie('token', '', strtotime('-1 year'), '/', '', false, true);
            unset($_COOKIE['token']);
        }
    }

    public static function cleanOut(): void
    {
        Model\Token::where('timeout', '<', date('Y-m-d H:i:s'))->delete();
    }
}
