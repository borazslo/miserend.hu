<?php

/*
 CREATE TABLE `miserend`.`church_holders` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `church_id` INT(10) NOT NULL,
  `description` VARCHAR(255) NULL,
  `status` ENUM('asked', 'allowed', 'denied', 'revoked') NOT NULL DEFAULT 'asked',
  `created_at` TIMESTAMP NOT NULL DEFAULT  CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;
 */

namespace Eloquent;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Capsule\Manager as DB;

class ChurchHolder extends Model {
        use \Illuminate\Database\Eloquent\SoftDeletes;
        
        protected $fillable = array('church_id','user_id','description','status');
        protected $appends = array('user');

        function getUserAttribute($value) {
            return new \User($this->user_id);
        }
        
        function getChurchAttribute($value) {
            return \Eloquent\Church::find($this->church_id);
        }
        
        
        /* custom */
    function sendEmails() {                
        /*
         * miserend adminiok
         * egyházmegyei felelős(ök)
         * templom feltöltésre jogosult felhasználó
         */
        $this->append('church')->get();
        $emails = [];        
        
        if($this->status == 'asked') {
            /* Miserend Adminok */
            $admins = DB::table('user')->where('jogok','LIKE','%miserend%')->where('notifications',1)->get();
            foreach($admins as $admin) {
                $emails[$admin->email] = [$this->status."_admin",$admin];            
            }
        } elseif($this->status == 'allowed') {
            $emails[$this->user->email] = [$this->status."_user",$this->user];
            
        }
        
        
        foreach($emails as $addressee) {
            $this->addressee = $addressee[1];
            $mail = new \Eloquent\Email();                
            
            $mail->render('churchholders_'.$addressee[0],$this);
            $mail->send($addressee[1]->email);    
            
            
        }
        return true;
    }
}
