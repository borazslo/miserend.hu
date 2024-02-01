<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Ajax;

use App\Html\Ajax\Ajax;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Autoconfigure(lazy: true, autowire: true)]
class Chat extends Ajax
{
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

    public function load(Request $request): JsonResponse
    {
        if (!$this->getSecurity()->isGranted('ROLE_USER')) {
            return $this->forbiddenResponse();
        }

        $chat = $this->getChat();

        $date = date('Y-m-d H:i:s', strtotime($request->request->get('date') ?? ''));

        if (!$request->request->get('rev')) {
            $comments = $chat->loadComments(['last' => $date]);
        } else {
            $comments = $chat->loadComments(['first' => $date]);
        }

        $payload = [
            'comments' => $comments,
            'new' => \count($comments),
            'alert' => $chat->alert,
        ];

        return $this->successResponse($payload);
    }

    public function send(Request $request): JsonResponse
    {
        $security = $this->getSecurity();
        if (!$security->isGranted("'any'")) {
            return $this->forbiddenResponse();
        }

        if ($request->getMethod() !== 'POST') {
            return $this->errorResponse('Hiba történt: érvénytelen metódus!', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($request->headers->get('content-type') !== 'application/json') {
            return $this->errorResponse('Hiba történt: hiányzó http fejléc!', Response::HTTP_BAD_REQUEST);
        }

        $jsonBodyString = $request->getContent();

        if (!json_validate($jsonBodyString)) {
            return $this->errorResponse('Hiba történt: érvénytelen json!', Response::HTTP_BAD_REQUEST);
        }

        $jsonBody = json_decode($jsonBodyString, true);
        $message = $jsonBody['payload']['message'] ?? null;

        if ($message === null) {
            return $this->errorResponse('Hiba történt: hiányzó üzenet!', Response::HTTP_BAD_REQUEST);
        }

        $message = trim($message);
        $recipient = $jsonBody['payload']['recipient'] ?? ''; // ez kesobb siman null

        $fields = [
            'datum' => date('Y-m-d H:i:s'),
            'user' => $security->getUser()->getUsername(),
            'kinek' => $recipient,
            'szoveg' => $message,
        ];

        $insertResult = DB::table('chat')->insert($fields);
        if (!$insertResult) {
            return $this->errorResponse('Hiba történt: nem sikerült menteni az üzenetet!', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse([
            'message' => $message,
            'recipient' => $recipient,
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        if (!$this->getSecurity()->isGranted("'any'")) {
            return $this->forbiddenResponse();
        }

        $chat = $this->getChat();

        return $this->successResponse([
            'users' => $chat->getUsers(),
        ]);
    }
}
