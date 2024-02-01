<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

use App\Legacy;

class Photos
{
    /**
     * A méret nélküli képeknek próbál méretet találni.
     */
    public function cron()
    {
        $photos = Legacy\Model\Photo::whereNull('width')
            ->orWhereNull('height')
            ->orWhere('width', 0)
            ->orWhere('height', 0)
            ->get();

        foreach ($photos as $photo) {
            $photo->updateSize();
        }
    }
}
