<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Church;
use App\Repository\ChurchRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChurchController extends AbstractController
{
    public function redirectToChurchView(Church $church, int $status = 301): RedirectResponse
    {
        return $this->redirectToRoute('church_view', [
            'church_id' => $church->getId(),
            'slug' => $church->getSlug(),
        ], status: 301);
    }

    /**
     * @todo kell egy 404 handler ami megjeleniti a megfelelo hibauzenetet es oldalt ha nem talalhato a templom
     * @todo milyen url formakat kell meg kezelni?
     */
    #[Route(path: '/templom/{church_id}/{slug}', name: 'church_view')]
    public function view(
        ChurchRepository $repository,
        #[MapEntity(expr: 'repository.findOnePublicChurch(church_id)')]
        Church $church,
        string $slug = null,
    ): Response {
        if (null === $slug && null === $church->getSlug()) {
            $repository->generateSlug($church);

            return $this->redirectToChurchView($church);
        }

        if (null !== $slug && $slug !== $church->getSlug()) {
            return $this->redirectToChurchView($church);
        }

        return $this->render('church/view.html.twig', [
            'church' => $church,
        ]);
    }
}
