<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Services;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\DatabaseManager;

class MessageRepository
{
    public function __construct(private readonly Manager $database)
    {
    }

    protected function getDatabaseManager(): DatabaseManager
    {
        return $this->database->getDatabaseManager();
    }

    public function clean(): void
    {
        $this->getDatabaseManager()->table('messages')
            ->where('timestamp', '<', date('Y-m-d H:i:s', strtotime('-1 month')))
            ->orWhere('shown', 1)
            ->delete();
    }

    public function add(string $text, $severity = false): true
    {
        $this->getDatabaseManager()->table('messages')->insertGetId([
            'sid' => session_id(),
            'timestamp' => date('Y-m-d H:i:s'),
            'severity' => $severity,
            'text' => $text,
        ]);

        return true;
    }

    public function getToShow(): array
    {
        $rawMessages = $this->getDatabaseManager()
            ->table('messages')
            ->select('id', 'timestamp', 'text', 'severity')
            ->where('shown', 0)
            ->where('sid', session_id())
            ->get();

        if (\count($rawMessages) === 0) {
            return [];
        }

        $ids = [];
        $messages = [];
        foreach ($rawMessages as $message) {
            $ids[] = $message->id;
            $messages[] = (array) $message;
        }

        $this->getDatabaseManager()
            ->table('messages')
            ->whereIn('id', $ids)
            ->update(['shown' => 1]);

        return $messages;
    }
}
