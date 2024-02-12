<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Components\ApiClients\BreviarKBSClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LiturgicalDayController extends AbstractController
{
    public function __invoke(BreviarKBSClient $client, ?\DateTimeInterface $date = null): Response
    {
        $date = $date ?? new \DateTime();

        $dayOfTheWeek = (int) $date->format('N');
        if ($dayOfTheWeek === 7) {
            return new Response('');
        }

        $calendar = $client->fetchCalendarAt($date);
        $level = isset($calendar['CalendarDay']['Celebration']['LiturgicalCelebrationLevel']) ? ((int) $calendar['CalendarDay']['Celebration']['LiturgicalCelebrationLevel']) : null;

        if ($level === null || $level > 4) {
            return new Response('');
        }

        return $this->render('alerts/liturgical_day.html.twig', [
            'calendar' => $calendar,
        ]);
    }
}
