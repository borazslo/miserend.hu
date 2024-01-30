<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use App\Legacy\Security;
use Illuminate\Database\Capsule\Manager as DB;

class User
{
    private ?string $username = null;
    private ?string $name = null;
    private ?string $nickname = null;
    private ?string $email = null;
    private ?string $jogok = null;
    private $uid;
    private array $responsible = [];
    private $loggedin;
    private bool $isadmin = false;
    private array $favorites = [];

    private $login;

    private $jelszo;

    private $regDatum;

    private $lastlogin;

    private $lastactive;

    private $notifications;

    private $becenev;

    private $nev;

    private $volunteer;

    public function __construct(
        int $uid = null,
        string $email = null,
        string $login = null,
    ) {
        if (null !== $uid) {
            $user = DB::table('user')
                ->select('*');

            if (null !== $email) {
                $user = $user->where('email', $email);
            } elseif (null !== $login) {
                $user = $user->where('login', $login);
            } else {
                $user = $user->where('uid', $uid);
            }
            $user = $user->first();

            if ($user) {
                foreach ($user as $key => $value) {
                    $this->{'set'.ucfirst($key)}($value);
                }
                $this->username = $user->login;
                $this->nickname = $user->becenev;
                $this->name = $user->nev;
                $this->roles = explode('-', trim($this->jogok, " \t\n\r\0\x0B-"));
                foreach ($this->roles as $k => $v) {
                    if ('' == $v) {
                        unset($this->roles[$k]);
                    }
                }
                $this->getResponsabilities();
                if ($this->checkRole('miserend')) {
                    $this->isadmin = true;
                }

                return true;
            } else {
                // TODO: kitalálni mit csináljon, ha  nincs megfelelő azobosítójú user. Legyen vendég?
                // There is no user with this uid;
                $uid = 0;
                // return false;
            }
        }
        // Lássuk a vendégeket
        if (!isset($uid) || !is_numeric($uid) || 0 == $uid) {
            $this->loggedin = false;
            $this->uid = 0;
            $this->username = '*vendeg*';
            $this->nickname = '*vendég*';
            $this->responsible = [];
        }
    }

    /**
     * @deprecated
     * @see Security::isGranted()
     */
    public function checkRole($role = false): bool
    {
        if (false == $role) {
            return true;
        }

        if ('"any"' == $role || "'any'" == $role) {
            if (!isset($this->jogok)) {
                return false;
            }
            if ('' != trim(preg_replace('/-/i', '', $this->jogok))) {
                return true;
            } else {
                return false;
            }
        } elseif (preg_match('/^ehm:([0-9]{1,3})$/i', $role, $match)) {
            $isResponsible = DB::table('egyhazmegye')->where('id', $match[1])->where('felelos', $this->username)->first();
            if ($isResponsible) {
                return true;
            } else {
                return false;
            }
        } elseif (isset($this->jogok) && preg_match('/(^|-)'.$role.'(-|$)/i', $this->jogok)) {
            return true;
        } else {
            return false;
        }
    }

    public function getHoldingData($church_id)
    {
        $holding = Model\ChurchHolder::where('user_id', $this->uid)->where('church_id', $church_id)->orderBy('updated_at', 'desc')->first();
        if ($holding) {
            $holding->setAppends([]);
        }

        return $holding;
    }

    public function getResponsabilities()
    {
        $this->responsibilities = [
            'diocese' => [],
            'church' => [],
        ];
        if ($this->uid > 0) {
            $results = DB::table('egyhazmegye')
                ->select('id')
                ->where('ok', 'i')
                ->where('felelos', $this->username)
                ->get();
            foreach ($results as $result) {
                $this->responsible['diocese'][] = $result->id;
            }

            $this->responsibilities['church'] = Model\ChurchHolder::where('user_id', $this->uid)->get()->groupBy('status');

            $this->responsible['church'] = Model\ChurchHolder::where('user_id', $this->uid)->where('status', 'allowed')->get()->Pluck(
                'church_id'
            )->toArray();
        }
    }

    public function processResponsabilities()
    {
        if (!isset($this->responsible)) {
            $this->getResponsabilities();
        }

        $tmp = [];
        foreach ($this->responsible['church'] as $church) {
            $tmp[$church] = Model\Church::find($church);
        }
        $this->responsible['church'] = $tmp;
    }

