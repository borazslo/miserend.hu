<?php

class Request {
    
   function IntegerRequired($name) {
       $value = self::getRequired($name);         
       if(!is_numeric($value)) {
           throw new Exception("Required '$name' is not an Integer.");
       }
       return $value;
   }

   function SimpletextRequired($name) {
       $value = self::getRequired($name);         
       if(!preg_match('/^[a-zA-Z_-]+$/i',$value)) {
           throw new Exception("Required '$name' is not a SimpleText.");
       }
       return $value;
   }

   function DateRequired($name) {
       $value = self::getRequired($name);         
       if(strtotime($value) == false) {
           throw new Exception("Required '$name' is not a Date.");
       }
       return date('Y-m-d',strtotime($value));
   }

   function DatewDefault($name,$default = false) {
       $value = self::getwDefault($name,$default);
       if(strtotime($value) == false) {
           throw new Exception("Required '$name' is not a Date.");
       }
       return date('Y-m-d',strtotime($value));
   }

   private function getwDefault($name,$default = false) {
       if($value = self::get($name)) {
           return $value;
       } else {
           return $default;
       }
   }
   
   private function getRequired($name) {
       if(!$value = self::get($name)) {
            throw new Exception("Required '$name' is required.");
       } else {
           return $value;
       }
   }
   
   private function get($name) {
       if(isset($_REQUEST[$name])) {
           return $_REQUEST[$name];
       } else {
           return false;
       }
   }  
}