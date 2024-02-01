<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\User;

use App\Legacy\Html\Html;
use App\Legacy\Pagination;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Catalogue extends Html
{
    /**
     * @throws \Exception
     */
    public function list(Request $request): Response
    {
        if (!$this->getSecurity()->isGranted('user')) {
            throw new \Exception('Nincs jogosultságod megnézni a felhasználók listáját.');
        }

        $query = [
            'kulcsszo' => $request->request->get('kulcsszo'),
            'sort' => $request->request->get('sort', 'lastactive desc'),
            'role' => $request->request->get('adminok', ''),
        ];

        $pagination = $this->initPagination();
        $pagination->take = $request->request->get('take', 50);

        $offset = $pagination->take * $pagination->active;
        $take = $pagination->take;

        $form = $this->buildForm($query);
        $buildQuery = $this->buildQuery($query);

        $maxResults = \count($buildQuery->get());

        // Data for pagination
        $params = [];
        foreach (['kulcsszo', 'sort', 'role', 'take'] as $param) {
            if (isset($_REQUEST[$param]) && $_REQUEST[$param] != '' && $_REQUEST[$param] != '0') {
                $params[$param] = $_REQUEST[$param];
            }
        }

        $url = Pagination::qe($params, '/user/catalogue?');
        $pagination->set($maxResults, $url);

        $buildQuery->orderByRaw($query['sort']);
        $users = $buildQuery->offset($offset)
            ->limit($take)
            ->get();

        if (preg_match('/^(lastlogin|lastactive|regdatum)/i', $query['sort'], $match)) {
            $field = preg_replace(['/ /i', '/-/i'], ['&nbsp;', '&#8209;'], $match[1]);
        } else {
            $field = 'lastlogin';
        }

        return $this->render('user/catalogue.twig', [
            'pagination' => $pagination,
            'field' => $field,
            'users' => $users,
            'form' => $form,
        ]);
    }

    public function buildForm(array $query): array
    {
        $sortOptions = [
            'login' => 'felhasználó név',
            'becenev' => 'becenév',
            'nev' => 'név',
            'lastlogin desc' => 'utolsó belépés',
            'lastactive desc' => 'utolsó aktivitás',
            'regdatum desc' => 'regisztracio',
            'templomok desc' => 'ellátott templomok',
            'favorites desc' => 'kedvenc templomok',
        ];

        if (!\array_key_exists($query['sort'], $sortOptions)) {
            throw new \Exception("Sajnos '".$query['sort']."' alapján nem lehet rendezni a felhasználókat.");
        }

        $form = [
            'kulcsszo' => [
                'name' => 'kulcsszo',
                'value' => $query['kulcsszo'],
                'size' => 20,
            ],
            'sort' => [
                'label' => 'Rendezés:',
                'name' => 'sort',
                'options' => $sortOptions,
                'selected' => $query['sort'],
            ],
            'adminok' => [
                'label' => 'Jogkör:',
                'name' => 'adminok',
                'options' => [
                    '' => 'Mindenki'],
                'selected' => $query['role'],
            ],
        ];

        $roles = $this->getConstants()::ROLES;

        foreach ($roles as $role) {
            $form['adminok']['options'][$role] = $role;
        }

        return $form;
    }

    private function buildQuery(array $query): Builder
    {
        $queryBuilder = DB::table('user')
                ->select('user.*');

        if (!empty($query['role'])) {
            $queryBuilder->where('jogok', 'like', '%'.$query['role'].'%');
        }

        if ($query['sort'] == 'templomok desc') {
            $queryBuilder->addSelect(DB::raw('count(church_holders.church_id) as templomok'))->leftJoin('church_holders', 'church_holders.user_id', '=', 'user.uid')->where('church_holders.status', 'allowed')->groupBy('uid');
        }

        if ($query['sort'] == 'favorites desc') {
            $queryBuilder->addSelect(DB::raw('count(favorites.tid) as favorites'))
                ->leftJoin('favorites', 'favorites.uid', '=', 'user.uid')
                ->groupBy('user.uid');
        }

        if (!empty($query['kulcsszo'])) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('user.login', 'like', '%'.$query['kulcsszo'].'%')
                        ->orWhere('user.nev', 'like', '%'.$query['kulcsszo'].'%')
                        ->orWhere('user.email', 'like', '%'.$query['kulcsszo'].'%');
            });
        }

        return $queryBuilder;
    }
}
