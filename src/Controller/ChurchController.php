<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Components\AccessibilityHelper;
use App\Entity\Church;
use App\Entity\User;
use App\Repository\ChurchRepository;
use App\Repository\OsmTagRepository;
use App\Repository\PhotoRepository;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ChurchController extends AbstractController implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        ContainerInterface $container,
    ) {
        $this->container = $container;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', 10],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            'repository' => ChurchRepository::class,
        ] + parent::getSubscribedServices();
    }

    protected function getRepository(): ChurchRepository
    {
        return $this->container->get('repository');
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MAIN_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $exception = $event->getThrowable();

        if (!$exception instanceof NotFoundHttpException) {
            return;
        }

        // egyelore csak ezt kezeljuk le. kesobb lehet mas ehhez a controllerhez tartozot is
        if ($request->attributes->get('_route') === 'church_view' && $request->attributes->has('church_id')) {
            $churchId = $request->attributes->get('church_id');

            // TODO softDelete filtert itt ki kell kapcsolni ha lesz

            $church = $this->getRepository()->find($churchId);

            $message = null;
            $statusCode = 500;

            if ($church === null) {
                $statusCode = 404;
                $message = 'Sajnos ilyen templom nincs.';
            } elseif ($church->isDeleted()) {
                $statusCode = 410;
                $message = 'A templomot végleg töröltük!';
            } elseif ($church->getModeration() !== Church::MODERATION_ACCEPTED) { // TODO kulon uzenet a kolonfele moderacios statusokhoz?
                $statusCode = 403;
                $message = 'A templom elbírálás alatt áll. Kérjük térjen vissza később.';
            }

            $response = new Response(status: $statusCode);

            $this->render('messages/not_found.html.twig', [
                'message' => $message ?? 'Hiba történt az oldalon. :(',
            ], $response);

            $event->setResponse($response);
        }
    }

    public function redirectToChurchView(Church $church, int $status = 301): RedirectResponse
    {
        return $this->redirectToRoute('church_view', [
            'church_id' => $church->getId(),
            'slug' => $church->getSlug(),
        ], status: 301);
    }

    /**
     * Templom megjelenitese. A /templom/:id es a /?templom=:id urlek iranyitodnak ide.
     *
     * @see https://miserend.hu/?templom=408
     * @see https://miserend.hu/templom/408
     *
     * @todo milyen url formakat kell meg kezelni?
     */
    #[Route(path: '/templom/{church_id}/{slug}', name: 'church_view', requirements: ['slug' => '((?!changeholder|ujeszrevetel).)*'])]
    public function view(
        #[MapEntity(expr: 'repository.findOnePublicChurch(church_id)')]
        Church $church,
        ?string $slug = null,
    ): Response {
        if ($slug === null && $church->getSlug() === null) {
            $this->getRepository()->generateSlug($church);

            return $this->redirectToChurchView($church);
        }

        if ($slug === null && $slug !== $church->getSlug()) {
            return $this->redirectToChurchView($church);
        }

        if ($slug !== $church->getSlug()) {
            return $this->redirectToChurchView($church);
        }

        return $this->render('church/view.html.twig', [
            'church' => $church,
        ]);
    }

    public function randomChurch(PhotoRepository $repository): Response
    {
        return $this->render('church/panels/random_church.html.twig', [
            'photo' => $repository->findRandomPhoto(),
        ]);
    }

    public function accessibility(Church $church, OsmTagRepository $tagRepository): Response
    {
        return $this->render('church/panels/accessibility.html.twig', [
            'church' => $church,
            'accessibility' => new AccessibilityHelper($tagRepository->findTagsWithChurch($church, true)),
        ]);
    }

}
