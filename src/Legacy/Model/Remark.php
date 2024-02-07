<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Model;

use Illuminate\Database\Capsule\Manager as DB;

class Remark extends \Illuminate\Database\Eloquent\Model
{
    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function scopeSelectCreatedMonth($query)
    {
        return $query->addSelect(DB::raw('DATE_FORMAT(created_at,\'%Y-%m\') as created_month'), DB::raw('COUNT(*) as count_created_month'));
    }

    public function scopeSelectCreatedYear($query)
    {
        return $query->addSelect(DB::raw('DATE_FORMAT(created_at,\'%Y\') as created_year'), DB::raw('COUNT(*) as count_created_year'));
    }

    public function scopeCountByCreatedMonth($query)
    {
        return $query->selectCreatedMonth()
                        ->groupBy('created_month')->orderBy('created_month');
    }

    public function scopeCountByCreatedYear($query)
    {
        return $query->selectCreatedYear()
                        ->groupBy('created_year')->orderBy('created_year');
    }

    public function getChurchAttribute($value)
    {
        return Church::find($this->church_id);
    }

    /*
     * custom functions
     */
    public function appendComment($text)
    {
        $user = $this->getSecurity()->getUser();
        if ($text != '') {
            $newline = "\n<img src='/img/edit.gif' align='absmiddle' title='".$user->username.' ('.date('Y-m-d H:i:s').")'>".$text;
            $this->adminmegj .= $newline;
        }
    }

    public function emails()
    {
        /*
         * miserend adminiok
         * egyházmegyei felelős(ök)
         * templom feltöltésre jogosult felhasználó
         */
        $emails = [];
        /* Miserend Adminok */
        $admins = DB::table('user')->where('jogok', 'LIKE', '%miserend%')->where('notifications', 1)->get();
        foreach ($admins as $admin) {
            $emails[$admin->email] = ['admin', $admin->email, $admin];
        }
        /* Egyházmegyei felelős (csak felhasználónév alapján) */
        $responsabile = DB::table('egyhazmegye')->select('user.*')->where('egyhazmegye.id', $this->church->egyhazmegye)->leftJoin('user', 'user.login', '=', 'egyhazmegye.felelos')->where('notifications', 1)->first();
        if ($responsabile) {
            $emails[$responsabile->email] = ['diocese', $responsabile->email, $responsabile];
        }
        /* Templom felelősök */
        $churchHolders = DB::table('church_holders')->where('church_id', $this->church->id)->where('church_holders.status', 'allowed')->leftJoin('user', 'user.uid', '=', 'church_holders.user_id')->where('user.notifications', 1)->get();
        foreach ($churchHolders as $churchHolder) {
            $emails[$churchHolder->email] = ['responsible', $churchHolder->email, $churchHolder];
        }

        foreach ($emails as $email) {
            $this->sendMail($email[0], $email[1], $email[2]);
        }

        return true;
    }

    public function sendMail($type, $to, $addressee = false)
    {
        if ($addressee) {
            $this->addressee = $addressee;
        } else {
            $this->addressee = false;
        }

        $this->append('church');

        $mail = new Email();
        $mail->render('remark_'.$type, $this);
        $mail->send($to);
    }
}
