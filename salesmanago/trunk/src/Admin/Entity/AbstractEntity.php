<?php

namespace bhr\Admin\Entity;

if(!defined('ABSPATH')) exit;

class AbstractEntity
{
    public static $tagsTypes = array(
        'registration',
        'login',
        'newsletter',
        'purchase',
        'guestPurchase'
    );

    /**
     * @return array
     */
    public function toArray()
   {
       $out = array();
       foreach (get_object_vars($this) as $key=>$val) {
           if(is_object($val) && !is_array($val)) {
               $out[$key] = $val->toArray();
           } else {
               $out[$key] = $val;
           }
       }
       return $out;
   }

}