<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Church;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChurchController extends AbstractController
{
    #[Route(path: '/templom/{church_id}', name: 'church_view')]
    public function view(
        #[MapEntity(id: 'church_id')]
        Church $church,
    ): Response {
        dump($church);
        exit;
    }
}
