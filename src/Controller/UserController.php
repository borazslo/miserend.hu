<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Types\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends AbstractController
{
    #[Route(path: '/profil', name: 'user_profile', methods: ['GET', 'POST'])]
    public function profile(
        #[CurrentUser]
        User $user,
        Request $request,
    ): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // save
        }

        return $this->render('user/edit.twig', [
            'user' => $user,
            'edit' => true,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/user/new', name: 'user_new', methods: ['GET'])]
    public function legacyRegistration(Request $request): Response
    {
        return $this->redirectToRoute('user_new', status: Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route(path: '/regisztracio', name: 'user_registration', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // save
        }

        return $this->render('user/edit.twig', [
            'edit' => false,
            'form' => $form->createView(),
        ]);
    }
}
