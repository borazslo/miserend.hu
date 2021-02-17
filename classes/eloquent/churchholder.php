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
        
        
        static function migrate() {
            $tmps = DB::table('templomok')->select('templomok.id as church_id','user.uid as user_id')->leftJoin('user','templomok.letrehozta','=','user.login')->where('user.uid','<>','')->get();            
            foreach($tmps as $tmp) {                
                $item = \Eloquent\ChurchHolder::updateOrCreate((array) $tmp, ['status' => 'allowed','description' => 'Ő a létrehozója a templomnak.']);
            }
        }
}