    public function getRemarks($limit = false, $ago = false)
    {
        if (false == $limit || !is_numeric($limit)) {
            $limit = 5;
        }

        $query = Model\Remark::select('*', DB::raw('count(*) as total'))->where(function ($q) {
            $q->where('login', $this->username)->orWhere('email', $this->email);
        });
        if (false != $ago) {
            $query = $query->where('datum', '>', date('Y-m-d H:i:s', strtotime('-'.$ago)));
        }

        $query = $query->groupBy('church_id')->orderBy('created_at', 'desc');

        $this->remarksCount = $query->count();
        $this->remarks = $query->limit($limit)->get();

        return true;
    }

    public function submit($vars)
    {
        $return = true;

        if (isset($vars['uid']) && !is_numeric($vars['uid']) && '' != $vars['uid']) {
            addMessage('Nincs ilyen felhasználónk!', 'danger');

            return false;
        }

        $dangers = [
            'uid' => 'Probléma támadt az azonosítóval!',
            'username' => 'Probléma a felhasználónévvel! (Nem megfelelő karakterek, vagy már használatban van. A felhasználó nevet nem lehet megváltoztatni.)',
            'nickname' => 'Probléma a becenévvel!',
            'name' => 'Probléma a névvel!',
            'email' => 'Nem megfelelő email cím! Talán már használatban van?',
            'volunteer' => 'Hibás értéke van az önkéntességnek!',
            'roles' => 'Hibás formátumú jogkörök!',
            'notifications' => 'Email értesítések engedélyezése körül hiba lépett fel!',
        ];

        foreach (['uid', 'username', 'nickname', 'name', 'email', 'volunteer', 'roles', 'notifications'] as $input) {
            if (isset($vars[$input])) {
                if (!$this->presave($input, $vars[$input])) {
                    $return = false;
                    addMessage($dangers[$input], 'danger');
                }
            }
        }

        if (isset($vars['uid']) && ('' != $vars['password1'] || '' != $vars['password2'])) {
            if ($vars['password1'] != $vars['password2'] || '' == $vars['password1']) {
                addMessage('A két jelszó nem egyezik meg egymással', 'danger');
                $return = false;
            } else {
                if (!$this->presave('password', $vars['password1'])) {
                    $return = false;
                    addMessage('Sajnos nem megfelelő a jelszó!', 'danger');
                }
            }
        }

        if (false == $return) {
            return false;
        }

        if (!isset($vars['uid'])) {
            $pwd = $this->generatePassword();
            $this->presave('password', $pwd);
        }

        if (!$this->save()) {
            addMessage('Nem sikerült elmenteni. Pedig minden rendben volt előtte.', 'warning');

            return false;
        } else {
            if (!isset($vars['uid'])) {
                addMessage('A felhasználót sikeresen létrehoztuk.', 'success');

                $this->newpwd = $pwd;
                $email = new Model\Email();
                $email->render('user_welcome', $this);
                if ($email->send($this->email)) {
                    addMessage('Elküldtük az emailt az új regisztrációról.', 'success');
                }
            } else {
                addMessage('A változásokat elmentettük.', 'success');
            }
        }

        return true;
    }

