<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use App\Model\Remark;
use App\Request;

class SwitchReliable extends Ajax
{
    public function __construct()
    {
        $rid = Request::IntegerRequired('rid');
        $reliable = Request::InArrayRequired('reliable', ['i', 'n', '?', 'e']);

        $remark = Remark::find($rid);
        global $user;
        $holding = $user->getHoldingData($remark->church->id);
        if (!$holding) {
            $holding = 'denied';
        } else {
            $holding = $holding->status;
        }
        if (!$user->checkRole('miserend') && 'allowed' != $holding && !$user->checkRole('ehm:'.$remark->church->egyhazmegye)) {
            throw new \Exception('Hiányzó jogosultság.');
        }
        $remark->megbizhato = $reliable;
        $remark->save();

        header('Content-Type: text/plain');
        echo 'ok';
    }
}
