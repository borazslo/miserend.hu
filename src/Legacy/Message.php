<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

use App\Legacy\Services\MessageRepository;

class Message
{
    /**
     * @deprecated
     * @see MessageRepository::clean()
     */
    public static function clean()
    {
        dump(sprintf('%s::%s', __CLASS__, __METHOD__));
        exit;
    }

    /**
     * @deprecated
     * @see MessageRepository::add()
     */
    public static function add($text, $severity = false)
    {
        dump(sprintf('%s::%s', __CLASS__, __METHOD__));
        exit;
    }

    /**
     * @deprecated
     * @see MessageRepository::getToShow()
     */
    public static function getToShow()
    {
        dump(sprintf('%s::%s', __CLASS__, __METHOD__));
        exit;
    }
}
