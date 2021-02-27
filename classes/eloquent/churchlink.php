<?php
/*
    CREATE TABLE `miserend`.`church_links` (
      `id` INT(10) NOT NULL AUTO_INCREMENT,
      `church_id` INT(10) NOT NULL,
      `href` VARCHAR(255) NULL,
      `title` VARCHAR(255) NULL,
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

class ChurchLink extends Model {
        use \Illuminate\Database\Eloquent\SoftDeletes;
        
        protected $fillable = array('church_id','href','title');
        protected $appends = array('type','icon','html');

        protected $icons = [
            'instagram' => 'fab fa-instagram',
            'facebook' => 'fab fa-facebook-square'
        ];
        
        function getHrefAttribute($value) {            
            if(!preg_match('/^http(s|):\/\//i',$value)) {                
                return 'http://'.$value;
            } else 
                return $value;
        }
        
        function getTypeAttribute($value) {
            $link =  trim(preg_replace('/^http(s|):\/\//i','',$this->href),'/');
            $parts = explode('/',$link);
            if(preg_match('/('.implode('|',array_keys($this->icons)).')/',$parts[0],$match)) {
                return $match[1];
            }
        }
        
        function getIconAttribute($value) {
            if(isset($this->icons[$this->type]))
                return $this->icons[$this->type];
            else 
                return 'fa fa-globe';            
        }
        
        function getHtmlAttribute($value) {
            return '<i class="'.$this->icon.'"></i> <a href="'.$this->href.'" title="'.$this->type.'">'.$this->title.'</a>';
        }
        
        function getTitleAttribute($value) {
            if(!$value) {
                $link =  trim(preg_replace('/^http(s|):\/\//i','',$this->href),'/');
                $link = preg_replace('/^www\./i','',$link);
                if($this->type) {                
                    $parts = explode('/',$link);                
                    unset($parts[0]);
                    return implode('/',$parts);
                } else 
                    return $link;            
            } else
                return $value;
           
        }
        
        function getChurchAttribute($value) {
            return \Eloquent\Church::find($this->church_id);
        }
}    