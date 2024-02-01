<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html;

use App\Entity\User;
use App\Legacy\Model\Photo;
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
            'favorites' => $this->getFavorites(),
            'alert' => \LiturgicalDayAlert('html'),
            'photo' => $this->getRandomPhoto(),
        ]);
    }

    private function getFavorites(): iterable
    {
        $user = $this->getSecurity()->getUser();
        assert($user instanceof User);
        return $user->getFavorites();
    }

    /** @deprecated  */
    private function getRandomPhoto()
    {
        $photo = Photo::big()->vertical()->where('flag', 'i')->orderbyRaw('RAND()')->first();
        if ($photo->church) { // TODO: Van, hogy a random képhez nem is tartozik templom. Valami régi hiba miatt.
            $photo->church->location;
        }

        return $photo;
    }
}
