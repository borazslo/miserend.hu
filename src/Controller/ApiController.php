<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Request\DataTransferObject\ChurchFavoriteDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1')]
class ApiController extends AbstractController
{
    #[Route(path: '/church/favorite', methods: 'POST')]
    public function churchFavorite(
        #[MapRequestPayload]
        ChurchFavoriteDto $churchFavorite,
    ): Response {
        return $this->forward(ChurchController::class.'::changeFavorite', path: [
            'church' => $churchFavorite->church,
            'method' => $churchFavorite->method,
        ]);
    }
}
