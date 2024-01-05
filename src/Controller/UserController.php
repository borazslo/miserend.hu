<?php

namespace App\Controller;

use App\Form\Types\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route(path: '/user/new', name: 'user_new', methods: ['GET', 'POST'])]
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