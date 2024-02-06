<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html;

use App\Legacy\Services\ConstantsProvider;
use App\Legacy\Services\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Home extends Html
{
    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
            ConstantsProvider::class => ConstantsProvider::class,
            UserRepository::class => UserRepository::class,
        ];
    }

    public function getConstantsProvider(): ConstantsProvider
    {
        return $this->container->get(ConstantsProvider::class);
    }

    public function getUserRepository(): UserRepository
    {
        return $this->container->get(UserRepository::class);
    }

    public function main(Request $request): Response
    {
        if ($request->query->has('templom')) {
            return new RedirectResponse('/templom/'.$request->query->getInt('templom'), 301);
        }

        $user = $this->getUser();

        return $this->render('home.html.twig', [
            'searchform' => [],
            'favorites' => [],
            'alert' => '',
            'photo' => [],
        ]);
    }
}
