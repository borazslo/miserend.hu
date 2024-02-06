<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Ajax;

use App\Legacy\Request;
use App\Legacy\Response\HttpResponseInterface;
use App\Legacy\Response\HttpResponseTrait;
use App\Model;
use Symfony\Component\HttpFoundation\Response;

class ChurchLink extends Ajax implements HttpResponseInterface
{
    use HttpResponseTrait;

    protected function delete(): ?Response
    {
        $id = Request::IntegerRequired('id');
        $link = \App\Legacy\Model\ChurchLink::find($id);
        if (!$link) {
            throw new \Exception('There is no ChurchLink with id: '.$id);
        }
        if (!$link->church) {
            $link->delete();
            throw new \Exception('There is no Church with id: '.$link->church_id);
        }

        if (!$link->church->WriteAccess) {
            throw new \Exception('Hozzáférés megtagadva.');
        }

        if ($link->delete()) {
            return new Response('ok');
        }

        return null;
    }

    protected function add(): Response
    {
        $church_id = Request::IntegerRequired('church_id');
        $church = \App\Legacy\Model\Church::find($church_id);
        if (!$church) {
            throw new \Exception('There is no Church with id: '.$church_id);
        }
        if (!$church->WriteAccess) {
            throw new \Exception('Hozzáférés megtagadva.');
        }

        $link = \App\Legacy\Model\ChurchLink::create([
            'church_id' => $church_id,
            'href' => Request::TextRequired('href'),
            'title' => Request::Text('title'),
        ]);
        $link->save();

        return new Response('<div class="church-link" data-link-id="'.$link->id.'">'.$link->html.'</div>');
    }

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $action = Request::InArrayRequired('action', ['delete', 'add']);

        $response = match ($action) {
            'delete' => $this->delete(),
            'add' => $this->add(),
        };

        if (null === $response) {
            throw new \Exception('unhandled request');
        }

        $this->response = $response;
    }
}
