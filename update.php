<?php

include_once 'load.php';

$users = Illuminate\Database\Capsule\Manager::table('user')->select('uid','login','jelszo')->whereRaw('CHAR_LENGTH(`jelszo`) <> 60')->take(5)->orderBy('uid')->get();
foreach($users as $user) {        
        $jelszo = password_hash(base64_decode($user->jelszo), PASSWORD_BCRYPT);
        echo $user->login.": ".base64_decode($user->jelszo)." ->".$jelszo."<br/>";
        Illuminate\Database\Capsule\Manager::table('user')->where('uid',$user->uid)->update(['jelszo' => $jelszo]);    
}

