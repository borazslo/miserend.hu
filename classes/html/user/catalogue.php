<?php

namespace Html\User;

use Illuminate\Database\Capsule\Manager as DB;

class Catalogue extends \Html\Html {

    public function __construct() {
        global $user;

        if (!$user->checkRole('user')) {
            throw new \Exception('Nincs jogosultságod megnézni a felhasználók listáját.');
        }

        $this->input['kulcsszo'] = \Request::Text('kulcsszo');
        $this->input['sort'] = \Request::TextwDefault('sort', 'lastactive desc');
        $this->input['adminok'] = \Request::SimpleTextwDefault('adminok', '');
        $this->input['limit'] = \Request::IntegerwDefault('limit', 50);

        $this->buildForm();
        $this->buildQuery();

        if($this->input['limit'] < $this->query->count()) {
            $this->more = "Van még ".($this->query->count() - $this->input['limit'])." találat.";
        }
        $this->query->orderByRaw($this->input['sort']);
        
        $results = $this->query->get();

        foreach ($results as $result) {
            if (preg_match('/^(lastlogin|lastactive|regdatum)/i', $this->input['sort'], $match))
                $field = preg_replace(array('/ /i', '/-/i'), array('&nbsp;', '&#8209;'), $match[1]);
            else
                $field = 'lastlogin';
            
            $this->users[$result->uid] = new \User($result->uid);
        }
        $this->field = $field;
        
    }

    function buildForm() {

        $sortOptions = [
            'login' => 'felhasználó név',
            'becenev' => 'becenév',
            'nev' => 'név',
            'lastlogin desc' => 'utolsó belépés',
            'lastactive desc' => 'utolsó aktivitás',
            'regdatum desc' => 'regisztracio',
            'templomok desc' => 'ellátott templomok'
        ];

        $this->form = array(
            'kulcsszo' => array(
                'name' => 'kulcsszo',
                'value' => $this->input['kulcsszo'],
                'size' => 20,
            ),
            'sort' => array(
                'label' => 'Rendezés:',
                'name' => 'sort',
                'options' => $sortOptions,
                'selected' => $this->input['sort'],
            ),
            'adminok' => array(
                'label' => 'Jogkör:',
                'name' => 'adminok',
                'options' => array(
                    '' => 'Mindenki'),
                'selected' => $this->input['adminok']
            )
        );

        $roles = unserialize(ROLES);
        foreach ($roles as $role) {
            $this->form['adminok']['options'][$role] = $role;
        }

        if (!key_exists($this->input['sort'], $sortOptions)) {
            throw new \Exception("Sajnos '" . $this->input['sort'] . "' alapján nem lehet rendezni a felhasználókat.");
        }
    }

    function buildQuery() {

        $query = DB::table('user')
                ->select('uid');

        if (!empty($this->input['adminok'])) {
            $query->where('jogok', 'like', "%" . $this->input['adminok'] . "%");
        }

        if($this->input['sort'] == 'templomok desc')
            $query->addSelect(DB::raw('count(church_holders.church_id) as templomok'))->leftJoin('church_holders', 'church_holders.user_id','=','user.uid')->where('church_holders.status','allowed')->groupBy('uid');
                
        if (!empty($this->input['kulcsszo'])) {
            $input = $this->input;
            $query->where(function ($q) use ($input) {
                $q->where('user.login', 'like', "%" . $input['kulcsszo'] . "%")
                        ->orWhere('user.nev', 'like', "%" . $input['kulcsszo'] . "%")
                        ->orWhere('user.email', 'like', "%" . $input['kulcsszo'] . "%");
            });
        }
        $query->take($this->input['limit']);       
        $this->query = $query;
    }

}
