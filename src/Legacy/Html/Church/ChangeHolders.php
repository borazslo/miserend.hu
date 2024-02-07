<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Church;

use App\Legacy\Html\Html;
use App\Legacy\Model\ChurchHolder;
use App\Legacy\Request;
use Symfony\Component\HttpFoundation\Response;

class ChangeHolders extends Html
{
    public function form(\Symfony\Component\HttpFoundation\Request $request): Response
    {
        return $this->render('church/changeholders.twig');
    }

    public function legach($path)
    {
        $where = [];
        $data = [];

        if (isset($path[0])) {
            $where['church_id'] = $path[0];
        } else {
            $where['church_id'] = Request::IntegerRequired('tid');
        }

        $where['user_id'] = Request::Integer('uid');
        $confirmation = Request::Simpletext('confirmation');

        if (!$where['user_id']) {
            if ($confirmation) {
                // Boldogok vagyunk
                return;
            } else {
                throw new \Exception("Required 'uid' is required.");
            }
        }

        $data['status'] = Request::InArrayRequired('access', ['allowed', 'denied', 'revoked', 'asked']);
        $description = Request::Text('description');
        if ($description != '') {
            $data['description'] = $description;
        }

        $user = $this->getSecurity()->getUser();
        if ($user->getUid() == $where['user_id'] && $data['status'] == 'asked') {
            if ($confirmation == 'needed') {
                $churchHolder = ChurchHolder::where('user_id', $where['user_id'])->where('church_id', $where['church_id'])->first();
                if (!$churchHolder) {
                    $churchHolder = new ChurchHolder(array_merge($where, $data));
                }
                $this->holder = $churchHolder;
            } else {
                $churchHolder = ChurchHolder::updateOrCreate($where, $data);
                $churchHolder->sendEmails();
                addMessage('A kérést köszönettel elmentettük.', 'info');

                return $this->redirect('/templom/'.$where['church_id']);
            }
        } elseif ($user->checkRole('miserend')) {
            $churchHolder = ChurchHolder::updateOrCreate($where, $data);
            $churchHolder->sendEmails();
            addMessage('A változtatást sikeresen elmentettük.', 'info');

            return $this->redirect('/templom/'.$where['church_id'].'/edit');
        } else {
            throw new \Exception('Hiányzó jogosultság');
        }
    }
}
