<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use App\Legacy\Response\HttpResponseInterface;
use App\Legacy\Response\HttpResponseTrait;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventsList extends Ajax implements HttpResponseInterface
{
    use HttpResponseTrait;

    public function __construct()
    {
        $return = DB::table('events')
            ->groupBy('name')
            ->pluck('name');

        $this->response = new JsonResponse([
            'events' => $return,
        ]);
    }
}
