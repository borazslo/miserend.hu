<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Ajax;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Favorite extends Ajax
{
    public function favorite(Request $request): Response
    {
        if (!$this->getSecurity()->isGranted('ROLE_USER')) {
            return new JsonResponse([
                'status' => 'failed',
                'message' => 'A kedvencek közé mentéshez be kell jelentkezni.',
            ], status: Response::HTTP_FORBIDDEN);
        }

        $json = $this->jsonBody($request);

        if ($json instanceof JsonResponse) {
            return $json;
        }

        $churchId = $json['church-id'] ?? null;
        $method = $json['method'] ?? null;

        if (!$churchId) {
            return new JsonResponse([
                'status' => 'failed',
                'message' => 'Nincs templom id.',
            ], status: Response::HTTP_BAD_REQUEST);
        }

        $churchId = (int) $churchId;

        $user = $this->getSecurity()->getUser();

        $result = match ($method) {
            'add' => $user->addFavorites($churchId),
            'del' => $user->removeFavorites($churchId),
            default => false,
        };

        if (!$result) {
            return new JsonResponse([
                'status' => 'failed',
                'message' => 'Nem sikerult elmentenem.',
            ], status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse('OK');
    }
}
