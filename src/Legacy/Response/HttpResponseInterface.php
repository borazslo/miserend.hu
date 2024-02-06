<?php

namespace App\Legacy\Response;

use Symfony\Component\HttpFoundation\Response;

interface HttpResponseInterface
{
    public function getResponse(): Response;
}