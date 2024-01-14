<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\User;

use Illuminate\Database\Capsule\Manager as DB;

class Catalogue extends \App\Html\Html
{
    public function __construct()
    {
        parent::__construct();
        global $user;

        if (!$user->checkRole('user')) {
            throw new \Exception('Nincs jogosultságod megnézni a felhasználók listáját.');
        }

        $this->input['kulcsszo'] = \App\Request::Text('kulcsszo');
        $this->input['sort'] = \App\Request::TextwDefault('sort', 'lastactive desc');
        $this->input['adminok'] = \App\Request::SimpleTextwDefault('adminok', '');

        $this->pagination->take = \App\Request::IntegerwDefault('take', 50);
        $offset = $this->pagination->take * $this->pagination->active;
        $take = $this->pagination->take;

        $this->buildForm();
        $this->buildQuery();

        $maxResults = \count($this->query->get());

        // Data for pagination
        $params = [];
        foreach (['kulcsszo', 'sort', 'adminok', 'take'] as $param) {
            if (isset($_REQUEST[$param]) && '' != $_REQUEST[$param] && '0' != $_REQUEST[$param]) {
                $params[$param] = $_REQUEST[$param];
            }
        }
        $params['q'] = 'user/catalogue';
        $url = \App\Pagination::qe($params, '/?');
        $this->pagination->set($maxResults, $url);

        $this->query->orderByRaw($this->input['sort']);
        $results = $this->query->offset($offset)->limit($take)->get();

        foreach ($results as $result) {
            if (preg_match('/^(lastlogin|lastactive|regdatum)/i', $this->input['sort'], $match)) {
                $field = preg_replace(['/ /i', '/-/i'], ['&nbsp;', '&#8209;'], $match[1]);
            } else {
                $field = 'lastlogin';
            }

            $this->users[$result->uid] = new \App\User($result->uid);
        }
        $this->field = $field;
    }

    public function buildForm()
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

        $this->form = [
            'kulcsszo' => [
                'name' => 'kulcsszo',
                'value' => $this->input['kulcsszo'],
                'size' => 20,
            ],
            'sort' => [
                'label' => 'Rendezés:',
                'name' => 'sort',
                'options' => $sortOptions,
                'selected' => $this->input['sort'],
            ],
            'adminok' => [
                'label' => 'Jogkör:',
                'name' => 'adminok',
                'options' => [
                    '' => 'Mindenki'],
                'selected' => $this->input['adminok'],
            ],
        ];

        $roles = unserialize(ROLES);
        foreach ($roles as $role) {
            $this->form['adminok']['options'][$role] = $role;
        }

        if (!\array_key_exists($this->input['sort'], $sortOptions)) {
            throw new \Exception("Sajnos '".$this->input['sort']."' alapján nem lehet rendezni a felhasználókat.");
        }
    }

    public function buildQuery()
    {
        $query = DB::table('user')
                ->select('user.uid');

        if (!empty($this->input['adminok'])) {
            $query->where('jogok', 'like', '%'.$this->input['adminok'].'%');
        }

        if ('templomok desc' == $this->input['sort']) {
            $query->addSelect(DB::raw('count(church_holders.church_id) as templomok'))->leftJoin('church_holders', 'church_holders.user_id', '=', 'user.uid')->where('church_holders.status', 'allowed')->groupBy('uid');
        }

        if ('favorites desc' == $this->input['sort']) {
            $query->addSelect(DB::raw('count(favorites.tid) as favorites'))->leftJoin('favorites', 'favorites.uid', '=', 'user.uid')->groupBy('user.uid');
        }

        if (!empty($this->input['kulcsszo'])) {
            $input = $this->input;
            $query->where(function ($q) use ($input) {
                $q->where('user.login', 'like', '%'.$input['kulcsszo'].'%')
                        ->orWhere('user.nev', 'like', '%'.$input['kulcsszo'].'%')
                        ->orWhere('user.email', 'like', '%'.$input['kulcsszo'].'%');
            });
        }
        $this->query = $query;
    }
}
