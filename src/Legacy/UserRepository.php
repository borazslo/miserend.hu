<?php

namespace App\Legacy;

use App\Model\Favorite;
use App\User;
use Illuminate\Database\Capsule\Manager as DB;

class UserRepository
{
    public function __construct(private readonly DB $database)
    {
    }

    protected function getDatabaseManager()
    {
        return $this->database->getDatabaseManager();
    }

    public function getFavorites(User $user): iterable
    {
        if ($user->getUid() > 0) {
            $favorites = Favorite::where('uid', $user->getUid())
                ->get()
                ->sortBy(function ($favorite) {
                    return $favorite->church->nev;
                });
        } else {
            $total = $this->getDatabaseManager()->raw('count(*) as total');
            $favorites = Favorite::groupBy('tid')
                ->select('tid', $total)
                ->orderBy('total', 'DESC')
                ->limit(10)
                ->get();
        }

        foreach ($favorites as $favorite) {
            $favorites[$favorite->tid] = $favorite;
        }

        return $favorites;
    }
}