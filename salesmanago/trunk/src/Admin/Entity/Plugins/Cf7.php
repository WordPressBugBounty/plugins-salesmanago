<?php

namespace bhr\Admin\Entity\Plugins;

if(!defined('ABSPATH')) exit;

use bhr\Admin\Entity\AbstractEntity;
use bhr\Admin\Entity\Plugins\DoubleOptIn;
use bhr\Admin\Model\Helper;

class Cf7 extends AbstractPlugin
{
    public static function listAvailableForms()
    {
        $args = array(
            'post_type' => 'wpcf7_contact_form',
            'posts_per_page' => -1);
        $availableFormsList = array();
        if ($data = Helper::getCf7Forms($args)) {
            foreach ($data as $key) {
                $availableFormsList[$key->ID] = $key->post_title;
            }
        }
        return $availableFormsList;
    }

    public function setLegacyFormsCf7($forms = array())
    {
        $availableFormsList = self::listAvailableForms();
        parent::setLegacyForms($forms, $availableFormsList);
        return $this;
    }

    /**
     * @return string
     */
    public static function getNoFormsMessage()
    {
        return __('No forms for CF7 found:', 'salesmanago').' <a href="admin.php?page=wpcf7-new">'.__('add new form', 'salesmanago').'</a>.';
    }
}
