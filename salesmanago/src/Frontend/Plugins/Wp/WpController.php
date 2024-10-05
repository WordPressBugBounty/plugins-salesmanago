<?php

namespace bhr\Frontend\Plugins\Wp;

if(!defined('ABSPATH')) exit;

use bhr\Frontend\Model\Helper;
use bhr\Frontend\Plugins\Wp\WpContactModel as ContactModel;
use WPCF7_Submission;
use bhr\Frontend\Controller\TransferController;
use SALESmanago\Exception\Exception;

class WpController
{
    private $TransferController;
    private $ContactModel;

    public function __construct($PlatformSettings, TransferController $TransferController)
    {
        $this->TransferController = $TransferController;
        if(!$this->ContactModel = new ContactModel($PlatformSettings)) {
            return false;
        }

        return $this;
    }

    /**
     * @param $userId
     * @return bool
     */
    public function registerUser($userId)
    {
        try {
            //Populate new Contact Model with fields from submitted data
            if (!$this->ContactModel->parseContact($userId, WpContactModel::ID)) {
                return false;
            }

            //Set tags from plugin settings
            $this->ContactModel->setTagsFromConfig(WpContactModel::TAGS_REGISTRATION);

	        Helper::doAction('salesmanago_wp_register_contact', array('Contact' => $this->ContactModel->get()));

	        //Transfer Contact with global controller
            return $this->TransferController->transferContact($this->ContactModel->get());

        } catch (Exception $e) {
            error_log(print_r($e->getLogMessage(), true));
            return false;
        } catch (\Exception $e) {
            error_log(print_r($e->getMessage(), true));
            return false;
        }
    }

    /**
     * @param $userLogin
     * @return bool
     */
    public function loginUser($userLogin)
    {
        try {
            if (!$this->checkUserLevel($userLogin)) {
                return true;
            }

            //Populate new Contact Model with fields from submitted data
            if (!$this->ContactModel->parseContact($userLogin, WpContactModel::LOGIN)) {
                return false;
            }

            //Set tags from plugin settings
            $this->ContactModel->setTagsFromConfig(WpContactModel::TAGS_LOGIN);

	        Helper::doAction('salesmanago_wp_login_contact', array('Contact' => $this->ContactModel->get()));

	        //Transfer Contact with global controller
            return $this->TransferController->transferContact($this->ContactModel->get());

        } catch (\Exception $e) {
            error_log(print_r($e->getMessage(), true));
        }
        return false;
    }

    /**
     * @param $contactIdentify
     * @return bool
     */
    protected function checkUserLevel($contactIdentify)
    {
        $contact = (!get_user_by('email', $contactIdentify))
            ? get_user_by('login', $contactIdentify)
            : get_user_by('email', $contactIdentify);

        if (empty($contact)) {
            return true;
        }

        $contact = $contact->get_role_caps();

        if(isset($contact) && isset($contact['level_4'])) {
            return false;
        }
        return true;
    }

}
