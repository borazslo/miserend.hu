<?php

class Token {
       
    static function create($forUserId, $type) {
        global $config;
                
        if(isset($_COOKIE['token'])) {
                \Eloquent\Token::where('name',$_COOKIE['token'])->delete();
        }

        $timeout = date('Y-m-d H:i:s', strtotime("+" . $config['token'][$type]));        
        $token = Eloquent\Token::create([
            'name' => md5(uniqid(mt_rand(), true)),
            'type' => $type,
            'uid' => $forUserId,
            'timeout' => $timeout
            ]);        
        $token->save();

        setcookie('token', $token->name, strtotime($timeout),"/","", false, true);  // https?
        $_COOKIE['token'] = $token->name;

        return $token->name;
    }

   
    static function delete() {
        if(isset($_COOKIE['token'])) {
            \Eloquent\Token::where('name',$_COOKIE['token'])->delete();
            setcookie('token', "", strtotime("-1 year"),"/","", false, true);        
            unset($_COOKIE['token']);
        }        
    }
     
    static function cleanOut() {
        \Eloquent\Token::where('timeout','<',date('Y-m-d H:i:s'))->delete();
    }
        
    
}
