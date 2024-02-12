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
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends AbstractController
{
    #[Route(path: '/profil', name: 'user_profile', methods: ['GET', 'POST'])]
    public function profile(
        #[CurrentUser]
        User $user,
        Request $request,
    ): Response {
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
    public function create(Request $request, UserRepository $repository, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository->initPasswordChange($user);

            $repository->save($user, true);

            $email = new TemplatedEmail();
            $email->subject('Miserend - Regisztráció');
            $email->htmlTemplate('emails/user_welcome.html.twig');
            $email->context([
                'user' => $user,
            ]);
            $email->to($user->getEmail());
            $email->from(new Address('info@miserend.hu', 'Miserend.hu'));

            $mailer->send($email);

            return $this->render('messages/base.html.twig', [
                'message' => 'Sikeres regisztráció!',
                'additional_message' => 'Elküldtük az emailt belépéshez szükséges tudnivalókról.',
            ]);
        }

        return $this->render('user/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/felhasznalo/{hash}/uj_jelszo', name: 'user_change_password')]
    public function changePassword(User $user): Response
    {
        exit;
    }

    #[Route(path: '/user/lostpassword', name: 'user_ask_new_password', methods: ['GET', 'POST'])]
    public function beginPasswordChange(Request $request): Request
    {
        exit;
    }
}
