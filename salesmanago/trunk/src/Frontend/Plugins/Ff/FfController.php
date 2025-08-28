<?php

namespace bhr\Frontend\Plugins\Ff;

if(!defined('ABSPATH')) exit;

use bhr\Frontend\Plugins\Ff\FfContactModel as ContactModel;
use bhr\Frontend\Controller\TransferController;
use Error;
use Exception;
use bhr\Frontend\Model\Helper;


class FfController
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
     * @param $form_data
     * @param $entry_id
     *
     * @return bool
     */
    public function execute( $form_data, $entry_id, $form)
    {
        try {
            //Get config for submitted form (tags, owner)
            if (!$this->ContactModel->setCurrentFormConfig($entry_id['form_id'])) {
                return false;
            }

            //Set Contact Owner
            if (!empty($this->ContactModel->getCurrentFormConfig())) {
                $this->TransferController->setOwner($this->ContactModel->getCurrentFormConfig()->owner);
            }

			//Get form date format (for Contact birthday)
			$bd_date_format = $this->get_bd_date_format( $form );

            //Populate new Contact Model with fields from submitted data
            if ( ! $this->ContactModel->parseContact( $form_data, $bd_date_format ) ) {
                return false;
            }

            //Optional: Set Double Opt-in defined per-form
            if ($AdditionalPlatformSettings = $this->ContactModel->getCustomDoubleOptIn( $form_data)) {
                $this->TransferController->setAdditionalConfigurationFields($AdditionalPlatformSettings);
            }

	        Helper::doAction('salesmanago_ff_contact', array('Contact' => $this->ContactModel->get()));

	        //Transfer Contact with global controller
            return $this->TransferController->transferContact($this->ContactModel->get());
        } catch (Error | Exception $e) {
            error_log(print_r($e->getMessage(), true));
            return false;
        }
    }

	/**
	 * Get date format for the birthday field, return false otherwise.
	 *
	 * @param $form
	 *
	 * @return string | false
	 */
	protected function get_bd_date_format ( $form ) {
		try {
			$fields = json_decode( $form->form_fields, true )['fields'];
			foreach ( $fields as $field ) {
				if ( $field['attributes']['name'] === 'sm_birthday' ) {
					return $field['settings']['date_format'];
				}
			}
			return false;
		} catch ( Error | Exception $e ) {
			error_log( print_r( $e->getMessage(), true ) );
			return false;
		}
	}
}
