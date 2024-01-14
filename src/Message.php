<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

class Message
{
    public static function clean()
    {
        DB::table('messages')
            ->where('timestamp', '<', date('Y-m-d H:i:s', strtotime('-1 month')))
            ->orWhere('shown', 1)
            ->delete();
    }

    public static function add($text, $severity = false)
    {
        $id = DB::table('messages')->insertGetId([
            'sid' => session_id(),
            'timestamp' => date('Y-m-d H:i:s'),
            'severity' => $severity,
            'text' => $text,
        ]);

        return true;
    }

    public static function getToShow()
    {
        $messages = DB::table('messages')
            ->select('id', 'timestamp', 'text', 'severity')
            ->where('shown', 0)
            ->where('sid', session_id())
            ->get();
        if (!\count($messages)) {
            return [];
        }

        foreach ($messages as $message) {
            $ids[] = $message->id;
            $return[] = (array) $message;
        }
        DB::table('messages')
            ->whereIn('id', $ids)
            ->update(['shown' => 1]);

        return (array) $return;
    }
}
