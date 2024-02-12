<?php

namespace App\Controller;

use App\Entity\Church;
use App\Entity\User;
use App\Repository\ChurchRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FavoriteController extends AbstractController
{
    #[Route(path: '/profil/kedvencek', name: 'user_favorites', methods: 'GET')]
    public function userFavorites(
        #[CurrentUser]
        User $user,
    ): Response {
        return $this->render('user/favorites.html.twig', [
            'favorites' => $user->getFavorites(),
        ]);
    }

    public function favorites(
        ChurchRepository $repository,
        #[CurrentUser]
        ?User $user = null,
    ) {
        return $this->render('church/favorites.html.twig', [
            'favorites' => $user === null ? $repository->findMostFavorite() : $user->getFavorites(),
        ]);
    }

    #[IsGranted(attribute: 'ROLE_USER')]
    #[Route(path: '/profil/kedvencek', name: 'user_favorite_change', methods: ['POST', 'DELETE'])]
    public function changeFavorite(
        Request $request,
        UserRepository $repository,
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

        return new JsonResponse('OK');
    }
}
