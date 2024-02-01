<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Model;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class ChurchHolder extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = ['church_id', 'user_id', 'description', 'status'];
    protected $appends = ['user'];

    public function getUserAttribute($value)
    {
        return new \App\Legacy\User($this->user_id);
    }

    public function getChurchAttribute($value)
    {
        return Church::find($this->church_id);
    }

    /* custom */
    public function sendEmails()
    {
        /*
         * miserend adminiok
         * egyházmegyei felelős(ök)
         * templom feltöltésre jogosult felhasználó
         */
        $this->append('church')->get();
        $emails = [];

        if ('asked' == $this->status) {
            /* Miserend Adminok */
            $admins = DB::table('user')->where('jogok', 'LIKE', '%miserend%')->where('notifications', 1)->get();
            foreach ($admins as $admin) {
                $emails[$admin->email] = [$this->status.'_admin', $admin];
            }
        } elseif ('allowed' == $this->status) {
            $emails[$this->user->email] = [$this->status.'_user', $this->user];
        }

        foreach ($emails as $addressee) {
            $this->addressee = $addressee[1];
            $mail = new Email();

            $mail->render('churchholders_'.$addressee[0], $this);
            $mail->send($addressee[1]->email);
        }

        return true;
    }
}
