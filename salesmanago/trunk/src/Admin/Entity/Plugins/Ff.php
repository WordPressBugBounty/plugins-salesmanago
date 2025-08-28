<?php

namespace bhr\Admin\Entity\Plugins;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Model\Helper;
use SALESmanago\Exception\Exception;

class Ff extends AbstractPlugin
{
    /**
     * @return array
     * @throws Exception
     */
    public static function listAvailableForms()
    {
        try {
            $availableFormsList = array();
            if ($forms = Helper::getFfForms()) {
	            foreach ( $forms as $key ) {
		            $availableFormsList[ $key->id ] = $key->title;
	            }
            }
            return $availableFormsList;
        }catch(\Exception $e){
            throw new Exception("Error on obtaining Fluent Forms list: " . print_r($e, true), 681);
        }
    }

    /**
     * @return string
     */
    public static function getNoFormsMessage()
    {
        return __('No forms for FF found:', 'salesmanago').' <a href="admin.php?page=fluent_forms#add=1">'.__('add new form', 'salesmanago').'</a>.';
    }
}