    public function presave($key, $val)
    {
        if (!isset($this->presaved)) {
            $this->presaved = [];
        }
        // TODO: check duplicate for: logn + email
        // TODO: van, amit ne engedjen, csak, amikor még tök új a cuccos.
        // TODO: a nickname - becenev / name - nev esetén ez nem segít, bár nem sok dupla munka azért
        // TODO: elrontja ...
        // if($this->$key == $val) return true;
        // TODO: szóljon vissza a kötelező
        if ('' == $val && \in_array($key, ['username', 'login', 'email'])) {
            return false;
        }

        if ('uid' == $key) {
            if ($this->uid != $val) {
                return false;
            }
        } elseif (\in_array($key, ['username', 'login'])) {
            if (0 == $this->uid) {
                if (!checkUsername($val)) {
                    return false;
                }
                $this->presaved['login'] = sanitize($val);
            } elseif ($this->username != $val) {
                return false;
            }
        } elseif (\in_array($key, ['jelszo', 'password'])) {
            $this->presaved['jelszo'] = password_hash($val, \PASSWORD_BCRYPT);
        } elseif ('roles' == $key || 'jogok' == $key) {
            if (!\is_array($val)) {
                $val = [$val];
            }
            $jogok = [];
            foreach ($val as $k => $i) {
                if ($k == $i) {
                    $jogok[$k] = trim(sanitize($i), '-');
                }
            }
            $jogok = array_unique($jogok);
            $this->presaved['jogok'] = implode('-', $jogok);
        } elseif ('nickname' == $key || 'becenev' == $key) {
            $this->presaved['becenev'] = sanitize($val);
        } elseif ('name' == $key || 'nev' == $key) {
            $this->presaved['nev'] = sanitize($val);
        } elseif ('volunteer' == $key) {
            if ('' == $val) {
                $this->presaved[$key] = 0;
            } elseif (\in_array($val, [0, 1])) {
                $this->presaved[$key] = $val;
            } else {
                return false;
            }
        } elseif (\in_array($key, ['regdatum', 'lastlogin', 'lastactive'])) {
            if (is_numeric($val)) {
                $this->presaved[$key] = date('Y-m-d H:i:s', $val);
            } elseif (strtotime($val)) {
                $this->presaved[$key] = date('Y-m-d H:i:s', strtotime($val));
            } else {
                return false;
            }
        } elseif ('email' == $key) {
            if (!filter_var($val, \FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            if ($this->isEmailInUse($val) && (!isset($this->email) || $val != $this->email)) {
                return false;
            }
            $this->presaved[$key] = $val;
        } elseif ('notifications' == $key) {
            if (!\in_array($val, [0, 1])) {
                return false;
            }
            $this->presaved[$key] = $val;
        } else {
            return false;
        }

        return true;
    }

    public function save()
    {
        if (!$this->presaved) {
            return false;
        }

        // Set Deafult
        if ($this->uid < 1) {
            if (!isset($this->presaved['regdatum'])) {
                $this->presave('regdatum', time());
            }
        }

        if (0 == $this->uid && isset($this->presaved['login'])) {
            try {
                $this->uid = DB::table('user')->insertGetId($this->presaved);
            } catch (Exception $e) {
                addMessage($e->getMessage(), 'danger');

                return false;
            }
        } elseif ($this->uid > 0) {
            try {
                DB::table('user')->where('uid', $this->uid)->update($this->presaved);
            } catch (Exception $e) {
                addMessage($e->getMessage(), 'danger');

                return false;
            }
        }

        foreach ($this->presaved as $key => $val) {
            $this->$key = $val;
        }

        // TODO: ezt már egyszer leírtam
        $this->username = $this->login;
        $this->nickname = $this->becenev;
        $this->name = $this->nev;
        if (isset($this->jogok)) {
            $this->roles = explode('-', trim($this->jogok, " \t\n\r\0\x0B-"));
        } else {
            $this->rolse = [];
        }

        unset($this->presaved);

        return $this->uid;
    }

    public function delete()
    {
        if (0 == $this->uid) {
            return false;
        }

        Model\ChurchHolder::where('user_id', $this->uid)->delete();
        Model\Favorite::where('uid', $this->uid)->delete();

        DB::table('user')->where('uid', $this->uid)->delete();

        foreach ($this as $key => $value) {
            unset($this->$key);
        }
        $this->loggedin = false;
        $this->uid = 0;
        $this->username = '*vendeg*';
        $this->nickname = '*vendeg*';

        addMessage('Sikeresen töröltük a felhasználót.', 'success');

        return true;
    }

    public function generatePassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; ++$i) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    public function newPassword($text)
    {
        $this->presave('password', $text);
        $this->save();
    }

    public function active()
    {
        DB::table('user')->where('uid', $this->uid)->update(['lastactive' => date('Y-m-d H:i:s')]);
    }

    public function getFavorites()
    {
        $favorites = [];

        if ($this->uid > 0) {
            $favorites = Model\Favorite::where('uid', $this->uid)->get()->sortBy(function ($favorite) {
                return $favorite->church->nev;
            });
        } else {
            $favorites = Model\Favorite::groupBy('tid')->select('tid', DB::raw('count(*) as total'))->orderBy('total', 'DESC')->limit(
                10
            )->get();
        }

        foreach ($favorites as $favorite) {
            $this->favorites[$favorite->tid] = $favorite;
        }

        return $favorites;
    }

    public function checkFavorite($tid)
    {
        if (!isset($this->favorites)) {
            $this->favorites = $this->getFavorites();
        }
        foreach ($this->favorites as $favorite) {
            if ($favorite['tid'] == $tid) {
                return true;
            }
        }

        return false;
    }

    public function addFavorites($tids)
    {
        if (!\is_array($tids)) {
            $tids = [$tids];
        }
        foreach ($tids as $tid) {
            if (!is_numeric($tid)) {
                return false;
            }
        }
        foreach ($tids as $key => $tid) {
            if (!Model\Church::find($tid)) {
                unset($tids[$key]);
            } else {
                $favorite = new Model\Favorite();
                $favorite->uid = $this->uid;
                $favorite->tid = $tid;
                $favorite->save();
            }
        }

        return true;
    }

    public function removeFavorites($tids)
    {
        if (!\is_array($tids)) {
            $tids = [$tids];
        }
        foreach ($tids as $tid) {
            if (!is_numeric($tid)) {
                return false;
            }
        }
        try {
            $query = Model\Favorite::where('uid', $this->uid)->whereIn('tid', $tids)->delete();

            return true;
        } catch (Exception $ex) {
            addMessage($ex->getMessage(), 'danger');

            return false;
        }
    }

    public function isEmailInUse($val)
    {
        $result = DB::table('user')
            ->select('email')
            ->where('email', $val)
            ->limit(1)
            ->get();
        if (\count($result)) {
            return true;
        } else {
            return false;
        }
    }

    /** @deprecated  */
    public static function login($name, $password)
    {
        // api login hasznalja meg
    }

    public static function deleteNonActivatedUsers()
    {
        $waitingBeforeDelete = '2 weeks';

        $users2delete = DB::table('user')
            ->where('lastlogin', '0000-00-00 00:00:00')
            ->where('regdatum', '<', date('Y-m-d H:i:s', strtotime('-'.$waitingBeforeDelete)));
        // We delete on if we have already sent user_pleaseactivate message
        $users2delete->whereRaw(
            DB::RAW(
                " EXISTS (
					SELECT *
					FROM emails
					WHERE
						`type` = 'user_pleaseactivate' AND
						`status` = 'sent' AND
						emails.to = user.email AND
						updated_at < '".date('Y-m-d H:i:s', strtotime('-2 days'))."'
						ORDER BY updated_at DESC
						LIMIT 1
					) "
            )
        );

        $results = $users2delete->orderByRaw('RAND()')
            ->limit(20)            // Lehetne végtelen, de jobb az óvatosság. Pláne, hogy még egyesével mennek az emailek
            ->get();            // Lehetne itt rögtön ->delete(), de a debug dolog miatt jobb, ha tovább is van

        $ids2delete = [];
        foreach ($results as $result) {
            $ids2delete[] = $result->uid;

            $email = new Model\Email();
            $email->to = $result->email;
            $email->render('user_youhavebeendeleted', $result);
            // $email->addToQueue();
            $email->send();
        }
        $count = DB::table('user')->whereIN('uid', $ids2delete)->delete();

        if ($count != \count($ids2delete)) {
            addMessage('Nem sikerül mindenkit törölni.', 'error');
            echo 'Nem sikerült mindenkit aki még nem lépett be törölni! '.print_r($ids2delete, 1).' ';
        }
    }

