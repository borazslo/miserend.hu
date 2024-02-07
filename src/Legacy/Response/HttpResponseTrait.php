<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Response;

use Symfony\Component\HttpFoundation\Response;

trait HttpResponseTrait
{
    private Response $response;

    public function getResponse(): Response
    {
        return $this->response;
    }
}
