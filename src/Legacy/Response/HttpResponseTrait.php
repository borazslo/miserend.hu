<?php

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