    public static function sendActivationNotification()
    {
        $lastEmailDiff = '-1 week';

        $users2notify = DB::table('user')
            ->where('lastlogin', '0000-00-00 00:00:00')
            ->where('notifications', 1)
            ->orderByRaw('RAND()')
            ->limit(5)
            ->get();

        foreach ($users2notify as $user) {
            $lastEmail = DB::table('emails')
                ->where('type', 'user_pleaseactivate')
                ->where('to', $user->email)
                ->whereIn('status', ['queued', 'sent'])
                ->orderBy('updated_at', 'desc')
                ->first();

            // Van olyan emlékztetőnk ami a sorban várakozik
            // vagy nincs egy hete hogy küldtünk neki értesítőt
            if (isset($lastEmail) && (
                'queued' == $lastEmail->status
                || strtotime($lastEmail->updated_at) > strtotime($lastEmailDiff)
            )) {
                // Nincs mit tenni
            } else {
                // Nincs még korábbi értesítő, vagy az már öregebb mint egy hét
                $user->inactiveDays = abs(round((time() - strtotime($user->regdatum)) / 86400));

                $email = new Model\Email();
                $email->to = $user->email;
                $email->render('user_pleaseactivate', $user);
                // $email->addToQueue();
                $email->send();
            }
        }

        return true;
    }

