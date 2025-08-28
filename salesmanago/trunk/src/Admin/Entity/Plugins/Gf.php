<?php

namespace bhr\Admin\Entity\Plugins;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Entity\AbstractEntity;
use bhr\Admin\Entity\MessageEntity;
use bhr\Admin\Entity\Plugins\DoubleOptIn;
use bhr\Admin\Model\Helper;
use \RGFormsModel;
use SALESmanago\Exception\Exception;

class Gf extends AbstractPlugin
{
    public static function listAvailableForms()
    {
        $availableFormsList = array();
        try {
            if ($formsList = Helper::getGfForms()) {
                foreach ($formsList as $form) {
                    $availableFormsList[$form->id] = $form->title;
                }
            }
        } catch (\Exception $e) {
            throw new Exception("Error on obtaining Gravity Forms list: " . print_r($e, true), 674);
        }
        return $availableFormsList;
    }

    public function setLegacyFormsGf($forms = array())
    {
        $availableFormsList = array();
        try {
            $availableFormsList = self::listAvailableForms();
        } catch (Exception $e) {
            MessageEntity::getInstance()->addException($e);
        }
        parent::setLegacyForms($forms, $availableFormsList);
        return $this;
    }

    /**
     * @return string
     */
    public static function getNoFormsMessage()
    {
        return __('No forms for GF found:', 'salesmanago').' <a href="admin.php?page=gf_new_form">'.__('add new form', 'salesmanago').'</a>.';
    }
}
