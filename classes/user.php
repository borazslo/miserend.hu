<?php

use Illuminate\Database\Capsule\Manager as DB;

class User {

    function __construct($uid = false) {
        if (isset($uid)) {
            $user = DB::table('user')
                    ->select('*');
            
            if(!is_numeric($uid) AND filter_var($uid, FILTER_VALIDATE_EMAIL) ) {
                $user = $user->where('email', $uid);
            } elseif (!is_numeric($uid)) {
                $user = $user->where('nev', $uid);
            } else {
                $user = $user->where('uid', $uid);
            }
            $user = $user->first();

            if($user) {
                foreach ($user as $key => $value) {
                    $this->$key = $value;
                }
                $this->username = $user->login;
                $this->nickname = $user->becenev;
                $this->name = $user->nev;
                $this->roles = explode('-', trim($this->jogok, " \t\n\r\0\x0B-"));
                $this->getResponsabilities();
                if ($this->checkRole('miserend')) {
                    $this->isadmin = true;
                }
                return true;   

            } else {
                 //TODO: kitalálni mit csináljon, ha  nincs megfelelő azobosítójú user. Legyen vendég?
                // There is no user with this uid;
                $uid = 0;
                //return false;
            }        
        }
        //Lássuk a vendégeket
        if (!isset($uid) OR ! is_numeric($uid) OR $uid == 0) {
            $this->loggedin = false;
            $this->uid = 0;
            $this->username = '*vendeg*';
            $this->nickname = '*vendég*';
            $this->responsible = false;
        } 
    }

    function checkRole($role = false) {
        if ($role == false)
            return true;

        if ($role == '"any"' OR $role == "'any'") {
            if (!isset($this->jogok)) {
                return false;
            }
            if (trim(preg_replace('/-/i', '', $this->jogok)) != '')
                return true;
            else
                return false;
        } elseif (preg_match('/^ehm:([0-9]{1,3})$/i', $role, $match)) {
            $isResponsible = DB::table('egyhazmegye')->where('id', $match[1])->where('felelos', $this->username)->first();
            if ($isResponsible)
                return true;
            else
                return false;
        } elseif (isset($this->jogok) AND preg_match('/(^|-)' . $role . '(-|$)/i', $this->jogok)) {
            return true;
        } else
            return false;
    }

    function getResponsabilities() {
        $this->responsible = array(
            'diocese' => array(),
            'church' => array()
        );
        if ($this->uid > 0) {
            $results = DB::table('egyhazmegye')
                    ->select('id')
                    ->where('ok', 'i')
                    ->where('felelos', $this->username)
                    ->get();
            foreach ($results as $result) {
                $this->responsible['diocese'][] = $result->id;
            }
            $results = DB::table('templomok')
                    ->select('id')
                    ->where('ok', 'i')
                    ->where('letrehozta', $this->username)
                    ->get();
            foreach ($results as $result) {
                $this->responsible['church'][] = $result->id;
            }            
        }
    }

    function processResponsabilities() {
        if (!isset($this->responsible)) {
            $this->getResponsabilities();
        }

        $tmp = array();
        foreach ($this->responsible['church'] as $church) {
            $tmp[$church] = \Eloquent\Church::find($church);
        }
        $this->responsible['church'] = $tmp;
    }

    function getRemarks($limit = false, $ago = false) {
        if ($limit == false OR ! is_numeric($limit))
            $limit = 5;
        
        $query = \Eloquent\Remark::select('*',DB::raw('count(*) as total'))->where(function ($q) {
                    $q->where('login', $this->username)->orWhere('email', $this->email);
                });
        if ($ago != false)
            $query = $query->where('datum','>', date('Y-m-d H:i:s', strtotime("-" . $ago)));
            
        $query = $query->groupBy('church_id')->orderBy('created_at','desc');
        
        $this->remarksCount = $query->count();
        $this->remarks = $query->limit($limit)->get();
                
        return true;
    }