    public static function sendUpdateNotification()
    {
        $users2notify = DB::table('templomok')
            ->select('templomok.id as tid', 'templomok.nev', 'templomok.ismertnev', 'templomok.varos', 'templomok.frissites')
            ->join('church_holders', 'templomok.id', '=', 'church_holders.church_id')
            ->addSelect('church_holders.description')
            ->join('user', 'user.uid', '=', 'church_holders.user_id')
            ->addSelect('user.*');

        $users2notify->whereRaw(
            DB::RAW(
                " NOT EXISTS (
					SELECT *
					FROM emails
					WHERE
						`type` = 'user_pleaseupdate' AND
						`status` IN ('sent','queued') AND
						emails.to = user.email AND
						updated_at > '".date('Y-m-d H:i:s', strtotime('-2 weeks'))."'
						ORDER BY updated_at DESC
						LIMIT 1
					) "
            )
        );

        $users2notify = $users2notify->where('church_holders.status', 'allowed')
            ->whereNull('church_holders.deleted_at')
            ->where('user.jogok', 'not like', '%miserend%')->where('user.notifications', 1)
            ->whereNotNull('user.email')
            ->where('user.email', '<>', '')
            ->where('templomok.frissites', '<', date('Y-m-d', strtotime('-1 year')))->where('templomok.ok', 'i')
            ->groupBy('user.email')
            ->orderByRaw('RAND()')
            ->limit(5)
            ->get();

        foreach ($users2notify as $user2notify) {
            $user = new self($user2notify->uid);
            $user->getResponsabilities();

            foreach ($user->responsible['church'] as $key => $churchID) {
                $user->responsible['church'][$churchID] = Model\Church::find($churchID);
                unset($user->responsible['church'][$key]);
            }

            $email = new Model\Email();
            $email->to = $user->email;
            $email->render('user_pleaseupdate', $user);
            // $email->addToQueue();
            $email->send();
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * @param string|null $nickname
     */
    public function setNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getJogok(): ?string
    {
        return $this->jogok;
    }

    /**
     * @param mixed $jogok
     */
    public function setJogok($jogok): void
    {
        $this->jogok = $jogok;
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     */
    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @return array
     */
    public function getResponsible(): array
    {
        return $this->responsible;
    }

    /**
     * @param array $responsible
     */
    public function setResponsible(array $responsible): void
    {
        $this->responsible = $responsible;
    }

    /**
     * @return false
     */
    public function getLoggedin(): bool
    {
        return $this->loggedin;
    }

    /**
     * @param false $loggedin
     */
    public function setLoggedin(bool $loggedin): void
    {
        $this->loggedin = $loggedin;
    }

    /**
     * @param bool $isadmin
     */
    public function setIsadmin(bool $isadmin): void
    {
        $this->isadmin = $isadmin;
    }

    /**
     * @return bool
     */
    public function getIsadmin(): bool
    {
        return $this->isadmin;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login): void
    {
        $this->login = $login;
    }

    /**
     * @return mixed
     */
    public function getJelszo()
    {
        return $this->jelszo;
    }

    /**
     * @param mixed $jelszo
     */
    public function setJelszo($jelszo): void
    {
        $this->jelszo = $jelszo;
    }

    /**
     * @return mixed
     */
    public function getRegDatum()
    {
        return $this->regDatum;
    }

    /**
     * @param mixed $regDatum
     */
    public function setRegDatum($regDatum): void
    {
        $this->regDatum = $regDatum;
    }

    /**
     * @return mixed
     */
    public function getLastlogin()
    {
        return $this->lastlogin;
    }

    /**
     * @param mixed $lastlogin
     */
    public function setLastlogin($lastlogin): void
    {
        $this->lastlogin = $lastlogin;
    }

    /**
     * @return mixed
     */
    public function getLastactive()
    {
        return $this->lastactive;
    }

    /**
     * @param mixed $lastactive
     */
    public function setLastactive($lastactive): void
    {
        $this->lastactive = $lastactive;
    }

    /**
     * @return mixed
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param mixed $notifications
     */
    public function setNotifications($notifications): void
    {
        $this->notifications = $notifications;
    }

    /**
     * @return mixed
     */
    public function getBecenev()
    {
        return $this->becenev;
    }

    /**
     * @param mixed $becenev
     */
    public function setBecenev($becenev): void
    {
        $this->becenev = $becenev;
    }

    /**
     * @return mixed
     */
    public function getNev()
    {
        return $this->nev;
    }

    /**
     * @param mixed $nev
     */
    public function setNev($nev): void
    {
        $this->nev = $nev;
    }

    /**
     * @return mixed
     */
    public function getVolunteer()
    {
        return $this->volunteer;
    }

    /**
     * @param mixed $volunteer
     */
    public function setVolunteer($volunteer): void
    {
        $this->volunteer = $volunteer;
    }


}
