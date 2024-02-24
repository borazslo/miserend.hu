<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Church;
use App\Entity\User;
use App\Repository\ChurchRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FavoriteController extends AbstractController
{
    #[Route(path: '/profil/kedvencek', name: 'user_favorites', methods: 'GET')]
    public function userFavorites(
        ChurchRepository $repository,
        #[CurrentUser]
        User $user,
    ): Response {
        return $this->render('user/favorites.html.twig', [
            'favorites' => $repository->findFavoriteChurches($user),
        ]);
    }

    public function favorites(
        ChurchRepository $repository,
        #[CurrentUser]
        ?User $user = null,
    ): Response {
        return $this->render('church/favorites.html.twig', [
            'favorites' => $user === null ? $repository->findMostFavorite() : $user->getFavorites(),
        ]);
    }

    #[IsGranted(attribute: 'ROLE_USER')]
    #[Route(path: '/profil/kedvenc/{church}', name: 'user_favorite_change', options: ['expose' => true], methods: ['POST', 'DELETE'])]
    public function changeFavorite(
        Request $request,
        UserRepository $repository,
        ChurchRepository $churchRepository,
        Church $church,
        #[CurrentUser]
        User $user,
    ): Response {
        if ($request->getMethod() === 'POST') {
            $user->addFavorite($church);
        } else {
            $user->removeFavorite($church);
        }

        $repository->flush();

        if ($request->headers->get('Turbo-Frame') === 'list-favorite') {
            return $this->render('user/favorites.html.twig', [
                'favorites' => $churchRepository->findFavoriteChurches($user),
            ]);
        }

        return $this->forward(ChurchController::class.'::view', [
            'church' => $church,
            'slug' => $church->getSlug(),
        ]);
    }
}
