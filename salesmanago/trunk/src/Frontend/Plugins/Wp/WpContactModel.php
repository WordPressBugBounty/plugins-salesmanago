<?php

namespace bhr\Frontend\Plugins\Wp;

if(!defined('ABSPATH')) exit;

use bhr\Frontend\Model\AbstractContactModel;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Exception\Exception;

class WpContactModel extends AbstractContactModel
{
    const
        NICK_N = 'nickname',
        F_NAME = 'first_name',
        L_NAME = 'last_name',
        DESC   = 'description',
        LOCALE = 'locale',
        ID     = 'id',
        LOGIN  = 'login';

    private   $contactData;
    private   $context;

    public function __construct($PlatformSettings)
    {
        //do not continue without settings
        if(empty($PlatformSettings) || empty($PlatformSettings->PluginWp)) {
            return false;
        }
        //create an Abstract Contact
        parent::__construct($PlatformSettings, $PlatformSettings->PluginWp);
        return true;
    }

    /**
     * @param $user
     * @param string $context
     * @return Contact|null
     */
    public function parseContact($user, $context = 'id')
    {
        $this->context = $context;
        if(!$this->getUserInfo($user)) {
            return null;
        }

        //No email no fun
        if (!isset($this->contactData->user_email)
            || empty($this->contactData->user_email)) {
            return null;
        }

        /* Contact */
        $this->Contact->setEmail($this->contactData->user_email)
            ->setName(isset($this->contactData->user_name) ? $this->contactData->user_name : '');

        /* Options */
        $this->setLanguage();
        $this->setOptInStatuses();

        /* Properties */
        $this->Contact->setProperties($this->Properties);
        return $this->Contact;
    }

    /**
     * @param $user
     * @return bool|null
     */
    private function getUserInfo($user)
    {
        if(!isset($user) || empty($user)) {
            return null;
        }

        if ($this->context === self::ID) {
            $this->contactData = \WP_User::get_data_by('id', $user);
        } elseif ($this->context === self::LOGIN) {
            $this->contactData = get_user_by('login', $user);
        }

        if (!$this->contactData) {
            return false;
        }

        if (!isset($this->contactData->user_email) || empty($this->contactData->user_email)) {
            return false;
        }

        $id = $this->contactData->ID;
        if (!empty($id)) {
            $name = trim(get_user_meta($id, self::F_NAME, true) . ' ' .
                get_user_meta($id, self::L_NAME, true));
        }

        if (empty($name)) {
            $name = trim(get_user_meta($id, self::NICK_N, true));
        }

        if (empty($name)) {
            if (isset($_REQUEST['action'])
                && $_REQUEST['action'] == 'save_account_details'
                && !empty($_REQUEST['account_first_name'])
                && !empty($_REQUEST['account_last_name'])
            ) {
                $name = trim(
                    $_REQUEST['account_first_name'] . ' ' .
                    $_REQUEST['account_last_name']);
            }
            if (empty($name)) {
                $name = trim($this->contactData['user_login']);
            }
        }
        if(!empty($name)) {
            $this->contactData->user_name = $name;
        }
        return true;
    }
}
