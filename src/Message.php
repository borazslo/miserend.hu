<?php

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

class Message
{

    static function clean()
    {
        DB::table('messages')
            ->where('timestamp', '<', date('Y-m-d H:i:s', strtotime('-1 month')))
            ->orWhere('shown', 1)
            ->delete();
    }

    static function add($text, $severity = false)
    {
        $id = DB::table('messages')->insertGetId([
            'sid' => session_id(),
            'timestamp' => date('Y-m-d H:i:s'),
            'severity' => $severity,
            'text' => $text,
        ]);

        return true;
    }

    static function getToShow()
    {
        $messages = DB::table('messages')
            ->select('id', 'timestamp', 'text', 'severity')
            ->where('shown', 0)
            ->where('sid', session_id())
            ->get();
        if (!count($messages)) {
            return array();
        }

        foreach ($messages as $message) {
            $ids[] = $message->id;
            $return[] = (array)$message;
        }
        DB::table('messages')
            ->whereIn('id', $ids)
            ->update(['shown' => 1]);

        return (array)$return;
    }

}

