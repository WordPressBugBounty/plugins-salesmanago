<?php

namespace bhr\Frontend\Plugins\Cf7;

if(!defined('ABSPATH')) exit;

use bhr\Frontend\Model\Helper;
use bhr\Frontend\Plugins\Cf7\Cf7ContactModel as ContactModel;
use bhr\Frontend\Controller\TransferController;
use SALESmanago\Exception\Exception;

class Cf7Controller
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
     * @param $data
     * @return bool
     */
    public function execute($data)
    {
        try {
            //Get instance of CF7 submission
            $submission = Helper::getCf7SubmissionInstance();
            if (!$submission) {
                return false;
            }

            //Get config for submitted form (tags, owner)
            if (!$this->ContactModel->setCurrentFormConfig($data->id())) {
                return false;
            }

            //Set Contact Owner
            if (!empty($this->ContactModel->getCurrentFormConfig())) {
                $this->TransferController->setOwner($this->ContactModel->getCurrentFormConfig()->owner);
            }

            //Get form data
            $formData = $submission->get_posted_data();
            $filteredFormData = $this->ContactModel->filterFormData( $formData );

            //Populate new Contact Model with fields from submitted data
            if ( !$this->ContactModel->parseContact( $filteredFormData ) ) {
                return false;
            }

            //Optional: Set Double Opt-in defined per-form
            if ( $AdditionalPlatformSettings = $this->ContactModel->getCustomDoubleOptIn( $filteredFormData ) ) {
                $this->TransferController->setAdditionalConfigurationFields( $AdditionalPlatformSettings );
            }

			Helper::doAction('salesmanago_cf7_contact', array('Contact' => $this->ContactModel->get()));

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
