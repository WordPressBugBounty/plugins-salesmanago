<?php

namespace bhr\Frontend\Plugins\Gf;

if(!defined('ABSPATH')) exit;

use bhr\Frontend\Model\Helper;
use bhr\Frontend\Plugins\Gf\GfContactModel as ContactModel;
use bhr\Frontend\Controller\TransferController;
use SALESmanago\Exception\Exception;

class GfController
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
     * @param $formValues
     * @param $formSetup
     * @return bool
     */
    public function execute($formValues, $formSetup)
    {
        try {
            //Check if submission is not empty
            if (empty($formValues)) {
                return false;
            }

            //Get config for submitted form (tags, owner)
            if(!$this->ContactModel->setCurrentFormConfig($formSetup)) {
                return false;
            }

            //Populate new Contact Model with fields from submitted data, based on form fields
            if (!$this->ContactModel->parseContact($formValues, $formSetup)) {
                return false;
            }

            //Optional: Set Double Opt-in defined per-form
            if ($AdditionalPlatformSettings = $this->ContactModel->getCustomDoubleOptIn()) {
                $this->TransferController->setAdditionalConfigurationFields($AdditionalPlatformSettings);
            }

	        Helper::doAction('salesmanago_gf_contact', array('Contact' => $this->ContactModel->get()));

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
}