    function submit($vars) {
        $return = true;

        if (isset($vars['uid']) AND ! is_numeric($vars['uid']) AND $vars['uid'] != '') {
            addMessage('Nincs ilyen felhasználónk!', 'danger');
            return false;
        }

        $dangers = array(
            'uid' => 'Probléma támadt az azonosítóval!',
            'username' => 'Probléma a felhasználónévvel! (Nem megfelelő karakterek, vagy már használatban van. A felhasználó nevet nem lehet megváltoztatni.)',
            'nickname' => 'Probléma a becenévvel!',
            'name' => 'Probléma a névvel!',
            'email' => 'Nem megfelelő email cím! Talán már használatban van?',
            'volunteer' => 'Hibás értéke van az önkéntességnek!',
            'roles' => 'Hibás formátumú jogkörök!',
            'notifications' => 'Email értesítések engedélyezése körül hiba lépett fel!',
        );

        foreach (array('uid', 'username', 'nickname', 'name', 'email', 'volunteer', 'roles','notifications') as $input) {
            if (isset($vars[$input])) {
                if (!$this->presave($input, $vars[$input])) {
                    $return = false;
                    addMessage($dangers[$input], 'danger');
                }
            }
        }

        
        if (isset($vars['uid']) AND ( $vars['password1'] != '' OR $vars['password2'] != '')) {
            if ($vars['password1'] != $vars['password2'] OR $vars['password1'] == '') {
                addMessage('A két jelszó nem egyezik meg egymással', 'danger');
                $return = false;
            } else {
                if (!$this->presave('password', $vars['password1'])) {
                    $return = false;
                    addMessage('Sajnos nem megfelelő a jelszó!', 'danger');
                }
            }
        }
        
        if ($return == false)
            return false;
               
        if (!isset($vars['uid'])) {
            $pwd = $this->generatePassword();
            $this->presave('password', $pwd);                
        }

        if (!$this->save()) {
            addMessage("Nem sikerült elmenteni. Pedig minden rendben volt előtte.", "warning");
            return false;
        } else {
            if (!isset($vars['uid'])) {
                addMessage("A felhasználót sikeresen létrehoztuk.", "success");
                
                $this->newpwd = $pwd;
                $email = new \Eloquent\Email();
                $email->render('user_welcome', $this);
                if ($email->send($this->email))
                    addMessage("Elküldtük az emailt az új regisztrációról.", "success");                                
            }    
            else
                addMessage("A változásokat elmentettük.", "success");
        }
        return true;
    }

    function presave($key, $val) {
        if (!isset($this->presaved))
            $this->presaved = array();
        //TODO: check duplicate for: logn + email
        //TODO: van, amit ne engedjen, csak, amikor még tök új a cuccos.
        //TODO: a nickname - becenev / name - nev esetén ez nem segít, bár nem sok dupla munka azért
        //TODO: elrontja ...
        //if($this->$key == $val) return true;
        //TODO: szóljon vissza a kötelező
        if ($val == '' AND in_array($key, array('username', 'login', 'email'))) {
            return false;
        }

        if ($key == 'uid') {
            if ($this->uid != $val)
                return false;
        } elseif (in_array($key, array('username', 'login'))) {
            if ($this->uid == 0) {
                if (!checkUsername($val))
                    return false;                    
                $this->presaved['login'] = sanitize($val);
            } elseif ($this->username != $val) {
                return false;
            }
        } elseif (in_array($key, array('jelszo', 'password'))) {
            $this->presaved['jelszo'] = password_hash($val, PASSWORD_BCRYPT);
        } elseif ($key == 'roles' OR $key == 'jogok') {
            if (!is_array($val))
                $val = array($val);
            foreach ($val as $k => $i)
                $val['jogok'] = trim(sanitize($i), "-");
            $val = array_unique($val);
            $this->presaved['jogok'] = implode('-', $val);
        } elseif ($key == 'nickname' or $key == 'becenev') {
            $this->presaved['becenev'] = sanitize($val);
        } elseif ($key == 'name' or $key == 'nev') {
            $this->presaved['nev'] = sanitize($val);
        } elseif ($key == 'volunteer') {
            if ($val == '')
                $this->presaved[$key] = 0;
            elseif (in_array($val, array(0, 1)))
                $this->presaved[$key] = $val;
            else
                return false;       
        } elseif (in_array($key, array('regdatum', 'lastlogin', 'lastactive'))) {
            if (is_numeric($val)) {
                $this->presaved[$key] = date('Y-m-d H:i:s', $val);
            } elseif (strtotime($val))
                $this->presaved[$key] = date('Y-m-d H:i:s', strtotime($val));
            else
                return false;
        } elseif ($key == 'email') {
            if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            if ($this->isEmailInUse($val) AND ( !isset($this->email) OR $val != $this->email )) {
                return false;
            }
            $this->presaved[$key] = $val;
        } elseif ($key == 'notifications') {
            if(!in_array($val,[0,1])) return false;            
            $this->presaved[$key] = $val;
        } else
            return false;

        return true;
    }

    function save() {
        if (!$this->presaved)
            return false;

        //Set Deafult
        if ($this->uid < 1) {
            if (!isset($this->presaved['regdatum']))
                $this->presave('regdatum', time());
        }
        
        if ($this->uid == 0 AND isset($this->presaved['login'])) {
            try {
                $this->uid = DB::table('user')->insertGetId($this->presaved);
            } catch (Exception $e) {
                addMessage($e->getMessage(),'danger');
                return false;
            }           
        } elseif ($this->uid > 0) {
            try {
                DB::table('user')->where('uid',$this->uid )->update($this->presaved);
            } catch (Exception $e) {
                addMessage($e->getMessage(),'danger');
                return false;
            }           
            
        }

        foreach ($this->presaved as $key => $val)
            $this->$key = $val;

        //TODO: ezt már egyszer leírtam
        $this->username = $this->login;
        $this->nickname = $this->becenev;
        $this->name = $this->nev;
        if(isset($this->jogok))
            $this->roles = explode('-', trim($this->jogok, " \t\n\r\0\x0B-"));
        else
            $this->rolse = [];

        unset($this->presaved);

        return $this->uid;
    }

