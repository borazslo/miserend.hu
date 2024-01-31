<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use App\Html\Html;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Ajax extends Html
{
    public $template = 'layout_empty.twig';

    protected function forbiddenResponse(): JsonResponse
    {
        return $this->errorResponse('Hiányzó jogosultság', Response::HTTP_FORBIDDEN);
    }

    protected function successResponse(array $payload): JsonResponse
    {
        return $this->jsonResponse($payload);
    }

    protected function errorResponse(string $errorMessage, int $statusCode): JsonResponse
    {
        return $this->jsonResponse([], $errorMessage, $statusCode);
    }

    protected function jsonResponse(array $payload, string $errorMessage = null, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $jsonContent = [
            'code' => $statusCode,
            'result' => $statusCode === 200 ? 'ok' : 'error',
            'text' => $errorMessage,
            'payload' => $payload,
        ];

        return new JsonResponse($jsonContent, $statusCode);
    }

    protected function jsonBody(Request $request): JsonResponse|array
    {
        if ($request->headers->get('content-type') !== 'application/json') {
            return $this->errorResponse('Hiba történt: hiányzó http fejléc!', Response::HTTP_BAD_REQUEST);
        }

        $jsonBodyString = $request->getContent();

        if (!json_validate($jsonBodyString)) {
            return $this->errorResponse('Hiba történt: érvénytelen json!', Response::HTTP_BAD_REQUEST);
        }

        return json_decode($jsonBodyString, true);
    }

    /**
     * @internal
     */
    public $content;
}
