<?php

namespace App\Legacy;

use App\Model\Token;

class TokenManager
{
    public function __construct(
        private readonly ConfigProvider $config
    )
    {
    }

    public function findToken(string $token): ?Token
    {
        return Token::where('name', $token)->first();
    }

    public function create($forUserId, $type): string
    {
        $config = $this->config->getConfig();

        if (isset($_COOKIE['token'])) {
            Token::where('name', $_COOKIE['token'])->delete();
        }

        $timeout = date('Y-m-d H:i:s', strtotime('+'.$config['token'][$type]));
        $token = Token::create([
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

    public function delete(): void
    {
        if (isset($_COOKIE['token'])) {
            Token::where('name', $_COOKIE['token'])->delete();
            setcookie('token', '', strtotime('-1 year'), '/', '', false, true);
            unset($_COOKIE['token']);
        }
    }

    public function cleanOut(): void
    {
        Token::where('timeout', '<', date('Y-m-d H:i:s'))->delete();
    }

    public function extend(Token $token)
    {
        $config = $this->config->getConfig();

        $token->timeout = date('Y-m-d H:i:s', strtotime('+'.$config['token'][$token->type]));
        $token->save();
    }
}
