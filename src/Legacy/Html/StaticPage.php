<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StaticPage extends Html
{
    public function staticPage(Request $request): Response
    {
        $templateFilename = match ($routeName = $request->attributes->get('_route')) {
            'terms_and_conditions', 'gdpr', 'about' => 'static_page/'.$routeName.'.twig',
            default => throw new NotFoundHttpException(),
        };

        return $this->render($templateFilename);
    }
}
