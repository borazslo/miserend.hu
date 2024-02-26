<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Components\ApiClients\BreviarKBSClient;
use App\Components\ApiClients\Exceptions\ContentUnavailableException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LiturgicalDayController extends AbstractController
{
    public function __invoke(BreviarKBSClient $client, ?\DateTimeImmutable $date = null): Response
    {
        $date = $date ?? new \DateTimeImmutable();

        $dayOfTheWeek = (int) $date->format('N');
        if ($dayOfTheWeek === 7) {
            return new Response('');
        }

        try {
            $calendar = $client->fetchCalendarAt($date);
            if (\count($celebrations = $calendar->getCelebrations()) > 0 && $celebrations[0]->getLevel() <= 4) {
                return $this->render('alerts/liturgical_day.html.twig', [
                    'celebration' => $celebrations[0],
                ]);
            }
        } catch (ContentUnavailableException $exception) {
        }

        return new Response('');
    }
}