    function delete() {
        if ($this->uid == 0)
            return false;

        DB::table('user')->where('uid', $this->uid)->delete();
        $this->selfEmpty();
        addMessage('Sikeresen töröltük a felhasználót.', 'success');
        return true;
    }

    function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    function newPassword($text) {
        $this->presave('password', $text);
        $this->save();
    }

    function active() {
        DB::table('user')->where('uid', $this->uid)->update(['lastactive' => date('Y-m-d H:i:s')]);
    }

    function getFavorites() {
        $favorites = array();

        if ($this->uid > 0) {
            $favorites = \Eloquent\Favorite::where('uid',$this->uid)->get()->sortBy(function($favorite){
                        return $favorite->church->nev;
                });
        }
        else {
            $favorites = \Eloquent\Favorite::groupBy('tid')->select('tid', DB::raw('count(*) as total'))->orderBy('total','DESC')->limit(10)->get();
        }        

        foreach ($favorites as $favorite) {
            $this->favorites[$favorite->tid] = $favorite; 
        }

        return $favorites;
    }

    function checkFavorite($tid) {
        if (!isset($this->favorites)) {
            $this->getFavorites();
        }
        foreach ($this->favorites as $favorite) {
            if ($favorite['tid'] == $tid) {
                return true;
            }
        }
        return false;
    }

    function addFavorites($tids) {
        if (!is_array($tids))
            $tids = array($tids);
        foreach ($tids as $tid) {
            if (!is_numeric($tid))
                return false;
        }
        foreach ($tids as $key => $tid) {
            if (!\Eloquent\Church::find($tid))
                unset($tids[$key]);
            else {
                $favorite = new Eloquent\Favorite;
                $favorite->uid = $this->uid;
                $favorite->tid = $tid;
                $favorite->save();
                }
        }
        return true;
    }

    function removeFavorites($tids) {
        if (!is_array($tids))
            $tids = array($tids);
        foreach ($tids as $tid) {
            if (!is_numeric($tid))
                return false;
        }
        try {
            $query = \Eloquent\Favorite::where('uid',$this->uid)->whereIn('tid',$tids)->delete();
            return true;
        } catch (Exception $ex) {
            addMessage($ex->getMessage(), 'danger');
            return false;
        }                
    }

    function isEmailInUse($val) {
        $result = DB::table('user')
                ->select('email')
                ->where('email', $val)
                ->limit(1)
                ->get();
        if (count($result)) {
            return true;
        } else
            return false;
    }

    static function emptyUser() {
        return new \User();
    }

    static function load() {
        if (isset($_SESSION['token'])) {
            if (!$token = validateToken($_SESSION['token'])) {
                setcookie('token', 'null', time());
                return \User::emptyUser();
            }
            $user = new \User($token['uid']);
        } elseif (isset($_COOKIE['token'])) {
            if (!$token = validateToken($_COOKIE['token'])) {
                setcookie('token', 'null', time());
                return \User::emptyUser();
            }
            $timeout = '+1 month';
            $newToken = generateToken($token['uid'], 'web', $timeout);
            setcookie('token', $newToken, strtotime($timeout));
            $_SESSION['token'] = $_COOKIE['token'] = $newToken;
            $user = new \User($token['uid']);
        } else {
            setcookie('token', 'macska', time() + 10000);
            return \User::emptyUser();
        }
        $user->loggedin = true;
        $user->active();
        return $user;
    }

    static function login($name, $password) {
        $name = sanitize($name);
        $userRow = DB::table('user')->where('login', $name)->first();
        if (!$userRow) {
            throw new \Exception("There is no such user.");
        }
        if (!password_verify($password, $userRow->jelszo)) {
            throw new \Exception("Invalid password.");
        }

        $timeout = '+1 month';
        $token = generateToken($userRow->uid, 'web', $timeout);
        setcookie('token', $token, strtotime($timeout));
        $_COOKIE['token'] = $token;
        $_SESSION['token'] = $token;

        DB::table('user')->where('uid', $userRow->uid)->update(['lastlogin' => date('Y-m-d H:i:s')]);
        return $userRow->uid;
    }

    function logout() {
        unset($_SESSION['token']);
        unset($_COOKIE['token']);
        setcookie('token', 'null', time());

        $this->selfEmpty();
        session_destroy();
        session_unset();
    }

    function selfEmpty() {
        foreach ($this as $key => $value)
            unset($this->$key);
        $this->loggedin = false;
        $this->uid = 0;
        $this->username = '*vendeg*';
        $this->nickname = '*vendeg*';
    }

}
